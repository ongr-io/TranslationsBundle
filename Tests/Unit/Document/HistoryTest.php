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

use ONGR\TranslationsBundle\Document\History;

class HistoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $date = new \DateTime();
        $dateString = $date->format('Y-m-d H:i:s');
        $history = new History();
        $history->setUpdatedAt($date);
        $history->setKey('foo');
        $history->setLocale('de');
        $history->setMessage('some message');
        $history->setDomain('foo_domain');

        $expected = [
            'updatedAt' => $dateString,
            'domain' => 'foo_domain',
            'locale' => 'de',
            'key' => 'foo',
            'id' =>null,
            'message' => 'some message',
        ];

        $this->assertEquals($expected, $history->jsonSerialize());
    }
}
