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

use Symfony\Component\HttpFoundation\Request;

/**
* Remakes requests to be compatible with TranslationManager.
*/
class RequestHandler
{
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
        $statuses = $request->request->get('statuses');
        $findBy = $request->request->get('findBy');
        foreach ($locales as $locale) {
            if ($messages[$locale] == '') {
                break;
            }
            $content['properties']['locale'] = $locale;
            $content['properties']['message'] = $messages[$locale];
            $content['properties']['status'] = $statuses[$locale];
            $content['findBy'] = $findBy[$locale];
            $requests[] = new Request([], [], [], [], [], [], json_encode($content));
        }
        return $requests;
    }
}
