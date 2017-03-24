<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service;

use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Event\Events;
use ONGR\TranslationsBundle\Event\MessageUpdateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @param string $id
     *
     * @return Translation|object
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Returns all active tags from translations
     * @return array
     */
    public function getTags()
    {
        return $this->getItems('tags');
    }

    /**
     * Returns all active domains from translations
     * @return array
     */
    public function getDomains()
    {
        return $this->getItems('domain');
    }

    /**
     * Returns all translations if filters are not specified
     *
     * @param array $filters An array with specified limitations for results
     *
     * @return DocumentIterator
     */
    public function getAll(array $filters = null)
    {
        $search = $this->repository->createSearch();
        $search->addQuery(new MatchAllQuery());
        $search->setScroll('2m');

        if ($filters) {
            foreach ($filters as $field => $value) {
                $search->addQuery(new TermsQuery($field, $value), BoolQuery::FILTER);
            }
        }

        return $this->repository->findDocuments($search);
    }

    /**
     * Edits object from translation.
     *
     * @param string $id
     * @param Request $request Http request object.
     */
    public function update($id, Request $request)
    {
        $content = json_decode($request->getContent(), true);

        if (empty($content)) {
            return;
        }

        $document = $this->get($id);

        if (isset($content['messages'])) {
            $this->updateMessages($document, $content['messages']);
            unset($content['messages']);
        }

        foreach ($content as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (!method_exists($document, $method)) {
                throw new \LogicException('Illegal variable provided for translation');
            }

            $document->$method($value);
        }

        $document->setUpdatedAt(new \DateTime());
        $this->repository->getManager()->persist($document);
        $this->repository->getManager()->commit();
    }

    /**
     * @param Translation[] $translations
     */
    public function save($translations)
    {
        foreach ($translations as $translation) {
            $this->repository->getManager()->persist($translation);
        }

        $this->repository->getManager()->commit();
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
                if (in_array($locale, $setMessagesLocales)) {
                    $message = $document->getMessageByLocale($locale);

                    if ($message && $message->getMessage() != $messageText) {
                        $this->dispatcher->dispatch(Events::ADD_HISTORY, new MessageUpdateEvent($document, $message));
                        $this->updateMessageData($message, $locale, $messages[$locale]);
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
     *
     * @return Message
     */
    private function updateMessageData(Message $message, $locale, $text)
    {
        $message->setLocale($locale);
        $message->setStatus(Message::DIRTY);
        $message->setMessage($text);
        $message->setUpdatedAt(new \DateTime());

        return $message;
    }

    /**
     * Returns a list of available tags or domains
     *
     * @param string $type
     * @return array
     */
    private function getItems($type)
    {
        $search = $this->repository->createSearch();
        $search->addAggregation(new TermsAggregation($type, $type));
        $result = $this->repository->findDocuments($search);
        $aggregation = $result->getAggregation($type);
        $items = [];

        foreach ($aggregation as $item) {
            $items[] = $item['key'];
        }

        return $items;
    }
}
