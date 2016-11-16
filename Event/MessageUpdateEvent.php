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
     * @var array
     */
    private $locale;

    /**
     * @param Translation  $document
     * @param array   $locale
     */
    public function __construct(Translation $document, $locale)
    {
        $this->document = $document;
        $this->locale = $locale;
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
     * @return array
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
