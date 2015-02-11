<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages bundle configuration.
 */
class ONGRTranslationsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ongr_translations.managed_locales', $config['managed_locales']);
        $container->setParameter('ongr_translations.formats', $config['formats']);
        $container->setParameter('ongr_translations.domains', $config['domains']);

        $this->setElasticsearchStorage($config['es_manager'], $container);
    }

    /**
     * Sets elasticsearch storage for translations.
     *
     * @param string           $managerName
     * @param ContainerBuilder $container
     */
    private function setElasticsearchStorage($managerName, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\TranslationsBundle\Storage\ElasticsearchStorage',
            [
                new Reference("es.manager.{$managerName}"),
            ]
        );

        $container->setDefinition('ongr_translations.storage', $definition);
    }
}
