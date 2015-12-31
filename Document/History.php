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
use ONGR\ElasticsearchBundle\Document\DocumentTrait;

/**
 * Holds translations history.
 *
 * @ES\Document(type="history")
 */
class History
{
    use DocumentTrait;

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
    private $locale;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $message;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    private $domain;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date")
     */
    private $createdAt;

    /**
     * Sets timestamps.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param string $key
     *
     * @return History
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $locale
     *
     * @return History
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $message
     *
     * @return History
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $domain
     *
     * @return History
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
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
}
