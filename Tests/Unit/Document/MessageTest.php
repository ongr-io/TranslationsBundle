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

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $date = new \DateTime();
        $dateString = $date->format('Y-m-d H:i:s');
        $message = new Message();
        $message->setLocale('fr');
        $message->setMessage('foo_message');
        $message->setCreatedAt($date);
        $message->setUpdatedAt($date);


        $expected = [
            'status' => 'fresh',
            'createdAt' => $dateString,
            'updatedAt' => $dateString,
            'locale' => 'fr',
            'message' => 'foo_message'
        ];

        $this->assertEquals($expected, $message->jsonSerialize());
    }
}
