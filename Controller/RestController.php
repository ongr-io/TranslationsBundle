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

        if (empty($content) || !array_key_exists('value', $content) || !array_key_exists('field', $content)) {
            return new JsonResponse(Response::$statusTexts[400], 400);
        }

        $value = $content['value'];
        $field = $content['field'];

        /** @var Repository $repository */
        $repository = $this->manager->getRepository('ONGRTranslationsBundle:Translation');

        try {
            /** @var Translation $translation */
            $translation = $repository->find($id);
            $translation->{'set' . ucfirst($field)}($value);

            $this->manager->persist($translation);
            $this->manager->commit();
        } catch (Missing404Exception $e) {
            return new JsonResponse(Response::$statusTexts[404], 404);
        }

        return new JsonResponse();
    }
}
