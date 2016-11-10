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

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use ONGR\ElasticsearchBundle\Service\Manager;
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
     * @param FileImport $fileImport
     * @param Manager    $esManager
     */
    public function __construct(
        FileImport $fileImport,
        Manager $esManager
    ) {
        $this->fileImport = $fileImport;
        $this->esManager = $esManager;
    }

    /**
     * Write translations to storage.
     */
    public function writeToStorage()
    {
        foreach ($this->translations as $path => $domains) {
            foreach ($domains as $domain => $transMeta) {
                foreach ($transMeta['translations'] as $key => $keyTrans) {
                    $document = new Translation();
                    $document->setDomain($domain);
                    $document->setKey($key);
                    $document->setPath($path);
                    $document->setFormat($transMeta['format']);
                    foreach ($keyTrans as $locale => $trans) {
                        $message = new Message();
                        $message->setLocale($locale);
                        $message->setMessage($trans);
                        $document->addMessage($message);
                    }
                    $this->esManager->persist($document);
                }
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

            $this->importBundleTranslationFiles($dir);
        }
    }

    /**
     * Imports translation files form the specific bundles.
     *
     * @param string $bundle
     */
    public function importBundleTranslationFiles($bundle)
    {
        $finder = $this->findTranslationsFiles($bundle);
        $this->importTranslationFiles($finder);
    }

    /**
     * Imports Symfony's components translation files.
     */
    public function importComponentTranslationFiles()
    {
        $classes = [
            'Symfony\Component\Validator\ValidatorBuilder' => '/Resources/translations',
            'Symfony\Component\Form\Form' => '/Resources/translations',
            'Symfony\Component\Security\Core\Exception\AuthenticationException' => '/../../Resources/translations',
        ];

        $dirs = [];
        foreach ($classes as $namespace => $translationDir) {
            $reflection = new \ReflectionClass($namespace);
            $dirs[] = dirname($reflection->getFileName()) . $translationDir;
        }

        $finder = new Finder();
        $finder->files()
            ->name($this->getFileNamePattern())
            ->in($dirs);

        $this->importTranslationFiles($finder->count() > 0 ? $finder : null);
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
}
