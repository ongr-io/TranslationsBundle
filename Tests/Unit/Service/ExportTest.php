<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\Service;

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Service\Export;
use ONGR\TranslationsBundle\Storage\StorageInterface;
use ONGR\TranslationsBundle\Translation\Export\YmlExport;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Export service unit tests.
 */
class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStream|vfsStreamDirectory
     */
    private $root;

    /**
     * Set virtual file system.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * Test if global translations directory is created.
     */
    public function testTargetDirectoryIsCreated()
    {
        $expectedDir = 'Resources/translations';
        /* @var $exportService Export */
        $exportService = $this
            ->getMockBuilder('ONGR\TranslationsBundle\Service\Export')
            ->setConstructorArgs(
                [
                    $this->getLoadersContainerMock(),
                    $this->getStorageMock(['read']),
                    $this->getExporterMock(),
                    vfsStream::url('root'),
                ]
            )
            ->setMethods(null)
            ->getMock();

        $this->assertFalse($this->root->hasChild($expectedDir), 'Resources/translations directory should not exists.');
        $exportService->export();
        $this->assertTrue($this->root->hasChild($expectedDir), 'Resources/translations directory should exists.');
    }

    /**
     * Tests if correct data array is formed.
     */
    public function testGetExportData()
    {
        $translation = new Translation();
        $translation->setDomain('foo_domain');
        $translation->setKey('foo_key');
        $message = new Message();
        $message->setLocale('foo_locale');
        $message->setMessage('foo_message');
        $translation->addMessage($message);

        $storageMock = $this->getStorageMock(['read', 'getRepository']);
        $storageMock
            ->expects($this->once())
            ->method('read')
            ->willReturn([$translation]);

        $exporter = $this->getExporterMock(['export']);
        $exporter
            ->expects($this->once())
            ->method('export')
            ->with(
                'vfs://root/Resources/translations/foo_domain.foo_locale.yml',
                [
                    'foo_key' => 'foo_message',
                ]
            );

        /* @var Export|\PHPUnit_Framework_MockObject_MockObject $exportService */
        $exportService = $this
            ->getMockBuilder('ONGR\TranslationsBundle\Service\Export')
            ->setConstructorArgs([$this->getLoadersContainerMock(), $storageMock, $exporter, vfsStream::url('root')])
            ->setMethods(null)
            ->getMock();

        $exportService->export();
    }

    /**
     * Returns storage mock.
     *
     * @param mixed $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    private function getStorageMock($methods = null)
    {
        return $this
            ->getMockBuilder('ONGR\TranslationsBundle\Storage\ElasticsearchStorage')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Returns exporter mock.
     *
     * @param mixed $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|YmlExport
     */
    private function getExporterMock($methods = null)
    {
        return $this
            ->getMockBuilder('ONGR\TranslationsBundle\Translation\Export\YmlExport')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Returns LoadersContainer mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoadersContainerMock()
    {
        return $this
            ->getMockBuilder('ONGR\TranslationsBundle\Service\LoadersContainer')
            ->getMock();
    }
}
