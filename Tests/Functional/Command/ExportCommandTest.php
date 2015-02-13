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
use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

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
     * @var vfsStream
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->remoteTranslations();

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
                        'locale' => 'en',
                        'key' => 'foo_key',
                        'message' => 'foo_message',
                    ],
                    [
                        '_id' => 'trans2',
                        'domain' => 'foo_domain',
                        'locale' => 'en',
                        'key' => 'bar_key',
                        'message' => 'bar_message',
                    ],
                    [
                        '_id' => 'trans3',
                        'domain' => 'baz_domain',
                        'locale' => 'lt',
                        'key' => 'baz_key',
                        'message' => 'baz_message',
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
        $root = $this->getContainer()->getParameter('kernel.root_dir');
        foreach ($data as $fileName => $translations) {
            $path = $root . '/Resources/translations/' . $fileName;

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
     * Remove testing files.
     */
    private function remoteTranslations()
    {
        $root = $this->getContainer()->getParameter('kernel.root_dir');
        $files = ['baz_domain.lt.yml', 'foo_domain.en.yml'];
        foreach ($files as $file) {
            $path = $root . '/Resources/translations/' . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->remoteTranslations();
    }
}
