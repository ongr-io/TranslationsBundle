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
        $translation->setDomain('foo_domain');
        $message = new Message();
        $message->setLocale('en');
        $message->setMessage('foo_message');
        $message->setCreatedAt($date);
        $message->setUpdatedAt($date);
        $translation->addMessage($message);

        $expectedJson = '{"id":"10b9bf5859bce4052de0dac6c01324679d21cad0","domain":"foo_domain",'
            . '"tags":[],"messages":{"en":{"message":"foo_message","status":"fresh",'
            . "\"createdAt\":\"{$dateString}\",\"updatedAt\":\"{$dateString}\"}},\"key\":null,\"path\":null,"
            . "\"format\":null,\"createdAt\":\"{$dateString}\",\"updatedAt\":\"{$dateString}\"}";

        $this->assertEquals($expectedJson, json_encode($translation), 'JSON strings should be equal');
    }
}
