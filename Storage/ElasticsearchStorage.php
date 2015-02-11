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
    public function read()
    {
        $repository = $this->getRepository();

        $search = $repository
            ->createSearch()
            ->addQuery(new MatchAllQuery());

        return $repository->execute($search, Repository::RESULTS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $translations)
    {
        foreach ($translations as $domain => $domainTrans) {
            /** @var Translation $document */
            $document = $this->getRepository()->createDocument();
            $document->setDomain($domain);
            $messages = [];

            foreach ($domainTrans as $locale => $locTrans) {
                foreach ($locTrans as $id => $trans) {
                    $message = new Message();
                    $message->setId($id);
                    $message->setLocale($locale);
                    $message->setMessage($trans);

                    $messages[] = $message;
                }
            }

            $document->setMessages($messages);
            $this->manager->persist($document);
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
