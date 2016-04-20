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
        $this->validateBundles($container, $config['bundles']);

        $this->setElasticsearchStorage($config['repository'], $container);
        $this->setFiltersManager($config['repository'], $container);
        $this->setTranslationManager($config['repository'], $container);
        $this->setControllerManager($config['repository'], 'list', $container);
        $this->setControllerManager($config['repository'], 'translation', $container);
        $this->setHistoryManager($this->editRepositoryName($config['repository']), $container);
        if ($config['history']) {
            $this->setEditMessageEvent($this->editRepositoryName($config['repository']), $container);
        }
    }

    /**
     * Adds translations manager.
     *
     * @param string           $repositoryId Elasticsearch repository id.
     * @param ContainerBuilder $container    Service container.
     */
    private function setTranslationManager($repositoryId, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\TranslationsBundle\Translation\TranslationManager',
            [
                new Reference($repositoryId),
                new Reference('event_dispatcher'),
            ]
        );

        $container->setDefinition('ongr_translations.translation_manager', $definition);
    }

    /**
     * Adds history manager.
     *
     * @param string           $repositoryId
     * @param ContainerBuilder $container
     */
    private function setHistoryManager($repositoryId, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\TranslationsBundle\Translation\HistoryManager',
            [
                new Reference($repositoryId),
            ]
        );

        $container->setDefinition('ongr_translations.history_manager', $definition);
    }

    /**
     * Sets elasticsearch storage for translations.
     *
     * @param string           $repositoryId Elasticsearch repository id.
     * @param ContainerBuilder $container    Service container.
     */
    private function setElasticsearchStorage($repositoryId, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\TranslationsBundle\Storage\ElasticsearchStorage',
            [
                new Reference($repositoryId),
            ]
        );

        $container->setDefinition('ongr_translations.storage', $definition);
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
     * Injects elasticsearch repository to controller and sets it into service container.
     *
     * @param string           $repositoryId   Elasticsearch repository id.
     * @param string           $controllerName Controller name to which add repository.
     * @param ContainerBuilder $container      Service container.
     */
    private function setControllerManager($repositoryId, $controllerName, ContainerBuilder $container)
    {
        $definition = new Definition(
            sprintf('ONGR\TranslationsBundle\Controller\%sController', ucfirst($controllerName)),
            [
                new Reference($repositoryId),
            ]
        );
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);

        $container->setDefinition(sprintf('ongr_translations.controller.%s', $controllerName), $definition);
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

    /**
     * Validates edit message event.
     *
     * @param string           $repositoryId
     * @param ContainerBuilder $container
     */
    private function setEditMessageEvent($repositoryId, ContainerBuilder $container)
    {
        $definition = new Definition(
            'ONGR\TranslationsBundle\Event\HistoryListener',
            [
                new Reference($repositoryId),
            ]
        );
        $definition->addTag(
            'kernel.event_listener',
            ['event' => 'translation.history.add', 'method' => 'addToHistory']
        );
        $container->setDefinition('ongr_translations.es_manager', $definition);
    }

    /**
     * Edits repository name.
     *
     * @param string $repository
     *
     * @return string
     */
    private function editRepositoryName($repository)
    {
        return substr_replace($repository, 'history', strrpos($repository, '.') + 1);
    }
}
