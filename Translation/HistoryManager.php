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

use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Filter\TermFilter;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchBundle\Service\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * History handler.
 */
class HistoryManager
{
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
     * @param Request $request
     *
     * @return array
     */
    public function history(Request $request)
    {
        $content = $this->parseJsonContent($request);
        $boolFilter = new BoolQuery();
        $boolFilter->add(new TermFilter('key', $content['key']));
        $boolFilter->add(new TermFilter('domain', $content['domain']));
        $boolFilter->add(new TermFilter('locale', $content['locale']));
        $sort = new FieldSort('created_at', FieldSort::DESC);
        $search = $this->repository->createSearch()->addFilter($boolFilter)->addSort($sort);

        return $this->repository->execute($search, Repository::RESULTS_ARRAY);
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
}
