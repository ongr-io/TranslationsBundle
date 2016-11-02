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
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\TranslationsBundle\Command\ImportCommand;
use Symfony\Component\Console\Application;
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

        // Trigger index creation
        $this->getManager();
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
        $this->assertEquals(0, $this->getTranslationsCount(['en'], ['security']));
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
                '--locales' => ['lt', 'lv'],
                '--domains' => ['messages', 'security'],
            ]
        );

        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['validators']));
        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['ONGRTranslation']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lv'], ['security']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lt'], ['messages']));
    }

    /**
     * Test 'config-only' bundles import.
     */
    public function testConfigOnlyOptionImport()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                '--config-only' => '--config-only',
            ]
        );
        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['validators']));
        $this->assertEquals(0, $this->getTranslationsCount(['lt'], ['ONGRTranslation']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['en'], ['messages']));
        $this->assertGreaterThan(0, $this->getTranslationsCount(['lt'], ['messages']));
    }

    /**
     * Tests if exception is thrown when unknown bundle is provided.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectBundleImportException()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'bundle' => 'Acme\AcmeTestBundle',
            ]
        );
    }

    /**
     * Returns translations count.
     *
     * @param array $locales
     * @param array $domains
     *
     * @return int|void
     */
    private function getTranslationsCount(array $locales = [], array $domains = [])
    {
        $repository = $this->getContainer()->get('ongr_translations.repository');

        $search = $repository
            ->createSearch()
            ->setScroll('2m')
            ->addQuery(new MatchAllQuery());
        if (!empty($locales)) {
            $search->addFilter(new TermsQuery('messages.locale', $locales));
        }
        if (!empty($domains)) {
            $search->addFilter(new TermsQuery('domain', $domains));
        }
        $result =  $repository->findDocuments($search);

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
