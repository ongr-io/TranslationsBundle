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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used for api's actions.
 */
class ApiController extends Controller
{
    /**
     * Action for editing translation objects.
     *
     * @param Request $request Http request object.
     *
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        $this->get('ongr_translations.translation_manager')->edit($request);

        return new JsonResponse();
    }

    /**
     * Action for removing translation objects.
     *
     * @param Request $request Http request object.
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $this->get('ongr_translations.translation_manager')->delete($request);

        return new JsonResponse();
    }

    /**
     * Action for adding translation objects.
     *
     * @param Request $request Http request object.
     *
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        $this->get('ongr_translations.translation_manager')->add($request);

        return new JsonResponse();
    }

    /**
     * Action for getting specific values from objects.
     *
     * @param Request $request Http request object.
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        return new JsonResponse($this->get('ongr_translations.translation_manager')->get($request));
    }
}
