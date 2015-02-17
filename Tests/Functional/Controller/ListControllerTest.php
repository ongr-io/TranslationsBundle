<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListControllerTest extends WebTestCase
{
    /**
     * Import test data.
     */
    protected function setUp()
    {
        $container = static::createClient()->getContainer();

        $importService = $container->get('ongr_translations.import');
        $importService->import(['en'], ['messages']);
        $importService->writeToStorage();
    }

    /**
     * Tests if list is returning 200 response.
     */
    public function testListRoute()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translate/list');

        $this->assertTrue($client->getResponse()->isOk());
    }

    /**
     * Tests translation not found response code.
     */
    public function testTranslationNotFound()
    {
        $client = self::createClient();
        $requestContent = json_encode(
            [
                'translation' => [
                    'field' => 'message',
                    'value' => 'foo_home',
                ],
            ]
        );
        $crawler = $client->request(
            'POST',
            '/translate/edit/1',
            ['id' => '1'],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isNotFound());
    }

    /**
     * Tests translation message update case.
     */
    public function testUpdateTranslations()
    {
        $client = self::createClient();

        $requestContent = json_encode(
            [
                'translation' => [
                    'field' => 'message',
                    'value' => 'foo_home',
                ],
            ]
        );

        $crawler = $client->request(
            'GET',
            '/translate/edit/370f67440cb78b213e563ae380dfd69f4f27e971',
            [ 'id' => '370f67440cb78b213e563ae380dfd69f4f27e971'],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isOk());
    }
}
