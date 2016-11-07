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

        $container->setParameter('ongr_translations.managed_locales', $config['managed_locales']);
        $container->setParameter('ongr_translations.formats', $config['formats']);
        $container->setParameter('ongr_translations.domains', $config['domains']);
        $container->setParameter('ongr_translations.list_size', $config['list_size']);
        $container->setAlias('ongr_translations.translation_repository', $config['repository']);
        $container->setAlias(
            'ongr_translations.history_repository',
            substr_replace($config['repository'], 'history', strrpos($config['repository'], '.') + 1)
        );
        $this->validateBundles($container, $config['bundles']);

        $this->setFiltersManager($config['repository'], $container);
    }

    /**
     * Adds filter manager for displaying translations gui.
     *
     * @param string           $repositoryId Elasticsearch repository id.
     * @param ContainerBuilder $container    Service container.
     */
    private function setFiltersManager($repositoryId, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\FilterManagerBundle\Search\FilterManager',
            [
                new Reference('ongr_translations.filters_container'),
                new Reference($repositoryId),
            ]
        );

        $container->setDefinition('ongr_translations.filter_manager', $definition);
    }

    /**
     * Validates configured bundles and sets into service container as parameter.
     *
     * @param ContainerBuilder $container Service container.
     * @param array            $bundles   Bundles array.
     *
     * @throws InvalidConfigurationException
     */
    private function validateBundles($container, $bundles)
    {
        foreach ($bundles as $bundle) {
            if (!class_exists($bundle)) {
                throw new InvalidConfigurationException(
                    "Invalid bundle namespace '{$bundle}'."
                );
            }
        }
        $container->setParameter('ongr_translations.bundles', $bundles);
    }
}
