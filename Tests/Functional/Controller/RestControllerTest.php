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

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

/**
 * Tests rest controller actions.
 */
class RestControllerTest extends AbstractElasticsearchTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'translation' => [
                    [
                        '_id' => sha1('foo.key'),
                        'key' => 'foo.key',
                        'message' => 'foo',
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests translation not found response code.
     */
    public function testEditActionTranslationNotFound()
    {
        $client = self::createClient();
        $requestContent = json_encode(
            [
                'value' => 'foo_home',
            ]
        );
        $crawler = $client->request(
            'POST',
            '/translate/_api/edit/2',
            [],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isNotFound());
    }

    /**
     * Tests translation message update case.
     */
    public function testEditAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'value' => 'foo_home',
            ]
        );

        $crawler = $client->request(
            'POST',
            "/translate/_api/edit/{$id}",
            [],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isOk(), 'Controller response should be 200.');

        $translation = $this
            ->getManager('default', false)
            ->getRepository('ONGRTranslationsBundle:Translation')
            ->find($id);

        $this->assertEquals('foo_home', $translation->getMessage(), 'Message should be updated.');
    }
}
