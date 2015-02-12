<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service;

use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Storage\StorageInterface;
use ONGR\TranslationsBundle\Translation\Export\ExportInterface;

/**
 * Class Export.
 */
class Export
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $exporter;

    /**
     * @var string
     */
    private $destinationDir;

    /**
     * Dependency Injection.
     *
     * @param StorageInterface $storage
     * @param ExportInterface  $exporter
     * @param string           $kernelRootDir
     */
    public function __construct(StorageInterface $storage, ExportInterface $exporter, $kernelRootDir)
    {
        $this->storage = $storage;
        $this->exporter = $exporter;
        $this->destinationDir = $kernelRootDir . '/Resources/translations';
    }

    /**
     * Exports translations from ES to files.
     *
     * @param array $locales
     * @param array $domains
     */
    public function export($locales, $domains)
    {
        if (!file_exists($this->destinationDir)) {
            mkdir($this->destinationDir, 0755);
        }

        foreach ($this->getExportData($locales, $domains) as $file => $translations) {
            $this->exporter->export($file, $translations);
        }
    }

    /**
     * Get translations for export.
     *
     * @param array $locales
     * @param array $domains
     *
     * @return array
     */
    public function getExportData($locales, $domains)
    {
        $data = [];
        foreach ($this->storage->read($locales, $domains) as $translation) {
            /** @var Translation $translation */
            $fileName = $translation->getDomain() . '.' .  $translation->getLocale() . '.yml';
            $path = $this->destinationDir . DIRECTORY_SEPARATOR .  $fileName;

            $data[$path][] = [
                $translation->getKey() => $translation->getMessage(),
            ];
        }

        return $data;
    }
}
