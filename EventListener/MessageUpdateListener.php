<?php

namespace ONGR\TranslationsBundle\EventListener;

use ONGR\TranslationsBundle\Event\MessageUpdateEvent;
use ONGR\TranslationsBundle\Service\HistoryManager;

class MessageUpdateListener
{
    /**
     * @var HistoryManager
     */
    private $historyManager;

    /**
     * @param HistoryManager $historyManager
     */
    public function __construct(HistoryManager $historyManager)
    {
        $this->historyManager = $historyManager;
    }

    public function onMessageUpdate(MessageUpdateEvent $e)
    {
        $this->historyManager->add($e->getMessage(), $e->getDocument());
    }
}
