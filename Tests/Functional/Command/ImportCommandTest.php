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

use ONGR\ElasticsearchBundle\DSL\Filter\TermsFilter;
use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use ONGR\TranslationsBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ImportCommand tests.
 */
class ImportCommandTest extends AbstractElasticsearchTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester = null;

    /**
     * @var ImportCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $app = new Application(static::$kernel);
        $app->add($this->getImportCommand());

        $this->command = $app->find('ongr:translations:import');
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Test full import case.
     */
    public function testImportAllCommand()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
            ]
        );

        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['validators']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['ONGRTranslations']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['messages']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['security']));
    }

    /**
     * Test bundle import case.
     */
    public function testBundleImport()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'bundle' => 'AcmeTestBundle',
            ]
        );

        $this->assertEquals(0, $this->getTranslationsCount(['en'], ['validators']));
        $this->assertEquals(0, $this->getTranslationsCount(['en'], ['ONGRTranslation']));
        $this->assertEquals(0, $this->getTranslationsCount(['en'], ['security']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['messages']));
    }

    /**
     * Test one locale import.
     */
    public function testOneLocaleImport()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                '--locales' => ['lt'],
            ]
        );

        $this->assertEquals(0, $this->getTranslationsCount(['en'], ['validators']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lt'], ['validators']));
    }

    /**
     * Test two domains import.
     */
    public function testDomainsImport()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                '--locales' => ['lt'],
                '--domains' => ['messages', 'security'],
            ]
        );

        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['validators']));
        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['ONGRTranslation']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lt'], ['security']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lt'], ['messages']));
    }

    /**
     * Returns translations count.
     *
     * @param array $locales
     * @param array $domains
     *
     * @return int|void
     */
    private function getTranslationsCount($locales = [], $domains = [])
    {
        $esStorage = $this->getContainer()->get('ongr_translations.storage');

        $result = $esStorage->read($locales, $domains);

        return $result->count();
    }

    /**
     * Returns Import command with assigned container.
     *
     * @return ImportCommand
     */
    private function getImportCommand()
    {
        $command = new ImportCommand();
        $command->setContainer($this->getContainer());

        return $command;
    }
}
