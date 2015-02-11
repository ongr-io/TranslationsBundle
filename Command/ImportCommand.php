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
        $this->addOption(
            'locales',
            'l',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Import only for these locales, instead of using the managed locales.'
        );
        $this->addOption(
            'domains',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Only imports files for given domains (comma separated).'
        );
        $this->addArgument('bundle', InputArgument::OPTIONAL, 'Import translations for this specific bundle.', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $import = $this->getContainer()->get('ongr_translations.import');

        $locales = $this->input->getOption('locales');
        if (empty($locales)) {
            $locales = $this->getContainer()->getParameter('ongr_translations.managed_locales');
        }
        $domains = $input->getOption('domains') ? explode(',', $input->getOption('domains')) : [];
        $bundleName = $this->input->getArgument('bundle');
        $import->setLocales($locales);
        $import->setDomains($domains);
        if ($bundleName) {
            $bundle = $this->getApplication()->getKernel()->getBundle($bundleName);
            $import->importBundleTranslationFiles($bundle->getPath());
        } else {
            $this->output->writeln('<info>*** Importing application translation files ***</info>');
            $import->importAppTranslationFiles($locales, $domains);
            if (!$this->input->getOption('globals')) {
                $this->output->writeln('<info>*** Importing bundles translation files ***</info>');
                $import->importBundlesTranslationFiles($locales, $domains);
                $this->output->writeln('<info>*** Importing component translation files ***</info>');
                $import->importComponentTranslationFiles($locales, $domains);
            }
        }
        $import->writeToStorage();
    }
}
