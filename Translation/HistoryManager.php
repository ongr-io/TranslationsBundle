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

use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Query\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Document\History;
use ONGR\TranslationsBundle\Document\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * Returns message history.
     *
     * @param string $id
     *
     * @return array
     */
    public function getHistory($id)
    {
        $search = $this->repository->createSearch();
        $search->addFilter(new TermQuery('translation', $id));
        $search->addSort(new FieldSort('created_at', FieldSort::DESC));

        return $this->repository->findDocuments($search);
    }


    /**
     * @param Message $message
     * @param $id
     * @param $locale
     */
    public function addHistory(Message $message, $id, $locale)
    {
        $history = new History();
        $history->setLocale($locale);
        $history->setTranslation($id);
        $history->setMessage($message->getMessage());
        $history->setId(sha1($id . $message->getMessage()));
        $history->setUpdatedAt($message->getUpdatedAt());

        $this->repository->getManager()->persist($history);
    }
}
