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
 * Holds translations history.
 *
 * @ES\Document(type="history")
 */
class History extends AbstractDocument
{
    /**
     * @var string
     *
     * @ES\Property(name="key", type="string", index="not_analyzed")
     */
    public $key;

    /**
     * @var string
     *
     * @ES\Property(name="locale", type="string", index="not_analyzed")
     */
    public $locale;

    /**
     * @var string
     *
     * @ES\Property(name="message", type="string", index="not_analyzed")
     */
    public $message;

    /**
     * @var string
     *
     * @ES\Property(name="domain", type="string", index="not_analyzed")
     */
    public $domain;

    /**
     * @var \DateTime
     *
     * @ES\Property(name="created_at", type="date")
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
