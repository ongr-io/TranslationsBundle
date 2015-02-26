<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Controller;

use ONGR\ElasticsearchBundle\DSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\FilterManagerBundle\Filters\FilterInterface;
use ONGR\FilterManagerBundle\Search\SearchResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for displaying translations.
 */
class ListController extends Controller
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * Sets elasticsearch manager for rest actions.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Renders view with filter manager response.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        /** @var SearchResponse $fmr */
        $fmr = $this->get('ongr_translations.filters_manager')->execute($request);

        return $this->render(
            'ONGRTranslationsBundle:List:list.html.twig',
            [
                'data' => iterator_to_array($fmr->getResult()),
                'locales' => $this->buildLocalesList($fmr->getFilters()['locale']),
                'filters_manager' => $fmr,
            ]
        );
    }

    /**
     * Creates locales list.
     *
     * @param FilterInterface $filter
     *
     * @return array
     */
    private function buildLocalesList($filter)
    {
        $repo = $this->manager->getRepository('ONGRTranslationsBundle:Translation');
        $search = $repo->createSearch();

        $localeAgg = new TermsAggregation('locale_agg');
        $localeAgg->setField('messages.locale');
        $search->addAggregation($localeAgg);
        $result = $repo->execute($search, Repository::RESULTS_RAW);
        $list = [];

        foreach ($result['aggregations']['agg_locale_agg']['buckets'] as $value) {
            $list[$value['key']] = true;
        }
        ksort($list);

        $activeLocales = [];

        if ($filter->getState(null)->isActive()) {
            foreach ($filter->getChoices() as $choice) {
                $activeLocales[$choice->getLabel()] = $choice->isActive();
            }
            $list = array_merge($list, $activeLocales);
        }

        return $list;
    }
}
