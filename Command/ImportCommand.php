<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Command;

use InvalidArgumentException;
use ONGR\TranslationsBundle\Service\Import;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Translations import command.
 */
class ImportCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ongr:translations:import');
        $this->setDescription('Import all translations from flat files (xliff, yml, php) into the database.');
        $this->addOption('globals', 'g', InputOption::VALUE_NONE, 'Import only globals (app/Resources/translations.');
        $this->addOption('config-only', 'c', InputOption::VALUE_NONE, 'Import only bundles specified in config.');
        $this->addOption(
            'locales',
            'l',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Import only for these locales, instead of using the managed locales.'
        );
        $this->addOption(
            'domains',
            'd',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Import only these domains.',
            []
        );
        $this->addArgument(
            'bundle',
            InputArgument::OPTIONAL,
            'Import translations for this specific bundle. Provide full bundles namespace.',
            null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        /** @var Import $import */
        $import = $this->getContainer()->get('ongr_translations.import');

        $locales = $this->input->getOption('locales');
        if (empty($locales)) {
            $locales = $this->getContainer()->getParameter('ongr_translations.managed_locales');
        }
        $domains = $input->getOption('domains');

        $bundleName = $this->input->getArgument('bundle');

        $import->setLocales($locales);
        $import->setDomains($domains);

        if ($input->getOption('config-only')) {
            $this->output->writeln('<info>*** Importing configured bundles translation files ***</info>');
            $import->importBundlesTranslationFiles($import->getConfigBundles());
        } else {
            if ($bundleName) {
                $this->validateBundleNamespace($bundleName);
                $this->output->writeln("<info>*** Importing {$bundleName} translation files ***</info>");
                $import->importBundlesTranslationFiles([$bundleName]);
            } else {
                $this->output->writeln('<info>*** Importing application translation files ***</info>');
                $import->importAppTranslationFiles();
                if (!$this->input->getOption('globals')) {
                    $this->output->writeln('<info>*** Importing bundles translation files ***</info>');
                    $import->importBundlesTranslationFiles(
                        array_merge($import->getBundles(), $import->getConfigBundles())
                    );
                    $this->output->writeln('<info>*** Importing component translation files ***</info>');
                    $import->importComponentTranslationFiles();
                }
            }
        }
        $import->writeToStorage();
    }

    /**
     * Check if provided bundles namespace is correct.
     *
     * @param string $bundleName
     *
     * @throws InvalidArgumentException
     */
    private function validateBundleNamespace($bundleName)
    {
        if (!class_exists($bundleName)) {
            throw new InvalidArgumentException(
                "Invalid bundle namespace '{$bundleName}'"
            );
        }
    }
}
