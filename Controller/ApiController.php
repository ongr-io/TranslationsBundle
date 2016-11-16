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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request, $id)
    {
        $response = ['error' => false];

        try {
            $this->get('ongr_translations.translation_manager')->update($id, $request);
        } catch (\LogicException $e) {
            $response = ['error' => true];
        }

        return new JsonResponse($response);
    }

    /**
     * Action for getting translation.
     *
     * @param Request $request Http request object.
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $id)
    {
        return new JsonResponse($this->get('ongr_translations.translation_manager')->get($id));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function allAction(Request $request)
    {
        return new JsonResponse($this->get('ongr_translations.translation_manager')->getAll());
    }

    /**
     * Action for executing export command.
     *
     * @return JsonResponse
     */
    public function exportAction()
    {
        $cwd = getcwd();
        if (substr($cwd, -3) === 'web') {
            chdir($cwd . DIRECTORY_SEPARATOR . '..');
        }

        $output = ['error' => false];

        if ($this->get('ongr_translations.command.export')->run(new ArrayInput([]), new NullOutput()) != 0) {
            $output['error'] = true;
        }

        return new JsonResponse($output);
    }

    /**
     * Action for executing history command.
     *
     * @param Request $request Http request object.
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function historyAction(Request $request, $id)
    {
        $document = $this->get('ongr_translations.translation_manager')->get($id);

        if (empty($document)) {
            return new JsonResponse(['error' => true, 'message' => 'translation not found']);
        }

        return new JsonResponse($this->get('ongr_translations.history_manager')->get($document));
    }
}
