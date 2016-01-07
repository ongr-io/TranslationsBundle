<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Translation;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Translation\TranslationManager;
use Symfony\Component\HttpFoundation\Request;

class TranslationManagerTest extends AbstractElasticsearchTestCase
{
    /**
     * @var TranslationManager
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get('ongr_translations.translation_manager');
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
                        '_id' => 'foo',
                        'key' => 'foo.key',
                        'domain' => 'foo',
                        'path' => '',
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
                        'path' => '',
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
                'history' => [
                    [
                        '_id' => 1,
                        'key' => 'foo',
                        'message' => 'Lorum ipsum',
                        'domain' => 'barbar',
                        'locale' => 'en',
                        'historyId' => sha1('foo.en.barbar'),
                    ],
                    [
                        '_id' => 2,
                        'key' => 'foo',
                        'message' => 'Lorum',
                        'domain' => 'barbar',
                        'locale' => 'en',
                        'historyId' => sha1('foo.en.barbar'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Test for add().
     */
    public function testAdd()
    {
        $body = ['id' => 'foo', 'name' => 'tags', 'properties' => ['name' => 'bar_tag']];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $this->manager->add($request);

        $translation = $this->getManager()->find('ONGRTranslationsBundle:Translation', 'foo');
        $this->assertNotNull($translation);

        $this->assertCount(3, $translation->getTags());
    }

    /**
     * Test for edit().
     */
    public function testEdit()
    {
        $body = [
            'id' => 'foo',
            'name' => 'tags',
            'properties' => ['name' => 'bar_tag'],
            'findBy' => ['name' => 'foo_tag'],
        ];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $this->manager->edit($request);

        $translation = $this->getManager()->find('ONGRTranslationsBundle:Translation', 'foo');
        $this->assertNotNull($translation);

        $tags = [];
        foreach ($translation->getTags() as $tag) {
            $tags[] = $tag->getName();
        }

        $expectedTags = ['bar_tag', 'tuna_tag'];
        $this->assertEquals($expectedTags, $tags);
    }

    /**
     * Test for delete().
     */
    public function testDelete()
    {
        $body = [
            'id' => 'foo',
            'name' => 'tags',
            'findBy' => ['name' => 'foo_tag'],
        ];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $this->manager->delete($request);

        $translation = $this->getManager()->find('ONGRTranslationsBundle:Translation', 'foo');
        $this->assertNotNull($translation);

        $tags = [];
        foreach ($translation->getTags() as $tag) {
            $tags[] = $tag->getName();
        }

        $expectedTags = ['tuna_tag'];
        $this->assertEquals($expectedTags, $tags);
    }

    /**
     * Test for get().
     */
    public function testGet()
    {
        $body = ['name' => 'messages', 'findBy' => ['status' => Message::DIRTY]];
        $request = new Request([], [], [], [], [], [], json_encode($body));
        $result = $this->manager->get($request);

        $this->assertCount(1, $result);
        $this->assertEquals('baz.key', reset($result)['key']);
    }
}
