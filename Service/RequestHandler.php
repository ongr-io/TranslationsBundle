<?php

/*
* This file is part of the ONGR package.
*
* (c) NFQ Technologies UAB <info@nfq.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ONGR\TranslationsBundle\Service;

use Elasticsearch\Common\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use ONGR\TranslationsBundle\Document\Translation;

/**
* Remakes requests to be compatible with TranslationManager.
*/
class RequestHandler
{
    /**
     * Translation that is modified by the request
     *
     * @var Translation
     */
    private $translation;

    /**
     * @return Translation
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param Translation $translation
     */
    public function setTranslation(Translation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Remakes a request to have json content
     * of a single object. If there is a number of
     * locales associated with a request it returns an
     * array of new requests
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function remakeRequest(Request $request)
    {
        $content = [];
        $content['name'] = $request->request->get('name');
        $content['properties'] = $request->request->get('properties');
        $content['id'] = $request->request->get('id');
        $content['findBy'] = $request->request->get('findBy');
        if ($request->request->has('locales')) {
            return $this->turnToArray($request, $content);
        }
        if (
            $content['name'] == 'tags' &&
            $this->sameTagExists($content['properties']['name'])
        ) {
            throw new \InvalidArgumentException('Tag already set');
        }
        $content = json_encode($content);
        return new Request([], [], [], [], [], [], $content);
    }

    /**
     * Turns a request to an array of requests with json content
     *
     * @param Request $request
     * @param array $content
     *
     * @return array
     */
    private function turnToArray(Request $request, array $content)
    {
        $requests = [];
        $locales = $request->request->get('locales');
        $messages = $request->request->get('messages');
        $findBy = $request->request->get('findBy');
        foreach ($locales as $locale) {
            if (
                $messages[$locale] == '' ||
                $this->sameMessageExists($locale, $messages[$locale])
            ) {
                continue;
            }
            $content['properties']['locale'] = $locale;
            $content['properties']['message'] = $messages[$locale];
            $content['properties']['status'] = 'dirty';
            $content['findBy'] = $findBy[$locale];
            $requests[] = new Request([], [], [], [], [], [], json_encode($content));
        }
        return $requests;
    }

    /**
     * Checks if the given message matches the
     * message set in the given locale of the translation
     *
     * @param string $locale
     * @param string $message
     *
     * @return bool
     */
    private function sameMessageExists($locale, $message)
    {
        if (!isset($this->translation)) {
            throw new \InvalidArgumentException('translation is not set in RequestHandler.php');
        }
        $return = false;
        foreach ($this->translation->getMessages() as $translationMessage) {
            if (
                $translationMessage->getLocale() == $locale &&
                $translationMessage->getMessage() == $message
            ) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Checks if the same tag exists
     *
     *  @param string $name
     *
     * @return bool
     */
    private function sameTagExists($name)
    {
        if (!isset($this->translation)) {
            throw new \InvalidArgumentException('translation is not set in RequestHandler.php');
        }
        foreach ($this->translation->getTags() as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }
        return false;
    }
}
