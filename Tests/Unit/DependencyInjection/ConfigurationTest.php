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

use ONGR\TranslationsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns default configuration for bundle.
     *
     * @return array
     */
    public function getTestConfigurationData()
    {
        $expectedConfiguration = [
            'repository' => 'es.manager.default.default',
            'managed_locales' => [],
            'formats' => [],
            'domains' => [],
            'bundles' => [],
        ];

        $out = [];

        // Case #0 Default values.
        $out[] = [
            [
                'repository' => 'es.manager.default.default',
                'managed_locales' => [
                    'en',
                ],
            ],
            array_replace(
                $expectedConfiguration,
                [
                    'managed_locales' => [
                        'en',
                    ],
                    'history' => true,
                ]
            ),
        ];

        // Case #1 Custom values.
        $out[] = [
            [
                'repository' => 'es.manager.default.foo',
                'managed_locales' => [
                    'en',
                    'fr',
                ],
                'history' => false,
            ],
            array_replace(
                $expectedConfiguration,
                [
                    'repository' => 'es.manager.default.foo',
                    'managed_locales' => [
                        'en',
                        'fr',
                    ],
                    'history' => false,
                ]
            ),
        ];

        // Case #2 Specific translation files format.
        $out[] = [
            [
                'repository' => 'es.manager.default.default',
                'managed_locales' => [
                    'en',
                ],
                'formats' => [
                    'yml',
                    'xlf',
                ],
                'history' => true,
            ],
            array_replace(
                $expectedConfiguration,
                [
                    'managed_locales' => [
                        'en',
                    ],
                    'formats' => [
                        'yml',
                        'xlf',
                    ],
                    'history' => true,
                ]
            ),
        ];

        // Case #3 No managed locales.
        $out[] = [
            [
                'repository' => 'es.manager.default.default',
                'managed_locales' => [],
            ],
            $expectedConfiguration,
            true,
            'The path "ongr_translations.managed_locales" should have at least 1 element(s) defined.',
        ];

        // Case #4 Specific translation domains.
        $out[] = [
            [
                'repository' => 'es.manager.default.default',
                'managed_locales' => [
                    'en',
                ],
                'domains' => [
                    'messages',
                ],
                'history' => true,
            ],
            array_merge(
                $expectedConfiguration,
                [
                    'managed_locales' => [
                        'en',
                    ],
                    'domains' => [
                        'messages',
                    ],
                    'history' => true,
                ]
            ),
        ];

        return $out;
    }

    /**
     * Tests if expected default values are added.
     *
     * @param array  $config
     * @param array  $expected
     * @param bool   $exception
     * @param string $exceptionMessage
     *
     * @dataProvider getTestConfigurationData
     */
    public function testConfiguration($config, $expected, $exception = false, $exceptionMessage = '')
    {
        if ($exception) {
            $this->setExpectedException(
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                $exceptionMessage
            );
        }

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [$config]);
        $this->assertEquals($expected, $processedConfig);
    }
}
