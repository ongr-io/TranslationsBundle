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

use ONGR\TranslationsBundle\Storage\StorageInterface;
use ONGR\TranslationsBundle\Translation\Import\FileImport;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Collects translations.
 */
class Import
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
     * @var string
     */
    private $kernelDir;

    /**
     * @var array
     */
    private $bundles;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param FileImport       $fileImport
     * @param StorageInterface $storage
     * @param string           $kernelDir
     * @param array            $kernelBundles
     */
    public function __construct(FileImport $fileImport, StorageInterface $storage, $kernelDir, $kernelBundles)
    {
        $this->fileImport = $fileImport;
        $this->storage = $storage;
        $this->kernelDir = $kernelDir;
        $this->bundles = $kernelBundles;
    }

    /**
     * Collects translations.
     */
    public function import()
    {
        $this->importAppTranslationFiles();

        $this->importBundlesTranslationFiles();

        $this->importComponentTranslationFiles();
    }

    /**
     * Returns translations as array.
     *
     * @return array
     */
    public function getTranslations()
    {
        if (empty($this->translations)) {
            $this->import();

            return $this->translations;
        } else {
            return $this->translations;
        }
    }

    /**
     * Write translations to storage.
     */
    public function writeToStorage()
    {
        $this->storage->write($this->getTranslations());
    }

    /**
     * Imports application translation files.
     */
    public function importAppTranslationFiles()
    {
        $finder = $this->findTranslationsFiles(
            $this->kernelDir,
            $this->getLocales(),
            $this->getDomains()
        );
        $this->importTranslationFiles($finder);
    }

    /**
     * Imports translation files form all bundles.
     */
    public function importBundlesTranslationFiles()
    {
        foreach ($this->bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $dir = dirname($reflection->getFilename());

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
            'Symfony\Component\Validator\Validator' => '/Resources/translations',
            'Symfony\Component\Form\Form' => '/Resources/translations',
            'Symfony\Component\Security\Core\Exception\AuthenticationException' => '/../../Resources/translations',
        ];

        $dirs = [];
        foreach ($classes as $namespace => $translationDir) {
            $reflection = new \ReflectionClass($namespace);
            $dirs[] = dirname($reflection->getFilename()) . $translationDir;
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
                $this->translations = array_merge($this->translations, $this->fileImport->import($file));
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

    /**
     * @return mixed
     */
    public function getKernelDir()
    {
        return $this->kernelDir;
    }

    /**
     * @param mixed $kernelDir
     */
    public function setKernelDir($kernelDir)
    {
        $this->kernelDir = $kernelDir;
    }

    /**
     * @return mixed
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @param mixed $bundles
     */
    public function setBundles($bundles)
    {
        $this->bundles = $bundles;
    }
}
