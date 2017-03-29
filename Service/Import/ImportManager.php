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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Collects translations.
 */
class ImportManager
{
    /**
     * @var array
     */
    private $locales;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var YamlFileLoader
     */
    private $parser;

    /**
     * @var string
     */
    private $kernelRoot;

    /**
     * @param Repository $repository
     */
    public function __construct(
        Repository $repository,
        $kernelRoot
    )
    {
        $this->parser = new YamlParser();
        $this->repository = $repository;
        $this->manager = $repository->getManager();
        $this->kernelRoot = $kernelRoot;
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
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Write translations to storage.
     *
     * @param $translations
     */
    public function writeToStorage($domain, $translations)
    {
        foreach ($translations as $keys) {
            foreach ($keys as $key => $transMeta) {

                $search = $this->repository->createSearch();
                $search->addQuery(new MatchQuery('key', $key));
                $results = $this->repository->findDocuments($search);
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
                $this->manager->persist($document);
            }
        }

        $this->manager->commit();
    }

    /**
     * Imports translation files from a directory.
     * @param string $dir
     */
    public function importTranslationFiles($domain, $dir)
    {
        $translations = $this->getTranslationsFromFiles($domain, $dir);
        $this->writeToStorage($domain, $translations);
    }

    /**
     * Return a Finder object if $path has a Resources/translations folder.
     *
     * @param string $domain
     * @param string $path
     * @param array $directories
     *
     * @return array
     */
    public function getTranslationsFromFiles($domain, $path, array $directories = [])
    {
        if (!$directories) {
            $directories = [
                $path . 'Resources' . DIRECTORY_SEPARATOR . 'translations',
                $this->kernelRoot . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . 'translations',
            ];
        }

        $finder = new Finder();
        $translations = [];

        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $finder->files()
                    ->name($this->getFileNamePattern())
                    ->in($directory);

                foreach ($finder as $file) {
                    $translations = array_replace_recursive($this->getFileTranslationMessages($file, $domain), $translations);
                }
            }
        }

        return $translations;
    }

    /**
     * @param SplFileInfo $file
     * @param string      $domain
     *
     * @return array
     */
    public function getFileTranslationMessages(SplFileInfo $file, $domain)
    {
        $locale = explode('.', $file->getFilename())[1];

        if (!in_array($locale, $this->getLocales())) {
            return [];
        }

        $translations = [];

        try {
            $domainMessages = $this->parser->parse(file_get_contents($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename()));
        } catch (ParseException $e) {
            throw new InvalidResourceException(sprintf('Error parsing YAML, invalid file "%s"', $file->getPath()), 0, $e);
        }

        $path = substr(pathinfo($file->getPathname(), PATHINFO_DIRNAME), strlen(getcwd()) + 1);
        foreach ($domainMessages as $key => $content) {
            $translations[$domain][$key]['messages'][$locale] = $content;
            $translations[$domain][$key]['path'] = $path;
            $translations[$domain][$key]['format'] = $file->getExtension();
        }

        return $translations;
    }

    /**
     * @return string
     */
    private function getFileNamePattern()
    {
        $regex = sprintf(
            '/(.*\.(%s)\.(%s))/',
            implode('|', $this->getLocales()),
            'yml'
        );
        return $regex;
    }
}
