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
                ->scalarNode('es_manager')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('fallback_locale')
                    ->defaultValue('en')
                ->end()
                ->arrayNode('managed_locales')
                    ->isRequired()
                    ->requiresAtleastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
