<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ImportTest.
 */
class ImportTest extends WebTestCase
{
    /**
     * Expected translations data provider.
     *
     * @return array
     */
    public function getExpectedTranslationsData()
    {
        $out = [
            [
                [
                    'messages' => [
                        'home' => [
                            'en' => 'Home',
                            'lt' => 'Namai',
                        ],
                        'back_to_list' => [
                            'en' => 'Back',
                            'lt' => 'Atgal į sąrašą',
                        ],
                    ],
                ],
            ],
        ];

        return $out;
    }

    /**
     * Tests if translations files are parsed correctly.
     *
     * @param array $expectedTranslations
     *
     * @dataProvider getExpectedTranslationsData
     */
    public function testImport($expectedTranslations)
    {
        $translations = $this->getContainer()->get('ongr_translations.import')->getTranslations();

        $this->assertArraySubset(
            $expectedTranslations['messages'],
            reset($translations)['messages']['translations']
        );
    }

    /**
     * Returns container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return static::createClient()->getContainer();
    }
}
