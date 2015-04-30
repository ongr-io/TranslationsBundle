<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Event;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Repository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Listens for edit message request event and add old message to history.
 */
class HistoryListener
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Edit message event.
     *
     * @param TranslationEditMessageEvent $event
     */
    public function addToHistory(TranslationEditMessageEvent $event)
    {
        $manager = $this->repository->getManager();
        $document = $event->getDocument();
        $locale = $this->getLocale($event);
        $oldMessage = $this->getOldMessage($document, $locale);

        $repository = $manager->getRepository('ONGRTranslationsBundle:History');
        $historyDocument = $this->setDocument($document, $repository, $oldMessage, $locale);

        $manager->persist($historyDocument);
        $manager->commit();
    }

    /**
     * @param TranslationEditMessageEvent $event
     *
     * @return string
     */
    private function getLocale($event)
    {
        $request = $event->getRequest();
        $content = json_decode($request->getContent());

        return $content->properties->locale;
    }

    /**
     * @param DocumentInterface $document
     * @param Repository        $repository
     * @param string            $oldMessage
     * @param string            $locale
     *
     * @return mixed
     */
    private function setDocument($document, $repository, $oldMessage, $locale)
    {
        $newDocument = $repository->createDocument();
        $key = $document->getKey();
        $domain = $document->getDomain();
        $newDocument->setKey($key);
        $newDocument->setLocale($locale);
        $newDocument->setMessage($oldMessage);
        $newDocument->setDomain($domain);
        $newDocument->setId(sha1($document->getId() . $oldMessage));
        $newDocument->setCreatedAt(new \DateTime());

        return $newDocument;
    }

    /**
     * @param DocumentInterface $document
     * @param string            $locale
     *
     * @return mixed
     */
    private function getOldMessage($document, $locale)
    {
        $messages = $document->getMessages();
        foreach ($messages as $message) {
            $messageLocale = $message->getLocale();
            if ($locale == $messageLocale) {
                $oldMessage = $message->getMessage();

                return $oldMessage;
            }
        }
    }
}
