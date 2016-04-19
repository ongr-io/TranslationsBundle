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

use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\FilterManagerBundle\Filter\ViewData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for working with individual translation
 * in edit view
 */
class TranslationController extends Controller
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
     * Add a tag action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addTagAction(Request $request)
    {
        $response = [];
        $translation = $this->repository->find($request->request->get('id'));
        $cache = $this->get('es.cache_engine');
        $requestHandler = $this->get('ongr_translations.request_handler');
        try {
            $this->get('ongr_translations.translation_manager')
                ->add($requestHandler->remakeRequest($request));
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        !isset($response['error']) ?
            $response['success'] = 'Tag successfully added' :
            $response['success'] = false;
        $cache->save('translations_edit', $response);
        return new RedirectResponse(
            $this->generateUrl(
                'ongr_translations_translation_page',
                [
                    'translation' => $translation->getKey(),
                    'domain' => $translation->getDomain(),
                ]
            )
        );
    }

    /**
     * Add a tag action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        $response = [];
        $translation = $this->repository->find($request->request->get('id'));
        $cache = $this->get('es.cache_engine');
        $requestHandler = $this->get('ongr_translations.request_handler');
        $requests = $requestHandler->remakeRequest($request);
        try {
            foreach ($requests as $messageRequest) {
                $this->get('ongr_translations.translation_manager')
                    ->edit($messageRequest);
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        !isset($response['error']) ?
            $response['success'] = 'Messages updated successfully' :
            $response['success'] = false;
        $cache->save('translations_edit', $response);
        return new RedirectResponse(
            $this->generateUrl(
                'ongr_translations_translation_page',
                [
                    'translation' => $translation->getKey(),
                    'domain' => $translation->getDomain(),
                ]
            )
        );
    }
}
