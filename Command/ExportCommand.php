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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps translations from ES.
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
            'domains',
            'd',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Exports only these domains.',
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locales = $input->getOption('locales');
        if (empty($locales)) {
            $locales = $this->getContainer()->getParameter('ongr_translations.managed_locales');
        }

        $domains = $input->getOption('domains');

        $this->getContainer()->get('ongr_translations.export')->export($locales, $domains);
    }
}
