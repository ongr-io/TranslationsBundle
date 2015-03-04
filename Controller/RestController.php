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
use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used for rest actions.
 */
class RestController extends Controller
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Injects elasticsearch repository for rest actions.
     *
     * @param Repository $repository Elasticsearch repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Saves translation to elasticsearch.
     *
     * @param Request $request Request object.
     * @param string  $id      Translation id.
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, $id)
    {
        $content = json_decode($request->getContent(), true);

        if (empty($content) || !array_key_exists('value', $content)) {
            return new JsonResponse(Response::$statusTexts[400], 400);
        }

        $value = $content['value'];
        $locale = array_key_exists('locale', $content) ? $content['locale'] : false;

        try {
            /** @var Translation $translation */
            $translation = $this->repository->find($id);
            if ($locale) {
                $exist = false;
                foreach ($translation->getMessages() as $message) {
                    if ($message->getLocale() === $locale) {
                        $message->setMessage($value);
                        $exist = true;
                    }
                }
                if (!$exist) {
                    $msg = new Message();
                    $msg->setLocale($locale);
                    $msg->setMessage($value);
                    $translation->addMessage($msg);
                }
            } else {
                $translation->setGroup($value);
            }

            $this->repository->getManager()->persist($translation);
            $this->repository->getManager()->commit();
        } catch (Missing404Exception $e) {
            return new JsonResponse(Response::$statusTexts[404], 404);
        }

        return new JsonResponse();
    }
}
