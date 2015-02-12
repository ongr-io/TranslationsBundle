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
     * @ES\Property(name="group", type="string")
     */
    private $group;

    /**
     * @var string
     *
     * @ES\Property(name="locale", type="string", index="not_analyzed")
     */
    private $locale;

    /**
     * @var string
     *
     * @ES\Property(name="message", type="string", index="not_analyzed")
     */
    private $message;

    /**
     * @var string
     *
     * @ES\Property(name="key", type="string", index="not_analyzed")
     */
    private $key;

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

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
     * @return DocumentInterface
     */
    public function getId()
    {
        return sha1($this->getDomain() . $this->getLocale() . $this->getKey());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
