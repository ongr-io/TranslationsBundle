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
use ONGR\TranslationsBundle\Document\Message;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests rest controller actions.
 */
class ApiControllerTest extends AbstractElasticsearchTestCase
{
    const STREAM = 'translations_ctrl_api_test';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        vfsStream::setup(self::STREAM);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'translation' => [
                    [
                        '_id' => sha1('foofoo.key'),
                        'key' => 'foo.key',
                        'domain' => 'foo',
                        'path' => vfsStream::url(self::STREAM),
                        'format' => 'yml',
                        'tags' => [
                            [
                                'name' => 'foo_tag',
                            ],
                            [
                                'name' => 'tuna_tag',
                            ],
                        ],
                        'messages' => [
                            [
                                'locale' => 'en',
                                'message' => 'foo',
                                'status' => Message::FRESH,
                            ],
                        ],
                    ],
                    [
                        '_id' => sha1('bazbaz.key'),
                        'key' => 'baz.key',
                        'domain' => 'baz',
                        'path' => vfsStream::url(self::STREAM),
                        'format' => 'yml',
                        'tags' => [
                            [
                                'name' => 'baz_tag',
                            ],
                        ],
                        'messages' => [
                            [
                                'locale' => 'en',
                                'message' => 'baz',
                                'status' => Message::DIRTY,
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
    public function getTestActionStatusCodeData()
    {
        return [
            // Case #0.
            ['POST', '/translate/_api/edit', 400],
            // Case #1.
            ['POST', '/translate/_api/edit', 400, json_encode(['id' => 2])],
            // Case #2.
            ['POST', '/translate/_api/edit', 400, '{}'],
            // Case #3.
            [
                'POST',
                '/translate/_api/get',
                200,
                json_encode(
                    [
                        'id' => sha1('foo.key'),
                        'name' => 'tags',
                    ]
                ),
            ],
            // Case #4.
            ['POST', '/translate/_api/check', 400],
            // Case #5.
            ['POST', '/translate/_api/check', 400, json_encode(['message' => 'foo'])],
            // Case #6.
            ['POST', '/translate/_api/check', 200, json_encode(['message' => 'foo', 'locale' => 'en'])],
            // Case #7.
            [
                'POST',
                '/translate/_api/check',
                406,
                json_encode(
                    [
                        'message' => '{0 There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
                        'locale' => 'en',
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
     * @dataProvider getTestActionStatusCodeData
     */
    public function testActionStatusCode($method, $url, $statusCode, $content = '')
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
        $id = sha1('foofoo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'properties' => [
                    'name' => 'updated_foo_tag',
                ],
                'findBy' => [
                    'name' => 'foo_tag',
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

        $this->assertEquals('updated_foo_tag', $translation->getTags()[0]->getName(), 'tag should be updated.');
    }

    /**
     * Tests translation message update case.
     */
    public function testEditMessageAction()
    {
        $client = self::createClient();
        $id = sha1('foofoo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'messages',
                'properties' => [
                    'message' => 'updated_foo',
                ],
                'findBy' => [
                    'locale' => 'en',
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
        $id = sha1('foofoo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'properties' => ['name'],
            ]
        );

        $client->request(
            'POST',
            '/translate/_api/get',
            [],
            [],
            [],
            $requestContent
        );

        $this->assertTrue($client->getResponse()->isOk(), 'Controller response should be 200.');
        $this->assertEquals(
            [
                [
                    'tags' => [
                        ['name' => 'baz_tag'],
                    ],
                ],
                [
                    'tags' => [
                        ['name' => 'foo_tag'],
                        ['name' => 'tuna_tag'],
                    ],
                ],
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /**
     * Tests remove action.
     */
    public function testRemoveAction()
    {
        $client = self::createClient();
        $id = sha1('foofoo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'findBy' => [
                    'name' => 'tuna_tag',
                ],
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
        $id = sha1('foofoo.key');

        $requestContent = json_encode(
            [
                'id' => $id,
                'name' => 'tags',
                'properties' => [
                    'name' => 'new_foo_tag',
                ],
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

    /**
     * Tests export action.
     */
    public function testExportAction()
    {
        $client = self::createClient();
        $currentDir = getcwd();
        $webDir = $currentDir . DIRECTORY_SEPARATOR . 'web';

        if (!is_dir($webDir)) {
            mkdir($webDir);
        }

        chdir($webDir);

        $client->request('post', '/translate/_api/export');
        $path = vfsStream::url(self::STREAM . DIRECTORY_SEPARATOR . 'baz.en.yml');

        $this->assertFileExists($path, 'Translation file should exist');
        $dumpedData = Yaml::parse(file_get_contents($path));

        $this->assertEquals(['baz.key' => 'baz'], $dumpedData, 'Translations should be the same.');
        $document = $this
            ->getManager('default', false)
            ->getRepository('ONGRTranslationsBundle:Translation')
            ->find(sha1('bazbaz.key'));

        $this->assertEquals(Message::FRESH, $document->getMessages()[0]->getStatus(), 'Message should be refreshed');
        $this->assertEquals($currentDir, getcwd());

        rmdir($webDir);
    }
}
