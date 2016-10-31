<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Translation;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\ExistsQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Event\Events;
use ONGR\TranslationsBundle\Event\TranslationEditMessageEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Handles translation objects by http requests.
 */
class TranslationManager
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param Repository               $repository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Repository $repository, EventDispatcherInterface $dispatcher)
    {
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Adds object to translations.
     *
     * @param Request $request
     */
    public function add(Request $request)
    {
        $content = $this->parseJsonContent($request);
        $document = $this->getTranslation($content['id']);
        $this->addObject($document, $content);

        $this->repository->getManager()->persist($document);
        $this->repository->getManager()->commit();
    }

    /**
     * Edits object from translation.
     *
     * @param string $id
     * @param Request $request Http request object.
     */
    public function edit($id, Request $request)
    {
        $content = $this->parseJsonContent($request);
        $document = $this->getTranslation($id);

        if (isset($content['messages'])) {
//            $this->dispatcher->dispatch(Events::ADD_HISTORY, new TranslationEditMessageEvent($request, $document));
            $this->updateMessages($document, $content['messages']);
            unset($content['messages']);
        }

        try {
            foreach ($content as $key => $value) {
                $document->{'set'.ucfirst($key)}($value);
            }

            $document->setUpdatedAt(new \DateTime());
        } catch (\Exception $e) {
            throw new \LogicException('Illegal variable provided for translation');
        }

        $this->commitTranslation($document);
    }

    /**
     * @param Translation $document
     * @param array $messages
     */
    private function updateMessages(Translation $document, array $messages)
    {
        $documentMessages = $document->getMessages();

        foreach ($documentMessages as $message) {
            $locale = $message->getLocale();

            if (isset($messages[$locale])) {
                if ($messages[$locale] != $message->getMessage() && !empty($messages[$locale])) {
                    $message->setMessage($messages[$locale]);
                    $message->setUpdatedAt(new \DateTime());
                    $message->setStatus(Message::DIRTY);
                }

                unset($messages[$message->getLocale()]);
            }
        }

        if (!empty($messages)) {
            foreach ($messages as $locale => $messageText) {
                if (!empty($messageText)) {
                    $message = new Message();
                    $message->setStatus(Message::DIRTY);
                    $message->setMessage($messageText);
                    $documentMessages[] = $message;
                }
            }
        }

        $document->setMessages($documentMessages);
    }

    /**
     * Edits object from translation.
     *
     * @param Request $request Http request object.
     */
    public function editMessage(Request $request)
    {
        $content = $this->parseJsonContent($request);

        if (!isset($content['locale']) || !isset($content['message'])) {
            return;
        }

        $document = $this->getTranslation($content['id']);
        $message = null;

        foreach ($document->getMessages() as $currentMessage) {
            if ($currentMessage->getLocale() == $content['locale']) {
                $message = $currentMessage;
                break;
            }
        }

        if ($message === null) {
            $message = new Message();
            $message->setLocale($content['locale']);
            $document->addMessage($message);
        }

        $message->setMessage($content['message']);
        $message->setUpdatedAt(new \DateTime());
        $message->setStatus('dirty');
//        $this->dispatcher->dispatch(Events::ADD_HISTORY, new TranslationEditMessageEvent($request, $document));
        $this->commitTranslation($document);

        return $document;
    }

    /**
     * Removes object from translations.
     *
     * @param Request $request Http request object.
     */
    public function delete(Request $request)
    {
        $content = $this->parseJsonContent($request);
        $document = $this->getTranslation($content['id']);
        $this->deleteObject($document, $content);
        $this->commitTranslation($document);
    }

    /**
     * Returns specific values from objects.
     *
     * @param Request $request Http request object.
     *
     * @return array
     */
    public function get(Request $request)
    {
        $content = $this->parseJsonContent($request);

        $search = $this
            ->repository
            ->createSearch()
            ->addFilter(new ExistsQuery($content['name']));

        if (array_key_exists('properties', $content)) {
            foreach ($content['properties'] as $property) {
                $search->setSource($content['name'] . '.' . $property);
            }
        }

        if (array_key_exists('findBy', $content)) {
            foreach ($content['findBy'] as $field => $value) {
                $search->addQuery(
                    new TermsQuery($content['name'] . '.' . $field, is_array($value) ? $value : [$value]),
                    'must'
                );
            }
        }

        return $this->repository->execute($search, Result::RESULTS_ARRAY);
    }

    /**
     * @return DocumentIterator
     */
    public function getAllTranslations()
    {
        $search = $this->repository->createSearch();
        $search->addQuery(new MatchAllQuery());
        $search->setSize(1000);

        return $this->repository->findDocuments($search);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $search = $this->repository->createSearch();
        $search->addAggregation(new TermsAggregation('tags', 'tags'));
        $result = $this->repository->findDocuments($search);
        $tagAggregation = $result->getAggregation('tags');
        $tags = [];

        foreach ($tagAggregation as $tag) {
            $tags[] = $tag['key'];
        }

        return $tags;
    }

    /**
     * Adds object to translation.
     *
     * @param object $document
     * @param array  $options
     */
    private function addObject($document, $options)
    {
        $meta = $this->repository->getManager()->getMetadataCollector()
            ->getBundleMapping('ONGRTranslationsBundle:Translation');
        $objectClass = reset($meta)['aliases'][$options['name']]['namespace'];

        $object = new $objectClass();
        $this->setObjectProperties($object, $options['properties']);

        $this->updateTimestamp($object);
        $this->updateTimestamp($document);
    }

    /**
     * Removes message from document based on options.
     *
     * @param object $document
     * @param array  $options
     */
    private function deleteObject($document, $options)
    {
        $accessor = $this->getAccessor();
        $objects = $accessor->getValue($document, $options['name']);

        $key = $this->findObject($objects, $options['findBy']);

        if ($key >= 0) {
            unset($objects[$key]);
            $accessor->setValue($document, $options['name'], $objects);
        }
    }

    /**
     * Edits message from document based on options.
     *
     * @param object $document
     * @param array  $options
     */
    private function editObject($document, $options)
    {
        $accessor = $this->getAccessor();
        $objects = $accessor->getValue($document, $options['name']);

        if ($objects === null) {
            $this->addObject($document, $options);
        } else {
            $key = $this->findObject($objects, $options['findBy']);

            if ($key < 0) {
                $this->addObject($document, $options);
            } else {
                $this->setObjectProperties($objects[$key], $options['properties']);
                $this->updateTimestamp($objects[$key]);
                $this->updateTimestamp($document);
                $accessor->setValue($document, $options['name'], $objects);
            }
        }
    }

    /**
     * Finds object by property and its value from iterator and returns key.
     *
     * @param \Iterator $objects
     * @param array     $options
     *
     * @return int
     */
    private function findObject($objects, $options)
    {
        foreach ($objects as $key => $object) {
            $fit = true;

            foreach ($options as $property => $value) {
                if ($this->getAccessor()->getValue($object, $property) !== $value) {
                    $fit = false;
                    break;
                }
            }

            if ($fit) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * Parses http request content from json to array.
     *
     * @param Request $request Http request object.
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    private function parseJsonContent(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        if (empty($content)) {
            throw new BadRequestHttpException('No content found.');
        }

        return $content;
    }

    /**
     * @param object $document
     */
    private function commitTranslation($document)
    {
        $this->repository->getManager()->persist($document);
        $this->repository->getManager()->commit();
    }

    /**
     * Returns translation from elasticsearch.
     *
     * @param string $id
     *
     * @return Translation
     *
     * @throws BadRequestHttpException
     */
    private function getTranslation($id)
    {
        try {
            $document = $this->repository->find($id);
        } catch (Missing404Exception $e) {
            $document = null;
        }

        if ($document === null) {
            throw new BadRequestHttpException('Invalid translation Id.');
        }

        return $document;
    }

    /**
     * Returns property accessor instance.
     *
     * @return PropertyAccessorInterface
     */
    private function getAccessor()
    {
        if (!$this->accessor) {
            $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->enableMagicCall()
                ->getPropertyAccessor();
        }

        return $this->accessor;
    }

    /**
     * Sets `updated_at` property.
     *
     * @param object $object
     */
    private function updateTimestamp($object)
    {
        $accessor = $this->getAccessor();

        if ($accessor->isWritable($object, 'updated_at')) {
            $accessor->setValue($object, 'updated_at', new \DateTime());
        }
    }

    /**
     * Sets object properties into provided object.
     *
     * @param object $object     Object to set properties into.
     * @param array  $properties Array of properties to set.
     */
    private function setObjectProperties($object, $properties)
    {
        foreach ($properties as $property => $value) {
            $this->getAccessor()->setValue($object, $property, $value);
        }
    }
}
