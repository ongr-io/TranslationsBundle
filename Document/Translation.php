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
     * @var string
     * 
     * @ES\Property(name="tags", type="object", multiple=true, objectName="ONGRTranslationsBundle:Tag")
     */
    private $tags;

    /**
     * @var Message
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
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
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
     * Return unique document id.
     *
     * @return string
     */
    public function getId()
    {
        return sha1($this->getDomain() . $this->getKey());
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
            ]
        );
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
     * Returns messages as array.
     *
     * array (
     *  'locale' => 'message'
     * )
     *
     * @return array
     */
    private function getMessagesArray()
    {
        $result = [];
        foreach ($this->getMessages() as $message) {
            $result[$message->getLocale()] = $message->getMessage();
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
        return $this->tags !== null ? array_map(
            function ($value) {
                return $value->getName();
            },
            $this->tags
        ) : [];
    }
}
