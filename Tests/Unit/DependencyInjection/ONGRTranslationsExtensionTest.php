<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\DependencyInjection;

use ONGR\TranslationsBundle\DependencyInjection\ONGRTranslationsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class holds unit tests for ongr translations bundle extension.
 */
class ONGRTranslationsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if exception is thown when unknown bundle is provided.
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid bundle namespace 'Acme/AcmeTestBundle'.
     */
    public function testInvalidBundleName()
    {
        $config = [
            'ongr_translations' => [
                'repository' => 'es.manager.default.product',
                'bundles' => [
                    'Acme/AcmeTestBundle',
                ],
            ],
        ];

        $container = new ContainerBuilder();
        $extension = new ONGRTranslationsExtension();

        $extension->load($config, $container);
    }
}
