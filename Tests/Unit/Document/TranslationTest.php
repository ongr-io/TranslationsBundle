<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\Document;

use ONGR\TranslationsBundle\Document\Message;
use ONGR\TranslationsBundle\Document\Translation;

/**
 * Translation document tests.
 */
class TranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if document JSON serialization is correct.
     */
    public function testSerialize()
    {
        $date = new \DateTime();
        $dateString = $date->format('Y-m-d H:i:s');
        $translation = new Translation();
        $translation->setCreatedAt($date);
        $translation->setUpdatedAt($date);
        $translation->setTags('single tag');
        $translation->setDescription('foo description');
        $translation->setDomain('foo_domain');
        $message = new Message();
        $message->setLocale('en');
        $message->setMessage('foo_message');
        $translation->addMessage($message);

        $expected = [
            'createdAt' => $dateString,
            'updatedAt' => $dateString,
            'domain' => 'foo_domain',
            'tags' => ['single tag'],
            'format' => null,
            'key' => null,
            'path' => null,
            'id' => sha1('foo_domain'),
            'description' => 'foo description',
            'messages' => [
                'en' => $message
            ],
        ];

        $this->assertEquals($expected, $translation->jsonSerialize());
    }
}
