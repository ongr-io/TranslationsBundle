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
use Symfony\Component\DependencyInjection\Definition;
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
        $formats = $container->getParameter('ongr_translations.formats');
        $loadersContainer = new Definition('ONGR\TranslationsBundle\Service\LoadersContainer');

        foreach ($container->findTaggedServiceIds('translation.loader') as $id => $attributes) {
            if (!empty($formats)) {
                if (array_intersect($attributes[0], $formats)) {
                    $this->addLoader($loadersContainer, $attributes, $id);
                }
            } else {
                $this->addLoader($loadersContainer, $attributes, $id);
            }
        }

        $container->setDefinition('ongr_translations.loaders_container', $loadersContainer);

        $this->setImportLoadersContainer($container, $loadersContainer);
        $this->setExportLoadersContainer($container, $loadersContainer);
        $this->setComponentDirectories($container);
    }

    /**
     * Adds loader to LoadersContainer definition.
     *
     * @param Definition $loadersContainer
     * @param array      $attributes
     * @param string     $id
     */
    public function addLoader($loadersContainer, $attributes, $id)
    {
        $loadersContainer->addMethodCall(
            'set',
            [$attributes[0]['alias'], new Reference($id)]
        );
    }

    /**
     * Set import service LoadersContainer.
     *
     * @param ContainerBuilder $container
     * @param Definition       $loadersContainer
     */
    public function setImportLoadersContainer(ContainerBuilder $container, $loadersContainer)
    {
        if ($container->hasDefinition('ongr_translations.file_import')) {
            $container->findDefinition('ongr_translations.file_import')->replaceArgument(0, $loadersContainer);
        }
    }

    /**
     * Set export service LoadersContainer.
     *
     * @param ContainerBuilder $container
     * @param Definition       $loadersContainer
     */
    public function setExportLoadersContainer(ContainerBuilder $container, $loadersContainer)
    {
        if ($container->hasDefinition('ongr_translations.export')) {
            $container->findDefinition('ongr_translations.export')->replaceArgument(0, $loadersContainer);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function setComponentDirectories(ContainerBuilder $container)
    {
        $container->setParameter(
            'ongr_translations.component_directories',
            [
                'Symfony\Component\Validator\ValidatorBuilder',
                'Symfony\Component\Form\Form',
            ]
        );
    }
}
