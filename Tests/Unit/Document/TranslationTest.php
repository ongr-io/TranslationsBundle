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
     * @return Translation
     */
    private function getTranslation()
    {
        $date = new \DateTime();
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

        return $translation;
    }

    public function testSerialize()
    {
        $translation = $this->getTranslation();
        $dateString = $translation->getCreatedAt()->format('Y-m-d H:i:s');

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
                'en' => $translation->getMessagesArray()['en']
            ],
        ];

        $this->assertEquals($expected, $translation->jsonSerialize());
    }

    public function testGetMessageByLocale()
    {
        $translation = $this->getTranslation();

        $this->assertNotNull($translation->getMessageByLocale('en'));
        $this->assertNull($translation->getMessageByLocale('lt'));
    }
}
