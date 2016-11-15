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
use ONGR\TranslationsBundle\Document\History;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Service\HistoryManager;

class HistoryManagerTest extends AbstractElasticsearchTestCase
{
    /**
     * @var HistoryManager
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get('ongr_translations.history_manager');
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
                ],
                'history' => [
                    [
                        '_id' => 1,
                        'key' => 'foo.key',
                        'domain' => 'foo',
                        'message' => 'Lorum ipsum',
                        'locale' => 'en',
                    ],
                    [
                        '_id' => 2,
                        'key' => 'foo.key',
                        'domain' => 'foo',
                        'message' => 'Lorum',
                        'locale' => 'en',
                    ],
                ],
            ],
        ];
    }

    public function testGetHistory()
    {
        $messages = ['Lorum ipsum', 'Lorum'];
        $histories = $this->manager->get(
            $this->getContainer()->get('ongr_translations.translation_manager')->get('foo')
        );

        $this->assertArrayHasKey('en', $histories);

        /** @var History $history */
        foreach ($histories['en'] as $history) {
            $this->assertTrue(in_array($history->getMessage(), $messages));
        }
    }

    public function testAddHistory()
    {
        $translation = $this->getContainer()->get('ongr_translations.translation_manager')->get('foo');
        $message = $translation->getMessages()[0];
        $this->assertInstanceOf('ONGR\TranslationsBundle\Document\Message', $message);
        $this->manager->add($message, $translation);
        $this->getManager()->commit();
        $this->assertEquals(3, count($this->manager->get($translation)['en']));
    }
}
