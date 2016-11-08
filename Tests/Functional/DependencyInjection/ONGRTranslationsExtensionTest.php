<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Functional\DependencyInjection;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

class ONGRTranslationsExtensionTest extends AbstractElasticsearchTestCase
{
    /**
     * Data provider for testing container services.
     *
     * @return array
     */
    public function getTestServicesData()
    {
        return [
            [
                'ongr_translations.import',
                'ONGR\TranslationsBundle\Service\Import\Import',
            ],
            [
                'ongr_translations.file_import',
                'ONGR\TranslationsBundle\Service\Import\FileImport',
            ],
            [
                'ongr_translations.filter_manager',
                'ONGR\FilterManagerBundle\Search\FilterManager',
            ],
        ];
    }

    /**
     * Tests if container has services.
     *
     * @param string $id       Service Id.
     * @param string $instance Instance associated with service.
     *
     * @dataProvider getTestServicesData
     */
    public function testServices($id, $instance)
    {
        $container = $this->getContainer();

        $this->assertTrue($container->has($id));
        $this->assertInstanceOf($instance, $container->get($id));
    }
}
