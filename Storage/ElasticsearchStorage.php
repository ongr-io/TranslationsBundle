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
     * @var Manager Used for storing translations to elasticsearch.
     */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function read($locales = [], $domains = [])
    {
        $repository = $this->getRepository();

        $search = $repository
            ->createSearch()
            ->setScroll('2m')
            ->addQuery(new MatchAllQuery());

        if (!empty($locales)) {
            $search->addFilter(new TermsFilter('locale', $locales));
        }
        if (!empty($domains)) {
            $search->addFilter(new TermsFilter('domain', $domains));
        }

        return $repository->execute($search, Repository::RESULTS_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $translations)
    {
        foreach ($translations as $domain => $domainTrans) {
            /** @var Translation $document */

            foreach ($domainTrans as $key => $keyTrans) {
                $document = $this->getRepository()->createDocument();
                $document->setDomain($domain);
                $document->setKey($key);

                foreach ($keyTrans as $locale => $trans) {
                    $message = new Message();
                    $message->setLocale($locale);
                    $message->setMessage($trans);

                    $document->addMessage($message);
                }
                $this->manager->persist($document);
            }
        }

        $this->manager->commit();
    }

    /**
     * Returns repository for translations.
     *
     * @return Repository
     */
    private function getRepository()
    {
        return $this->manager->getRepository('ONGRTranslationsBundle:Translation');
    }
}
