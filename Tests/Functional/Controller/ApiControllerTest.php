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
class ApiControllerTest extends AbstractElasticsearchTestCase
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
                        'tags' => [
                            [
                                'name' => 'foo_tag',
                            ],
                            [
                                'name' => 'tuna_tag',
                            ],
                        ],
                        'messages' =>
                            [
                                [
                                    'locale' => 'en',
                                    'message' => 'foo',
                                ],
                            ],
                    ],
                    [
                        '_id' => sha1('baz.key'),
                        'key' => 'baz.key',
                        'tags' => [
                            [
                                'name' => 'baz_tag',
                            ],
                        ],
                        'messages' =>
                            [
                                [
                                    'locale' => 'en',
                                    'message' => 'baz',
                                ],
                            ],
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
            ['POST', '/translate/_api/edit', 400],
            ['POST', '/translate/_api/edit', 400, json_encode(['id' => 2])],
            ['POST', '/translate/_api/edit', 400, '{}'],
            [
                'GET',
                '/translate/_api/get',
                200,
                json_encode(
                    [
                        'id' => sha1('foo.key'),
                        'name' => 'tags',
                        'objectProperty' => 'name',
                    ]
                ),
            ],
        ];
    }

    /**
     * Tests edit action status codes.
     *
     * @param string $method     Http method to use.
     * @param string $url        Url to send request to.
     * @param int    $statusCode Status code with which client responded.
     * @param string $content    Request content.
     *
     * @dataProvider getTestEdtiActionStatusCodeData
     */
    public function testEdtiActionStatusCode($method, $url, $statusCode, $content = '')
    {
        $client = self::createClient();
        $client->request($method, $url, [], [], [], $content);
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests translation message update case.
     */
    public function testEditTagAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'objectProperty' => 'name',
                'propertyValue' => 'foo_tag',
                'newPropertyValue' => 'updated_foo_tag',
            ]
        );

        $client->request(
            'POST',
            '/translate/_api/edit',
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

        $this->assertEquals('updated_foo_tag', $translation->getTags()[0]->getName(), 'tag should be updated.');
    }

    /**
     * Tests translation message update case.
     */
    public function testEditMessageAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'messages',
                'objectProperty' => 'message',
                'newPropertyValue' => 'updated_foo',
                'findBy' => [
                    'property' => 'locale',
                    'value' => 'en',
                ],
            ]
        );

        $client->request(
            'POST',
            '/translate/_api/edit',
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

        $this->assertEquals('updated_foo', $translation->getMessages()[0]->getMessage(), 'Message should be updated.');
    }

    /**
     * Tests get action on tags.
     */
    public function testGetTagsAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'objectProperty' => 'name',
            ]
        );

        $client->request(
            'GET',
            '/translate/_api/get',
            [],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isOk(), 'Controller response should be 200.');
        $this->assertEquals(
            ['foo_tag', 'tuna_tag', 'baz_tag'],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /**
     * Tests remove action.
     */
    public function testRemoveAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'objectProperty' => 'name',
                'propertyValue' => 'tuna_tag',
            ]
        );

        $client->request(
            'POST',
            '/translate/_api/delete',
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

        foreach ($translation->getTags() as $tag) {
            $this->assertNotEquals('tuna_tag', $tag->getName());
        }
    }

    /**
     * Test add action.
     */
    public function testAddAction()
    {
        $client = self::createClient();
        $id = sha1('foo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'objectProperty' => 'name',
                'propertyValue' => 'new_foo_tag',
            ]
        );

        $client->request(
            'POST',
            '/translate/_api/add',
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

        $tags = [];
        foreach ($translation->getTags() as $tag) {
            $tags[] = $tag->getName();
        }

        $key = array_search('new_foo_tag', $tags);
        $this->assertNotFalse($key, 'tag should exist');
        $this->assertEquals('new_foo_tag', $tags[$key], 'Tag should have name as defined in request.');
    }
}
