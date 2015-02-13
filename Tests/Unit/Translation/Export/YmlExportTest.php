<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\Translation\Export\YmlExport;

use ONGR\TranslationsBundle\Translation\Export\YmlExport;
use org\bovigo\vfs\vfsStream;

/**
 * YmlExport unit tests.
 */
class YmlExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set upf vfsStream.
     */
    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup('/root/Resources/translations');
    }

    /**
     * Translations data provider.
     *
     * @return array
     */
    public function getTranslationsData()
    {
        $out = [];

        $out[] = [
            'vfs://root/Resources/translations/foo_domain.foo_locale.yml',
            [
                [
                    'foo_key' => 'foo_message',
                    'bar_key' => 'bar_message',
                ],
            ],
        ];

        return $out;
    }

    /**
     * Tests if Yml file is formed correctly.
     *
     * @param string $file
     * @param array  $translations
     *
     * @dataProvider getTranslationsData
     */
    public function testYmlExport($file, $translations)
    {
        $dumper = new YmlExport();
        $dumper->export($file, $translations);


        $this->assertTrue($this->root->hasChild('root/Resources/translations/foo_domain.foo_locale.yml'));
        $dumpedData = explode(
            "\n",
            file_get_contents(vfsStream::url('root/Resources/translations/foo_domain.foo_locale.yml'))
        );
        $this->assertEquals('foo_key: foo_message', $dumpedData[0]);
        $this->assertEquals('bar_key: bar_message', $dumpedData[1]);
    }
}
