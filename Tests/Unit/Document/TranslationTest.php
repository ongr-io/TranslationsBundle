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
        $translation->setLocale('foo_locale');

        $expectedJson = '{"domain":"foo_domain","group":"default","locale":"foo_locale","message":null,' .
            '"key":null,"id":"f1279cd423ab905c338b34c6fab2a24ad1ba1209",'
            . '"score":null,"parent":null,"ttl":null,"highlight":null}';

        $this->assertEquals($expectedJson, json_encode($translation), 'JSON strings should be equal');
    }
}
