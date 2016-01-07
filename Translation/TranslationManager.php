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
use ONGR\ElasticsearchBundle\Result\ObjectIterator;
use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Query\ExistsQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\ElasticsearchBundle\Service\Repository;
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
        $this->commitTranslation($document);
    }

    /**
     * Edits object from translation.
     *
     * @param Request $request Http request object.
     */
    public function edit(Request $request)
    {
        $content = $this->parseJsonContent($request);
        $document = $this->getTranslation($content['id']);

        if ($content['name'] == 'messages') {
            $this->dispatcher->dispatch(Events::ADD_HISTORY, new TranslationEditMessageEvent($request, $document));
        }
        $this->editObject($document, $content);
        $this->commitTranslation($document);
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
     * Adds object to translation.
     *
     * @param object $document
     * @param array  $options
     */
    private function addObject($document, $options)
    {
        $accessor = $this->getAccessor();
        $objects = $accessor->getValue($document, $options['name']);

        $meta = $this->repository->getManager()->getMetadataCollector()
            ->getBundleMapping('ONGRTranslationsBundle:Translation');
        $objectClass = reset($meta)['aliases'][$options['name']]['namespace'];

        $object = new $objectClass();
        $this->setObjectProperties($object, $options['properties']);

        $objects[] = $object;
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
     * Commits document into elasticsearch client.
     *
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
     * @return object
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
