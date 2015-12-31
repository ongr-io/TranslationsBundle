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
    public $key;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    public $locale;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    public $message;

    /**
     * @var string
     *
     * @ES\Property(type="string", options={"index"="not_analyzed"})
     */
    public $domain;

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
