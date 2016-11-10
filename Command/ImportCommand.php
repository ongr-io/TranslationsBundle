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

use ONGR\TranslationsBundle\Service\Import\ImportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Translations import command.
 */
class ImportCommand extends ContainerAwareCommand
{
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
        /** @var ImportManager $import */
        $import = $this->getContainer()->get('ongr_translations.import');
        $configBundles = $this->getContainer()->getParameter('ongr_translations.bundles');

        $locales = $input->getOption('locales');
        if (empty($locales)) {
            $locales = $this->getContainer()->getParameter('ongr_translations.managed_locales');
        }
        $domains = $input->getOption('domains');

        $bundleName = $input->getArgument('bundle');

        $import->setLocales($locales);
        $import->setDomains($domains);

        if ($input->getOption('config-only')) {
            $output->writeln('<info>*** Importing configured bundles translation files ***</info>');
            $import->importBundlesTranslationFiles($configBundles);
        } else {
            if ($bundleName) {
                $output->writeln("<info>*** Importing {$bundleName} translation files ***</info>");
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
                $import->importBundlesTranslationFiles([$bundle], true);
            } else {
                $output->writeln('<info>*** Importing application translation files ***</info>');
                $import->importDirTranslationFiles($this->getContainer()->getParameter('kernel.root_dir'));
                if (!$input->getOption('globals')) {
                    $output->writeln('<info>*** Importing bundles translation files ***</info>');
                    $import->importBundlesTranslationFiles(
                        array_merge($this->getContainer()->getParameter('kernel.bundles'), $configBundles)
                    );
                    $output->writeln('<info>*** Importing component translation files ***</info>');
                    $import->importComponentTranslationFiles();
                }
            }
        }
        $import->writeToStorage();
    }
}
