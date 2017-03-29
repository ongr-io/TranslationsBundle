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
use ONGR\TranslationsBundle\Service\TranslationManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Yaml\Parser as YamlParser;
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
     * @var array
     */
    private $locales;

    /**
     * @var Translation[]
     */
    private $refresh = [];

    /**
     * @var YamlParser
     */
    private $parser;

    /**
     * @param ParameterBag   $loadersContainer
     * @param TranslationManager $translationManager
     * @param YmlExport          $exporter
     */
    public function __construct(
        TranslationManager $translationManager,
        YmlExport $exporter
    ) {
        $this->parser = new YamlParser();
        $this->translationManager = $translationManager;
        $this->exporter = $exporter;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
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

            $currentTranslations = $this->parser->parse(file_get_contents($file)) ?? [];

            $translations = array_merge_recursive($currentTranslations, $translations);

            $this->exporter->export($file, $translations);
        }

        $this->translationManager->save($this->refresh);
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
        $output = [];
        $filters = array_filter([
            'messages.locale' => $this->getLocales(),
            'domain' => $domains
        ]);

        $translations = $this->translationManager->getAll($filters);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $messages = $translation->getMessages();

            foreach ($messages as $key => $message) {
                if ($message->getStatus() === Message::DIRTY || $force) {
                    $path = sprintf(
                        '%s' . DIRECTORY_SEPARATOR . '%s.%s.%s',
                        $translation->getPath(),
                        'messages',
                        $message->getLocale(),
                        $translation->getFormat()
                    );
                    $output[$path][$translation->getKey()] = $message->getMessage();

                    $message->setStatus(Message::FRESH);
                    $this->refresh[] = $translation;
                }
            }
        }

        return $output;
    }
}
