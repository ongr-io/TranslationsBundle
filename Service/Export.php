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

use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Translation\Export\ExporterInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Export.
 */
class Export
{
    /**
     * @var Repository
     */
    private $repository;

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
     * @param Repository        $repository
     * @param ExporterInterface $exporter
     */
    public function __construct(
        LoadersContainer $loadersContainer,
        Repository $repository,
        ExporterInterface $exporter
    ) {
        $this->repository = $repository;
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
            foreach ($this->refresh as $translation) {
                $this->repository->getManager()->persist($translation);
            }

            $this->repository->getManager()->commit();

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
        $translations = $this->read($this->getManagedLocales(), $domains);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $messages = $translation->getMessages();

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
                    $this->refresh[] = $translation;
                }
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

    /**
     * {@inheritdoc}
     */
    public function read($locales = [], $domains = [])
    {
        $search = $this->repository
            ->createSearch()
            ->setScroll('2m')
            ->addQuery(new MatchAllQuery());
        if (!empty($locales)) {
            $search->addFilter(new TermsQuery('messages.locale', $locales));
        }
        if (!empty($domains)) {
            $search->addFilter(new TermsQuery('domain', $domains));
        }
        return $this->repository->findDocuments($search);
    }
}
