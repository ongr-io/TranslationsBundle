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

use ONGR\TranslationsBundle\Service\Export\ExportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exports translations.
 */
class ExportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ongr:translations:export');
        $this->setDescription('Export all translations from ES to yaml.');
        $this->addOption(
            'locales',
            'l',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Export only these locales, instead of using the managed locales.'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'If set, the bundle will export all translations, regardless of status'
        );
        $this->addArgument(
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
        /** @var ExportManager $export */
        $export = $this->getContainer()->get('ongr_translations.export');
        $bundle = $input->getArgument('bundle');

        $locales = $input->getOption('locales', $this->getContainer()->getParameter('ongr_translations.locales'));
        $export->setLocales($locales);

        if ($bundle) {
//            $output->writeln("<info>*** Importing {$bundleName} translation files ***</info>");
//            $bundle = $this->getContainer()->get('kernel')->getBundle($bundleNames);
//
//            foreach ($bundleNames as $bundleName) {
//                $dir = $this->getContainer()->get('kernel')->getBundle($bundleName);
//                $import->importTranslationFiles($bundle);
//            }
        } else {
            $export->export(['messages'], $input->getOption('force'));
            $export->export($this->getContainer()->getParameter('ongr_translations.bundles'), $input->getOption('force'));
        }

        $prettify = function ($array) {
            return !empty($array) ? implode('</comment><info>`, `</info><comment>', $array) : 'all';
        };

        $output->writeln(
            sprintf(
                '<info>Successfully exported translations in `</info>'
                . '<comment>%s</comment><info>` locale(s) for `</info>',
                $prettify($locales)
            )
        );
    }
}
