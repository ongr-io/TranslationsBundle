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

use ONGR\ElasticsearchBundle\DSL\Filter\TermsFilter;
use ONGR\ElasticsearchBundle\DSL\Query\MatchAllQuery;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\ORM\Repository;
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
            $search->addFilter(new TermsFilter('locale', $locales));
        }

        if (!empty($domains)) {
            $search->addFilter(new TermsFilter('domain', $domains));
        }

        return $this->getRepository()->execute($search, Repository::RESULTS_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $translations)
    {
        foreach ($translations as $domain => $domainTrans) {
            /** @var Translation $document */

            foreach ($domainTrans['translations'] as $key => $keyTrans) {
                $document = $this->getRepository()->createDocument();
                $document->setDomain($domain);
                $document->setKey($key);
                $document->setPath($domainTrans['path']);
                $document->setFormat($domainTrans['format']);

                foreach ($keyTrans as $locale => $trans) {
                    $message = new Message();
                    $message->setLocale($locale);
                    $message->setMessage($trans);
                    $document->addMessage($message);
                }
                $this->getRepository()->getManager()->persist($document);
            }
        }

        $this->getRepository()->getManager()->commit();
    }

    /**
     * Returns repository for translations.
     *
     * @return Repository
     */
    private function getRepository()
    {
        return $this->repository;
    }
}
