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

use Elasticsearch\Common\Exceptions\Missing404Exception;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\FilterManagerBundle\Search\SearchResponse;
use ONGR\TranslationsBundle\Document\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ManagerController.
 */
class ListController extends Controller
{
    /**
     * Renders view with filter manager response.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $fmr = $this->get('ongr_translations.filters_manager')->execute($request);

        return $this->render(
            'ONGRTranslationsBundle:List:list.html.twig',
            [
                'data' => iterator_to_array($fmr->getResult()),
                'filters_manager' => $fmr,
            ]
        );
    }

    /**
     * Saves translation to ES.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $content = $request->getContent();
        if (empty($content)) {
            return new Response(Response::$statusTexts[400], 400);
        }
        $content = json_decode($content, true);
        if ($content === null || empty($content['translation'])) {
            return new Response(Response::$statusTexts[400], 400);
        }
        $field = isset($content['translation']['field']) ? $content['translation']['field'] : false;
        $value = isset($content['translation']['value']) ? $content['translation']['value'] : false;

        /** @var Manager $manager */
        $manager = $this->getTranslationsManager();
        /** @var Repository $repo */
        $repo = $manager->getRepository('ONGRTranslationsBundle:Translation');

        try {
            /** @var Translation $translation */
            $translation = $repo->find($id);
            $translation->{'set' . ucfirst($field)}($value);

            $manager->persist($translation);
            $manager->commit();
        } catch (Missing404Exception $e) {
            return new Response(Response::$statusTexts[404], 404);
        }

        return new Response();
    }

    /**
     * Returns ES manager.
     *
     * @return Manager
     */
    public function getTranslationsManager()
    {
        $manager = $this->get('es.manager.default');

        return $manager;
    }
}
