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

use ONGR\FilterManagerBundle\Search\SearchResponse;
use ONGR\TranslationsBundle\Service\TranslationManager;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        /** @var SearchResponse $filterResponse */
        $filterResponse = $this->get('ongr_translations.filter_manager')->handleRequest($request);

        return new JsonResponse(iterator_to_array($filterResponse->getResult()));
    }

    /**
     * Renders view with filter manager response.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var TranslationManager $manager */
        $manager = $this->get('ongr_translations.translation_manager');

        return $this->render(
            'ONGRTranslationsBundle:List:list.html.twig',
            [
                'locales' => $this->getParameter('ongr_translations.locales'),
                'tags' => $manager->getTags(),
                'domains' => $manager->getDomains(),
            ]
        );
    }
}
