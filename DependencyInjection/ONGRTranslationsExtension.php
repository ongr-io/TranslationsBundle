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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
        $loader->load('filters_container.yml');

        $container->setParameter('ongr_translations.locales', $config['locales']);
        $container->setParameter('ongr_translations.list_size', $config['list_size']);
        $container->setParameter('ongr_translations.bundles', $config['bundles']);
        $container->setAlias('ongr_translations.repository.translation', $config['repository']['translation']);
        $container->setAlias('ongr_translations.repository.history', $config['repository']['history']);

        $this->setFiltersManager($config['repository']['translation'], $container);
    }

    /**
     * Adds filter manager for displaying translations gui.
     *
     * @param string           $repository Elasticsearch repository id.
     * @param ContainerBuilder $container  Service container.
     */
    private function setFiltersManager($repository, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\FilterManagerBundle\Search\FilterManager',
            [
                new Reference('ongr_translations.filters_container'),
                new Reference($repository),
                new Reference('event_dispatcher'),
                new Reference('jms_serializer')
            ]
        );

        $container->setDefinition('ongr_translations.filter_manager', $definition);
    }
}
