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
     * Data provider for testing client status codes on rest controller.
     *
     * @return array
     */
    public function getTestEdtiActionStatusCodeData()
    {
        return [
            ['/translate/_api/edit/', 404],
            ['/translate/_api/edit/2', 400],
            ['/translate/_api/edit/2', 400, '{}'],
            ['/translate/_api/edit/2', 404, json_encode(['value' => 'foo_home', 'field' => 'message'])],
        ];
    }

    /**
     * Tests edit action status codes.
     *
     * @param string $url        Url to send request to.
     * @param int    $statusCode Status code with which client responded.
     * @param string $content    Request content.
     *
     * @dataProvider getTestEdtiActionStatusCodeData
     */
    public function testEdtiActionStatusCode($url, $statusCode, $content = '')
    {
        $client = self::createClient();
        $client->request('POST', $url, [], [], [], $content);
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
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
                'field' => 'message',
            ]
        );

        $client->request(
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
