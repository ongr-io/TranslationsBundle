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
        $this->setDescription('Import all translations from flat files into the elasticsearch database.');
        $this->addOption(
            'locales',
            'l',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Import only the specific locales, leave blank to import all configured.'
        )
        ->addOption(
            'clean',
            null,
            InputOption::VALUE_NONE,
            'Clean not found translations keys'
        )
        ->addArgument(
            'bundle',
            InputArgument::OPTIONAL,
            'Import translations for the specific bundle.'
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
            $locales = $this->getContainer()->getParameter('ongr_translations.locales');
        }

        $clean = $input->getOption('clean');

        $import->setLocales($locales);

        $bundleNames = $input->getArgument('bundle');

        if ($bundleNames) {
//            $output->writeln("<info>*** Importing {$bundleName} translation files ***</info>");
//            $bundle = $this->getContainer()->get('kernel')->getBundle($bundleNames);
//
//            foreach ($bundleNames as $bundleName) {
//                $dir = $this->getContainer()->get('kernel')->getBundle($bundleName);
//                $import->importTranslationFiles($bundle);
//            }
        } else {
            $output->writeln('<info>*** Importing application translation files ***</info>');
            $domain = 'messages';
            $translations = $import->getTranslationsFromFiles(
                $domain,
                null,
                [$this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'translations']
            );

            $import->writeToStorage($domain, $translations);
            $output->writeln('<info>*** Importing bundles translation files ***</info>');

            if (true === $clean) {
                $output->writeln('<info>*** Cleaning old translation keys from elasticsearch ***</info>');
                $import->cleanTranslations($translations);
            }

            foreach ($configBundles as $configBundle) {
                $import->importTranslationFiles(
                    $configBundle,
                    $this->getContainer()->get('kernel')->locateResource('@'.$configBundle)
                );
            }
//            $output->writeln('<info>*** Importing component translation files ***</info>');
//            $import->importBundlesTranslationFiles(
//                $this->getContainer()->getParameter('ongr_translations.component_directories')
//            );
        }
    }
}
