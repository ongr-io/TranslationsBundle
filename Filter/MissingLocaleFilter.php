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

use ONGR\ElasticsearchDSL\Filter\TermsFilter;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\FilterManagerBundle\Filters\FilterState;
use ONGR\FilterManagerBundle\Filters\ViewData;
use ONGR\FilterManagerBundle\Filters\ViewData\Choice;
use ONGR\FilterManagerBundle\Filters\ViewData\ChoicesAwareViewData;
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

    /**
     * {@inheritdoc}
     */
    public function getViewData(DocumentIterator $result, ViewData $data)
    {
        $data = parent::getViewData($result, $data);
        $requestField = $this->getRequestField();
        $choices = $data->getChoices();
        $list = [];
        $active = true;

        foreach ($choices as $choice) {
            $parameters = $choice->getUrlParameters();
            if (isset($parameters[$requestField])) {
                $list = array_merge($list, $parameters[$requestField]);
            }
            $active &= $choice->isActive();
        }

        if (!$active) {
            $this->addSelectAll($data, array_unique($list));
        }

        return $data;
    }

    /**
     * Adds Select all choice.
     *
     * @param ChoicesAwareViewData $data
     * @param array                $list
     */
    private function addSelectAll(ChoicesAwareViewData $data, $list)
    {
        $parameters = $data->getUrlParameters();
        $parameters[$this->getRequestField()] = $list;

        $choice = new Choice();
        $choice->setLabel('label.missing_locale.all');
        $choice->setUrlParameters($parameters);

        $data->addChoice($choice);
    }
}
