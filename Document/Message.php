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
 * Object for translations in elasticsearch.
 *
 * @ES\Object()
 */
class Message implements \JsonSerializable
{
    const DIRTY = 'dirty';
    const FRESH = 'fresh';

    /**
     * @var string
     *
     * @ES\Property(name="locale", type="string", options={"index"="not_analyzed"})
     */
    private $locale;

    /**
     * @var string
     *
     * @ES\Property(name="message", type="string")
     */
    private $message;

    /**
     * @var string
     *
     * @ES\Property(name="status", type="string")
     */
    private $status;

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
     * Sets created date.
     */
    public function __construct()
    {
        $this->status = self::FRESH;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        if (in_array($status, (new \ReflectionObject($this))->getConstants())) {
            $this->status = $status;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'message' => $this->getMessage(),
            'status' => $this->getStatus(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
