<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * TranslatorPass to load translations loaders.
 */
class TranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $loadersReferences = [];

        $formats = $container->getParameter('ongr_translations.formats');

        foreach ($container->findTaggedServiceIds('translation.loader') as $id => $attributes) {
            if (!empty($formats)) {
                if (array_intersect($attributes[0], $formats)) {
                    $loadersReferences[$attributes[0]['alias']] = new Reference($id);
                } else {
                    continue;
                }
            } else {
                $loadersReferences[$attributes[0]['alias']] = new Reference($id);
            }
        }

        if ($container->hasDefinition('ongr_translations.file_import')) {
            $container->findDefinition('ongr_translations.file_import')->replaceArgument(0, $loadersReferences);
        }
    }
}
