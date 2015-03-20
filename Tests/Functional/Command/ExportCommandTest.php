<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Command;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use ONGR\TranslationsBundle\Command\ExportCommand;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Translation\Export\YmlExport;
use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Test export command.
 */
class ExportCommandTest extends AbstractElasticsearchTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester = null;

    /**
     * @var ExportCommand
     */
    private $command;

    /**
     * @var string
     */
    private $translationsDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        vfsStream::setup('translations_test');

        $app = new Application(static::$kernel);
        $app->add($this->getExportCommand());

        $this->command = $app->find('ongr:translations:export');
        $this->commandTester = new CommandTester($this->command);
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
                        '_id' => 'trans1',
                        'domain' => 'foo_domain',
                        'key' => 'foo_key',
                        'path' => vfsStream::url('translations_test'),
                        'format' => 'yml',
                        'messages' => [
                            [
                                'locale' => 'en',
                                'message' => 'foo_message',
                                'status' => Message::DIRTY,
                            ],
                        ],
                    ],
                    [
                        '_id' => 'trans2',
                        'domain' => 'foo_domain',
                        'key' => 'bar_key',
                        'path' => vfsStream::url('translations_test'),
                        'format' => 'yml',
                        'messages' => [
                            [
                                'locale' => 'en',
                                'message' => 'bar_message',
                                'status' => Message::DIRTY,
                            ],
                        ],
                    ],
                    [
                        '_id' => 'trans3',
                        'domain' => 'baz_domain',
                        'key' => 'baz_key',
                        'path' => vfsStream::url('translations_test'),
                        'format' => 'yml',
                        'messages' => [
                            [
                                'locale' => 'lt',
                                'message' => 'baz_message',
                                'status' => Message::DIRTY,
                            ],
                        ],
                    ],
                    [
                        '_id' => 'trans4',
                        'domain' => 'buz_domain',
                        'key' => 'foo_key',
                        'path' => vfsStream::url('translations_test'),
                        'format' => 'yml',
                        'messages' => [
                            [
                                'locale' => 'lt',
                                'message' => 'foo_message',
                                'status' => Message::DIRTY,
                            ],
                        ],
                    ],
                    [
                        '_id' => 'trans5',
                        'domain' => 'buz_domain',
                        'key' => 'fresh_key',
                        'path' => vfsStream::url('translations_test'),
                        'format' => 'yml',
                        'messages' => [
                            [
                                'locale' => 'lt',
                                'message' => 'fresh_foo_message',
                                'status' => Message::FRESH,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests if Yml file is created and formed correctly.
     *
     * @param array $data
     */
    public function verifyFiles($data)
    {
        foreach ($data as $fileName => $translations) {
            $path = vfsStream::url('translations_test' . DIRECTORY_SEPARATOR . $fileName);

            $this->assertFileExists($path, 'Translation file should exist');
            $dumpedData = Yaml::parse(file_get_contents($path));

            $this->assertEquals($translations, $dumpedData, 'Translations should be the same.');
        }
    }

    /**
     * Test export command.
     */
    public function testExportCommand()
    {
        $this->commandTester->execute([]);

        $data = [
            'baz_domain.lt.yml' =>
                ['baz_key' => 'baz_message'],
            'foo_domain.en.yml' => [
                'foo_key' => 'foo_message',
                'bar_key' => 'bar_message',
            ],
        ];

        $this->verifyFiles($data);
    }

    /**
     * Test export by domain command.
     */
    public function testExportByDomains()
    {
        $this->commandTester->execute(
            [
                '--domains' => ['baz_domain'],
            ]
        );

        $data = [
            'baz_domain.lt.yml' =>
                ['baz_key' => 'baz_message'],
        ];

        $this->verifyFiles($data);
    }

    /**
     * Test export by domain command.
     */
    public function testExportByLocales()
    {
        $this->commandTester->execute(
            [
                '--locales' => ['en'],
            ]
        );

        $data = [
            'foo_domain.en.yml' => [
                'foo_key' => 'foo_message',
                'bar_key' => 'bar_message',
            ],
        ];

        $this->verifyFiles($data);
    }

    /**
     * Test if destination translations are merged correctly.
     */
    public function testExportMerge()
    {
        $dummyData = [
            'buz_domain.lt.yml' => [
                'bar_key' => 'bar_message',
            ],
        ];

        $this->createDummyFileWithData($dummyData);

        $this->commandTester->execute([]);

        $data = [
            'buz_domain.lt.yml' => [
                'foo_key' => 'foo_message',
                'bar_key' => 'bar_message',
            ],
        ];

        $this->verifyFiles($data);
    }

    /**
     * Returns Export command with assigned container.
     *
     * @return ExportCommand
     */
    private function getExportCommand()
    {
        $command = new ExportCommand();
        $command->setContainer($this->getContainer());

        return $command;
    }

    /**
     * Create dummy file with data.
     *
     * @param array $dummyData
     */
    private function createDummyFileWithData($dummyData)
    {
        $exporter = new YmlExport();
        foreach ($dummyData as $file => $translations) {
            $exporter->export(vfsStream::url('translations_test' . DIRECTORY_SEPARATOR . $file), $translations);
        }
    }
}
