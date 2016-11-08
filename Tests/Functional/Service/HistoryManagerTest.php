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
                        'tags' => ['foo_tag', 'tuna_tag'],
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
                        'translation' => 'foo',
                        'message' => 'Lorum ipsum',
                        'locale' => 'en',
                    ],
                    [
                        '_id' => 2,
                        'translation' => 'foo',
                        'message' => 'Lorum',
                        'locale' => 'en',
                    ],
                ],
            ],
        ];
    }

    public function testGetHistory()
    {
        $histories = $this->manager->getHistory('foo');

        $this->assertEquals(2, count($histories));

        $messages = [];

        foreach ($histories as $history) {
            $messages[] = $history->getMessage();
        }

        $this->assertEquals(['Lorum ipsum', 'Lorum'], $messages);
    }

    public function testGetOrderedHistory()
    {
        $orderedHistories = $this->manager->getOrderedHistory('foo');
        $histories = $this->manager->getHistory('foo');
        $unorderedHistories = [];

        foreach ($histories as $history) {
            $unorderedHistories[] = $history;
        }

        $this->assertEquals(['en' => $unorderedHistories], $orderedHistories);
    }

    public function testAddHistory()
    {
        $translation = $this->getManager()->getRepository('ONGRTranslationsBundle:Translation')->find('foo');
        $message = $translation->getMessages()[0];
        $this->assertInstanceOf('ONGR\TranslationsBundle\Document\Message', $message);
        $this->manager->addHistory($message, 'foo', 'en');
        $this->getManager()->commit();
        $this->assertEquals(3, count($this->manager->getHistory('foo')));
    }
}
