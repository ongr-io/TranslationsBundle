<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\Compiler;

use ONGR\TranslationsBundle\DependencyInjection\Compiler\TranslatorPass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Tests TranslatorPass.
 */
class TranslatorPassTest extends WebTestCase
{
    /**
     * Data provider for testLoadersContainerConfiguration.
     *
     * @return array
     */
    public function getConfigurationData()
    {
        $out = [];

        // Case #0 Formats not specified, all container loaders added.
        $out[] = [
            [],
            [
                'yml',
                'xlf',
            ],
            2,
        ];
        // Case #1 Yml format registered, one translation loader added.
        $out[] = [
            [
                'yml',
            ],
            [
                'yml',
                'xlf',
            ],
            1,
        ];

        return $out;
    }

    /**
     * Test loaders container configuration.
     *
     * @param array $registeredFormats
     * @param array $containerLoaders
     * @param array $loaded
     *
     * @dataProvider getConfigurationData
     */
    public function testLoadersContainerConfiguration($registeredFormats, $containerLoaders, $loaded)
    {
        $container = new ContainerBuilder();
        $this->setRegisteredFormats($registeredFormats, $container);
        $this->registerLoaders($containerLoaders, $container);

        $translatorPass = new TranslatorPass();
        $translatorPass->process($container);

        $loadersContainer = $container->getDefinition('ongr_translations.loaders_container');

        $this->assertTrue($loadersContainer->hasMethodCall('set'), 'There should be registered method call.');
        $this->assertEquals(
            $loaded,
            count($loadersContainer->getMethodCalls()),
            'Wrong loaders count in LoadersContainer'
        );
    }

    /**
     * Mock container builder with translation loaders.
     *
     * @param array            $containerLoaders
     * @param ContainerBuilder $container
     */
    public function registerLoaders($containerLoaders, $container)
    {
        foreach ($containerLoaders as $extension) {
            $loader = new Definition(sprintf('Symfony\Component\Translation\Loader\%sFileLoader', ucfirst($extension)));
            $loader->addTag('translation.loader', ['alias' => $extension]);

            $container->setDefinition(sprintf('translations_loader.%s', $extension), $loader);
        }
    }

    /**
     * Set registered formats to container.
     *
     * @param array            $registeredFormats
     * @param ContainerBuilder $container
     */
    public function setRegisteredFormats($registeredFormats, $container)
    {
        $container->setParameter('ongr_translations.formats', $registeredFormats);
    }
}
