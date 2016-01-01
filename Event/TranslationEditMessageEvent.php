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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event for edit message action.
 */
class TranslationEditMessageEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var object
     */
    private $document;

    /**
     * @param Request $request
     * @param object  $document
     */
    public function __construct(Request $request, $document)
    {
        $this->request = $request;
        $this->document = $document;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns document associated with the event.
     *
     * @return object
     */
    public function getDocument()
    {
        return $this->document;
    }
}
