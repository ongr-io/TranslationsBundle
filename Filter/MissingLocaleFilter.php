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

use ONGR\ElasticsearchBundle\DSL\Filter\TermsFilter;
use ONGR\ElasticsearchBundle\DSL\Search;
use ONGR\FilterManagerBundle\Filters\FilterState;
use ONGR\FilterManagerBundle\Filters\Widget\Choice\MultiTermChoice;
use ONGR\FilterManagerBundle\Search\SearchRequest;

/**
 * Filter to show only missing locales.
 */
class MissingLocaleFilter extends MultiTermChoice
{
    /**
     * {@inheritdoc}
     */
    public function modifySearch(Search $search, FilterState $state = null, SearchRequest $request = null)
    {
        if ($state && $state->isActive()) {
            $search->addPostFilter(
                new TermsFilter($this->getField(), $state->getValue(), ['execution' => 'and']),
                'must_not'
            );
        }
    }
}
