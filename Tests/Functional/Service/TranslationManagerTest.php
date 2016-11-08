<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Service;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Service\TranslationManager;
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
                            'foo_tag',
                            'tuna_tag',
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
                        'tags' => ['baz_tag'],
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
     * Test for edit().
     */
    public function testEdit()
    {
        $body = [
            'tags' => ['bar_tag', 'tuna_tag'],
            'messages' => [
                'en' => 'bar',
                'lt' => 'baras',
            ]
        ];

        $request = new Request([], [], [], [], [], [], json_encode($body));
        $this->manager->edit('foo', $request);

        /** @var Translation $translation */
        $translation = $this->getManager()->find('ONGRTranslationsBundle:Translation', 'foo');
        $this->assertNotNull($translation);
        $messages = $translation->getMessages();

        $expectedTags = ['bar_tag', 'tuna_tag'];
        $this->assertEquals($expectedTags, $translation->getTags());

        $messagesArray = [];

        foreach ($messages as $message) {
            $messagesArray[$message->getLocale()] = $message->getMessage();
            $this->assertEquals('dirty', $message->getStatus());
        }

        $this->assertEquals(['en' => 'bar', 'lt' => 'baras'], $messagesArray);
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

    public function testGetTags()
    {
        $this->assertEquals(['baz_tag', 'foo_tag', 'tuna_tag'], $this->manager->getTags());
    }

    public function testGetDomains()
    {
        $this->assertEquals(['baz', 'foo'], $this->manager->getDomains());
    }
}
