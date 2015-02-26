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
        $translation = new Translation();
        $translation->setDomain('foo_domain');
        $message = new Message();
        $message->setLocale('en');
        $message->setMessage('foo_message');
        $translation->addMessage($message);

        $expectedJson = '{"domain":"foo_domain","group":"default","messages":{"en":"foo_message"},"key":null,'
            . '"id":"10b9bf5859bce4052de0dac6c01324679d21cad0"}';

        $this->assertEquals($expectedJson, json_encode($translation), 'JSON strings should be equal');
    }
}
