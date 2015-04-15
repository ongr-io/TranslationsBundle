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

use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Storage\StorageInterface;
use ONGR\TranslationsBundle\Translation\Export\ExporterInterface;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var ExporterInterface
     */
    private $exporter;

    /**
     * @var LoadersContainer
     */
    private $loadersContainer;

    /**
     * @var array
     */
    private $managedLocales = [];

    /**
     * @var Translation[]
     */
    private $refresh = [];

    /**
     * @param LoadersContainer  $loadersContainer
     * @param StorageInterface  $storage
     * @param ExporterInterface $exporter
     */
    public function __construct(
        LoadersContainer $loadersContainer,
        StorageInterface $storage,
        ExporterInterface $exporter
    ) {
        $this->storage = $storage;
        $this->exporter = $exporter;
        $this->loadersContainer = $loadersContainer;
    }

    /**
     * Exports translations from ES to files.
     *
     * @param array $domains To export.
     */
    public function export($domains = [])
    {
        foreach ($this->readStorage($domains) as $file => $translations) {
            if (!file_exists($file)) {
                $this->getFilesystem()->touch($file);
            }
            list($domain, $locale, $extension) = explode('.', $file);
            if ($this->loadersContainer && $this->loadersContainer->has($extension)) {
                $messageCatalogue = $this->loadersContainer->get($extension)->load($file, $locale, $domain);
                $translations = array_merge($messageCatalogue->all($domain), $translations);
            }

            $this->exporter->export($file, $translations);
        }

        if (!empty($this->refresh)) {
            $this->storage->write($this->refresh);
            $this->refresh = [];
        }
    }

    /**
     * Sets managed locales.
     *
     * @param array $managedLocales
     */
    public function setManagedLocales($managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getManagedLocales()
    {
        return $this->managedLocales;
    }

    /**
     * Get translations for export.
     *
     * @param array $domains To read from storage.
     *
     * @return array
     */
    private function readStorage($domains)
    {
        $data = [];
        $translations = $this->storage->read($this->getManagedLocales(), $domains);

        /* @var Translation $translation */
        foreach ($translations as $translation) {
            $messages = $translation->getMessages();
            $wasDirty = false;

            foreach ($messages as $key => $message) {
                if ($message->getStatus() === Message::DIRTY) {
                    $path = sprintf(
                        '%s' . DIRECTORY_SEPARATOR . '%s.%s.%s',
                        $translation->getPath(),
                        $translation->getDomain(),
                        $message->getLocale(),
                        $translation->getFormat()
                    );
                    $data[$path][$translation->getKey()] = $message->getMessage();

                    $message->setStatus(Message::FRESH);
                    $messages[$key] = $message;
                    $wasDirty = true;
                }
            }

            if ($wasDirty) {
                $translation->setMessages($messages);
                $this->refresh[] = $translation;
            }
        }

        return $data;
    }

    /**
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return new Filesystem();
    }
}
