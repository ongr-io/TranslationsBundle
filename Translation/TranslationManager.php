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
     * @var HistoryManager
     */
    private $historyManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param Repository               $repository
     * @param HistoryManager           $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Repository $repository, HistoryManager $manager, EventDispatcherInterface $dispatcher)
    {
        $this->repository = $repository;
        $this->historyManager = $manager;
        $this->dispatcher = $dispatcher;
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
        $setMessagesLocales = array_keys($document->getMessagesArray());
        $documentMessages = $document->getMessages();

        foreach ($messages as $locale => $messageText) {
            if (!empty($messageText) && is_string($messageText)) {
                $this->dispatcher->dispatch(
                    Events::ADD_HISTORY,
                    new TranslationEditMessageEvent($document, $locale)
                );

                if (in_array($locale, $setMessagesLocales)) {
                    foreach ($documentMessages as $message) {
                        if ($message->getLocale() == $locale) {
                            $this->historyManager->addHistory($message, $document->getId(), $locale);
                            $this->updateMessageData($message, $locale, $messages[$locale], new \DateTime());
                            break;
                        }
                    }
                } else {
                    $documentMessages[] = $this->updateMessageData(new Message(), $locale, $messageText);
                }
            }
        }

        $document->setMessages($documentMessages);
    }

    /**
     * @param Message   $message
     * @param string    $locale
     * @param string    $text
     * @param \DateTime $updatedAt
     *
     * @return Message
     */
    private function updateMessageData(Message $message, $locale, $text, $updatedAt = null)
    {
        $message->setLocale($locale);
        $message->setStatus(Message::DIRTY);
        $message->setMessage($text);

        if ($updatedAt) {
            $message->setUpdatedAt($updatedAt);
        }

        return $message;
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
     * Returns all active tags from translations
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
            throw new BadRequestHttpException('Invalid translation Id.');
        }

        return $document;
    }
}
