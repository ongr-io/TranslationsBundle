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

use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\FilterManagerBundle\Filter\ViewData;
use ONGR\FilterManagerBundle\Search\SearchResponse;
use ONGR\TranslationsBundle\Document\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for displaying translations.
 */
class ListController extends Controller
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Injects elasticsearch repository for listing actions.
     *
     * @param Repository $repository Elasticsearch repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns a JsonResponse with available locales
     * @return JsonResponse
     */
    public function getInitialDataAction()
    {
        $out = [];
        $out['locales'] = $this->getParameter('ongr_translations.managed_locales');
        $out['tags'] = $this->get('ongr_translations.translation_manager')->getTags();
        return new JsonResponse($out);
    }

    /**
     * Returns a JsonResponse with available locales
     * @return JsonResponse
     */
    public function getTranslationsAction()
    {
        $documentArray = [];
        $locales = $this->getParameter('ongr_translations.managed_locales');

        /** @var Translation $doc */
        foreach ($this->get('ongr_translations.translation_manager')->getAllTranslations() as $doc) {
            $doc = $doc->jsonSerialize();

            foreach ($locales as $locale) {
                if (!isset($doc['messages'][$locale])) {
                    $doc['messages'][$locale]['message'] = '[No message]';
                }
            }

            $documentArray[] = $doc;
        }

        return new JsonResponse($documentArray);
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
        $fmr = $this->get('ongr_translations.filter_manager')->handleRequest($request);

        return $this->render(
            'ONGRTranslationsBundle:List:list.html.twig',
            [
                'data' => iterator_to_array($fmr->getResult()),
                'locales' => $this->getParameter('ongr_translations.managed_locales'),//$this->buildLocalesList($fmr->getFilters()['locale']),
                'filters_manager' => $fmr,
            ]
        );
    }

    /**
     * Creates locales list.
     *
     * @param ViewData\ChoicesAwareViewData $filter
     *
     * @return array
     */
    private function buildLocalesList($filter)
    {
        $locales = $this->container->getParameter('ongr_translations.managed_locales');
        $list = [];
        foreach ($locales as $locale) {
            $list[$locale] = true;
        }
        ksort($list);
        $activeLocales = [];

        if ($filter->getState()->isActive()) {
            foreach ($filter->getChoices() as $choice) {
                $activeLocales[$choice->getLabel()] = $choice->isActive();
            }
            $list = array_merge($list, $activeLocales);
        }

        return $list;
    }
}
