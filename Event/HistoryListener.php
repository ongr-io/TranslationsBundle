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

use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\TranslationsBundle\Document\History;
use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use ONGR\TranslationsBundle\Translation\HistoryManager;

/**
 * Listens for edit message request event and add old message to history.
 */
class HistoryListener
{
    /**
     * @var HistoryManager
     */
    private $manager;

    /**
     * @param HistoryManager $manager
     */
    public function __construct(HistoryManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Edit message event.
     *
     * @param TranslationEditMessageEvent $event
     */
    public function addToHistory(TranslationEditMessageEvent $event)
    {
        $document = $event->getDocument();
        $locale = $event->getLocale();

        if ($oldMessage = $this->getOldMessage($document, $locale)) {
            $this->manager->addHistory($oldMessage, $document->getId(), $locale);
        }
    }

    /**
     * @param Translation $document
     * @param string      $locale
     *
     * @return Message|null
     */
    private function getOldMessage($document, $locale)
    {
        $messages = $document->getMessages();

        foreach ($messages as $message) {
            if ($locale == $message->getLocale()) {
                return $message;
            }
        }

        return null;
    }
}
