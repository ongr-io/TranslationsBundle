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
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Document\History;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;

/**
 * History handler.
 */
class HistoryManager
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns an array of history objects grouped by locales
     *
     * @param Translation $translation
     *
     * @return array
     */
    public function get(Translation $translation)
    {
        $ordered = [];
        $search = $this->repository->createSearch();
        $search->addQuery(new TermQuery('key', $translation->getKey()), BoolQuery::FILTER);
        $search->addQuery(new TermQuery('domain', $translation->getDomain()), BoolQuery::FILTER);
        $search->addSort(new FieldSort('created_at', FieldSort::DESC));
        $histories = $this->repository->findDocuments($search);

        /** @var History $history */
        foreach ($histories as $history) {
            $ordered[$history->getLocale()][] = $history;
        }

        return $ordered;
    }

    /**
     * @param Message $message
     * @param Translation $translation
     */
    public function add(Message $message, Translation $translation)
    {
        $history = new History();
        $history->setLocale($message->getLocale());
        $history->setKey($translation->getKey());
        $history->setDomain($translation->getDomain());
        $history->setMessage($message->getMessage());
        $history->setUpdatedAt($message->getUpdatedAt());

        $this->repository->getManager()->persist($history);
    }

    /**
     * Returns message history.
     *
     * @param Translation $translation
     *
     * @return DocumentIterator
     */
    private function getUnorderedHistory(Translation $translation)
    {
        $search = $this->repository->createSearch();
        $search->addQuery(new TermQuery('key', $translation->getKey()), BoolQuery::FILTER);
        $search->addQuery(new TermQuery('domain', $translation->getDomain()), BoolQuery::FILTER);
        $search->addSort(new FieldSort('created_at', FieldSort::DESC));

        return $this->repository->findDocuments($search);
    }
}
