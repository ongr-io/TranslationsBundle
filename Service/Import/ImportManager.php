<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service\Import;

use ONGR\ElasticsearchBundle\Service\Manager;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use Symfony\Component\Finder\Finder;

/**
 * Collects translations.
 */
class ImportManager
{
    /**
     * @var FileImport
     */
    private $fileImport;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var array
     */
    private $domains;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var Manager
     */
    private $esManager;

    /**
     * @var Repository
     */
    private $translationsRepo;

    /**
     * @param FileImport  $fileImport
     * @param Repository  $repository
     */
    public function __construct(
        FileImport $fileImport,
        $repository
    ) {
        $this->fileImport = $fileImport;
        $this->translationsRepo = $repository;
        $this->esManager = $repository->getManager();
    }

    /**
     * @return mixed
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param mixed $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    /**
     * @return mixed
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param mixed $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @return mixed
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param mixed $formats
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;
    }

    /**
     * Write translations to storage.
     */
    public function writeToStorage()
    {
        foreach ($this->translations as $domain => $keys) {
            foreach ($keys as $key => $transMeta) {
                $search = $this->translationsRepo->createSearch();
                $search->addQuery(new MatchQuery('key', $key));
                $results = $this->translationsRepo->findDocuments($search);
                if (count($results)) {
                    continue;
                }

                $document = new Translation();
                $document->setDomain($domain);
                $document->setKey($key);
                $document->setPath($transMeta['path']);
                $document->setFormat($transMeta['format']);
                foreach ($transMeta['messages'] as $locale => $text) {
                    $message = new Message();
                    $message->setLocale($locale);
                    $message->setMessage($text);
                    $document->addMessage($message);
                }
                $this->esManager->persist($document);
            }
        }

        $this->esManager->commit();
    }

    /**
     * Imports translation files from a directory.
     * @param string $dir
     */
    public function importDirTranslationFiles($dir)
    {
        $finder = $this->findTranslationsFiles($dir);
        $this->importTranslationFiles($finder);
    }

    /**
     * Imports translation files form all bundles.
     *
     * @param array $bundles
     * @param bool  $isBundle
     */
    public function importBundlesTranslationFiles($bundles, $isBundle = false)
    {
        foreach ($bundles as $bundle) {
            $dir = $isBundle?
                dir($bundle->getPath())->path :
                dirname((new \ReflectionClass($bundle))->getFileName());

            $this->importDirTranslationFiles($dir);
        }
    }

    /**
     * Return a Finder object if $path has a Resources/translations folder.
     *
     * @param string $path
     *
     * @return Finder
     */
    protected function findTranslationsFiles($path)
    {
        $finder = null;

        if (preg_match('#^win#i', PHP_OS)) {
            $path = preg_replace('#' . preg_quote(DIRECTORY_SEPARATOR, '#') . '#', '/', $path);
        } else {
            $path = str_replace('\\', '/', $path);
        }

        $dir = $path . '/Resources/translations';

        if (is_dir($dir)) {
            $finder = new Finder();
            $finder->files()
                ->name($this->getFileNamePattern())
                ->in($dir);
        }

        return (null !== $finder && $finder->count() > 0) ? $finder : null;
    }

    /**
     * @return string
     */
    protected function getFileNamePattern()
    {
        if (count($this->getDomains())) {
            $regex = sprintf(
                '/((%s)\.(%s)\.(%s))/',
                implode('|', $this->getDomains()),
                implode('|', $this->getLocales()),
                implode('|', $this->getFormats())
            );
        } else {
            $regex = sprintf(
                '/(.*\.(%s)\.(%s))/',
                implode('|', $this->getLocales()),
                implode('|', $this->getFormats())
            );
        }

        return $regex;
    }

    /**
     * Imports some translations files.
     *
     * @param Finder $finder
     */
    protected function importTranslationFiles($finder)
    {
        if ($finder instanceof Finder) {
            foreach ($finder as $file) {
                $this->translations = array_replace_recursive($this->fileImport->import($file), $this->translations);
            }
        }
    }
}
