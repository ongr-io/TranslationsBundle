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
use ONGR\ElasticsearchBundle\Collection\Collection;

/**
 * Holds translations for certain domain.
 *
 * @ES\Document(type="translation")
 */
class Translation implements \JsonSerializable
{
    /**
     * @var string
     *
     * @ES\Id()
     */
    private $id;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $domain;

    /**
     * @var array
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $tags = [];

    /**
     * @var Message[]
     *
     * @ES\Embedded(class="ONGRTranslationsBundle:Message", multiple=true)
     */
    private $messages = [];

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $key;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $path;

    /**
     * @var string
     *
     * @ES\Property(type="string")
     */
    private $description;

    /**
     * @var string
     *
     * @ES\Property(type="string")
     */
    private $format;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date")
     */
    private $updatedAt;

    /**
     * Sets timestamps.
     */
    public function __construct()
    {
        $this->messages = new Collection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Sets document unique id.
     *
     * @param string $documentId
     *
     * @return $this
     */
    public function setId($documentId)
    {
        $this->id = $documentId;

        return $this;
    }

    /**
     * Returns document id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * Sets tags.
     *
     * @param array|string $tags
     */
    public function setTags($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        $this->tags = $tags;
    }

    /**
     * Returns all tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message[]|Collection $messages
     */
    public function setMessages(Collection $messages = null)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_replace(
            array_diff_key(get_object_vars($this), array_flip(['score', 'parent', 'ttl', 'highlight'])),
            [
                'id' => $this->getId(),
                'messages' => $this->getMessagesArray(),
                'tags' => $this->getTags(),
                'description' => $this->getDescription(),
                'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Returns messages as array.
     *
     * Format: ['locale' => 'message'].
     *
     * @return array
     */
    public function getMessagesArray()
    {
        $result = [];
        foreach ($this->getMessages() as $message) {
            $result[$message->getLocale()] = $message;
        }

        return $result;
    }
}
