<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service\Export;

use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Service\LoadersContainer;
use ONGR\TranslationsBundle\Service\TranslationManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Export.
 */
class ExportManager
{
    /**
     * @var TranslationManager
     */
    private $translationManager;

    /**
     * @var YmlExport
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
     * @param LoadersContainer   $loadersContainer
     * @param TranslationManager $translationManager
     * @param YmlExport          $exporter
     */
    public function __construct(
        LoadersContainer $loadersContainer,
        TranslationManager $translationManager,
        YmlExport $exporter
    ) {
        $this->loadersContainer = $loadersContainer;
        $this->translationManager = $translationManager;
        $this->exporter = $exporter;
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
     * Exports translations from ES to files.
     *
     * @param array $domains To export.
     * @param bool  $force
     */
    public function export($domains = [], $force = null)
    {
        foreach ($this->formExportList($domains, $force) as $file => $translations) {
            if (!file_exists($file)) {
                (new Filesystem())->touch($file);
            }
            list($domain, $locale, $extension) = explode('.', $file);
            if ($this->loadersContainer && $this->loadersContainer->has($extension)) {
                $messageCatalogue = $this->loadersContainer->get($extension)->load($file, $locale, $domain);
                $translations = array_merge($messageCatalogue->all($domain), $translations);
            }

            $this->exporter->export($file, $translations);
        }

        $this->translationManager->saveTranslations($this->refresh);
        $this->refresh = [];
    }

    /**
     * Get translations for export.
     *
     * @param array $domains To read from storage.
     * @param bool  $force   Determines if the message status is relevant.
     *
     * @return array
     */
    private function formExportList($domains, $force)
    {
        $data = [];
        $filters = array_filter([
            'messages.locale' => $this->getManagedLocales(),
            'domain' => $domains
        ]);

        $translations = $this->translationManager->getTranslations($filters);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $messages = $translation->getMessages();

            foreach ($messages as $key => $message) {
                if ($message->getStatus() === Message::DIRTY || $force) {
                    $path = sprintf(
                        '%s' . DIRECTORY_SEPARATOR . '%s.%s.%s',
                        $translation->getPath(),
                        $translation->getDomain(),
                        $message->getLocale(),
                        $translation->getFormat()
                    );
                    $data[$path][$translation->getKey()] = $message->getMessage();

                    $message->setStatus(Message::FRESH);
                    $this->refresh[] = $translation;
                }
            }
        }

        return $data;
    }
}
