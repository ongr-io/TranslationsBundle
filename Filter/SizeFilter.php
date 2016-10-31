<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Filter;

use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchDSL\Search;
use ONGR\FilterManagerBundle\Filter\FilterInterface;
use ONGR\FilterManagerBundle\Filter\FilterState;
use ONGR\FilterManagerBundle\Filter\Widget\Choice\MultiTermChoice;
use ONGR\FilterManagerBundle\Search\SearchRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale filter for constructing custom locale filter.
 */
class SizeFilter extends MultiTermChoice implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getState(Request $request)
    {
        $state = new FilterState();
        $state->setActive(true);

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function modifySearch(Search $search, FilterState $state = null, SearchRequest $request = null)
    {
        $search->setSize(10000);
    }

    /**
     * {@inheritdoc}
     */
    public function preProcessSearch(Search $search, Search $relatedSearch, FilterState $state = null)
    {
        // Do nothing
    }
}
