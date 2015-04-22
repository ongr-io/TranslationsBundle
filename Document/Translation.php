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
class Translation extends AbstractDocument implements \JsonSerializable
{
    /**
     * @var string
     *
     * @ES\Property(name="domain", type="string", index="not_analyzed")
     */
    private $domain;

    /**
     * @var Tag[]
     *
     * @ES\Property(name="tags", type="object", multiple=true, objectName="ONGRTranslationsBundle:Tag")
     */
    private $tags = [];

    /**
     * @var Message[]
     *
     * @ES\Property(name="messages", type="object", multiple=true, objectName="ONGRTranslationsBundle:Message")
     */
    private $messages = [];

    /**
     * @var string
     *
     * @ES\Property(name="key", type="string", index="not_analyzed")
     */
    private $key;

    /**
     * @var string
     *
     * @ES\Property(name="path", type="string", index="not_analyzed")
     */
    private $path;

    /**
     * @var string
     *
     * @ES\Property(name="format", type="string")
     */
    private $format;

    /**
     * @var \DateTime
     *
     * @ES\Property(name="created_at", type="date")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ES\Property(name="updated_at", type="date")
     */
    private $updatedAt;

    /**
     * Sets timestamps.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns all tags.
     *
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Adds a single tag.
     *
     * @param Tag $tag
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;
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
     * Return unique document id.
     *
     * @return string
     */
    public function getId()
    {
        if (!parent::getId()) {
            $this->setId(sha1($this->getDomain() . $this->getKey()));
        }

        return parent::getId();
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
     * @param Message[] $messages
     */
    public function setMessages($messages)
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
                'tags' => $this->getTagsArray(),
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
    private function getMessagesArray()
    {
        $result = [];
        foreach ($this->getMessages() as $message) {
            $result[$message->getLocale()] = $message;
        }

        return $result;
    }

    /**
     * Returns tags array.
     *
     * @return array
     */
    private function getTagsArray()
    {
        if ($this->tags === null) {
            return [];
        }

        $result = [];
        foreach ($this->tags as $tag) {
            $result[] = $tag->getName();
        }

        return $result;
    }
}
