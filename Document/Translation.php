<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\AbstractDocument;

/**
 * Holds translations for certain domain.
 *
 * @ES\Document(type="translation")
 */
class Translation extends AbstractDocument
{
    /**
     * @var string
     *
     * @ES\Property(name="domain", type="string", index="not_analyzed")
     */
    private $domain;

    /**
     * @var string
     * 
     * @ES\Property(name="group", type="string")
     */
    private $group;

    /**
     * @var Message
     *
     * @ES\Property(name="messages", type="object", multiple=true, objectName="ONGRTranslationsBundle:Message")
     */
    private $messages;

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }
}
