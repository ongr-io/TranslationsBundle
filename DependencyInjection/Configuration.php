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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_translations');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('repository')
                    ->info('Repository used for connecting with elasticsearch client.')
                    ->children()
                        ->scalarNode('translation')->isRequired()
                        ->end()
                        ->scalarNode('history')->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('list_size')
                    ->info('Maximum amount of translations displayed in the list')
                    ->defaultValue(1000)
                ->end()
                ->arrayNode('locales')
                    ->requiresAtleastOneElement()
                    ->info('Locales to manage (e.g. "en", "de", "en_eur").')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('bundles')
                    ->info('Bundles to scan for translations.')
                    ->prototype('scalar')
                    ->defaultValue([])
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
