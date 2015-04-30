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

use ONGR\ElasticsearchBundle\DSL\Bool\Bool;
use ONGR\ElasticsearchBundle\DSL\Filter\TermFilter;
use ONGR\ElasticsearchBundle\DSL\Sort\Sort;
use ONGR\ElasticsearchBundle\ORM\Repository;
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
        $boolFilter = new Bool();
        $boolFilter->addToBool(new TermFilter('key', $content['key']));
        $boolFilter->addToBool(new TermFilter('domain', $content['domain']));
        $boolFilter->addToBool(new TermFilter('locale', $content['locale']));
        $sort = new Sort('created_at', Sort::ORDER_DESC);
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
