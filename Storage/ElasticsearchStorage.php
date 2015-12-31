<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Storage;

use ONGR\ElasticsearchBundle\Result\Result;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;

/**
 * Elasticsearch storage for translations.
 */
class ElasticsearchStorage implements StorageInterface
{
    /**
     * @var Repository Elasticsearch repository used for storing translations.
     */
    private $repository;

    /**
     * Injects elasticsearch repository for storage actions.
     *
     * @param Repository $repository Elasticsearch repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function read($locales = [], $domains = [])
    {
        $search = $this
            ->getRepository()
            ->createSearch()
            ->setScroll('2m')
            ->addQuery(new MatchAllQuery());

        if (!empty($locales)) {
            $search->addFilter(new TermsQuery('locale', $locales));
        }

        if (!empty($domains)) {
            $search->addFilter(new TermsQuery('domain', $domains));
        }

        return $this->getRepository()->execute($search, Result::RESULTS_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $translations)
    {
        if (!(reset($translations) instanceof Translation)) {
            $translations = $this->toDocumentArray($translations);
        }

        foreach ($translations as $translation) {
            $this->getRepository()->getManager()->persist($translation);
        }

        $this->getRepository()->getManager()->commit();
    }

    /**
     * Converts arrays to documents.
     *
     * @param array $translations
     *
     * @return array
     */
    private function toDocumentArray(array $translations)
    {
        $out = [];

        foreach ($translations as $path => $domains) {
            foreach ($domains as $domain => $transMeta) {
                foreach ($transMeta['translations'] as $key => $keyTrans) {

                    /** @var Translation $document */
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
                    $out[] = $document;
                }
            }
        }

        return $out;
    }

    /**
     * {@inheritdoc}
     */
    private function getRepository()
    {
        return $this->repository;
    }
}
