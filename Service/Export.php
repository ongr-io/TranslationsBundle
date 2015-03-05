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
     * @var LoadersContainer
     */
    private $loadersContainer;

    /**
     * Dependency Injection.
     *
     * @param LoadersContainer $loadersContainer
     * @param StorageInterface $storage
     * @param ExportInterface  $exporter
     * @param string           $kernelRootDir
     */
    public function __construct(
        LoadersContainer $loadersContainer,
        StorageInterface $storage,
        ExportInterface $exporter,
        $kernelRootDir
    ) {
        $this->storage = $storage;
        $this->exporter = $exporter;
        $this->loadersContainer = $loadersContainer;
    }

    /**
     * Exports translations from ES to files.
     *
     * @param array $locales
     * @param array $domains
     */
    public function export($locales = [], $domains = [])
    {
        foreach ($this->getExportData($locales, $domains) as $file => $translations) {
            if (file_exists($file)) {
                list($domain, $locale, $extension) = explode('.', $file);
                if ($this->loadersContainer && $this->loadersContainer->has($extension)) {
                    $messageCatalogue = $this->loadersContainer->get($extension)->load($file, $locale, $domain);
                    $translations = array_merge($messageCatalogue->all($domain), $translations);
                }
            }

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
    private function getExportData($locales, $domains)
    {
        $data = [];
        $translations = $this->storage->read($locales, $domains);
        if (!empty($translations)) {
            foreach ($translations as $translation) {
                /** @var Translation $translation */
                foreach ($translation->getMessages() as $message) {
                    $path = sprintf(
                        '%s' . DIRECTORY_SEPARATOR . '%s.%s.%s',
                        $translation->getPath(),
                        $translation->getDomain(),
                        $message->getLocale(),
                        $translation->getFormat()
                    );
                    $data[$path][$translation->getKey()] = $message->getMessage();
                }
            }
        }

        return $data;
    }
}
