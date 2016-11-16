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

use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event for edit message action.
 */
class MessageUpdateEvent extends Event
{
    /**
     * @var Translation
     */
    private $document;

    /**
     * @var Message
     */
    private $message;

    /**
     * @param Translation $document
     * @param Message     $message
     */
    public function __construct(Translation $document, Message $message)
    {
        $this->document = $document;
        $this->message = $message;
    }
    /**
     * Returns document associated with the event.
     *
     * @return Translation
     */
    public function getDocument()
    {
        return $this->document;
    }


    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
