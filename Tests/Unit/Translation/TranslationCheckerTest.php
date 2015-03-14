<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Unit\Translation;

use ONGR\TranslationsBundle\Translation\TranslationChecker;

class TranslationCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for checking traslation.
     *
     * @return array
     */
    public function getTestCheckData()
    {
        return [
            [
                'works',
                true,
                'en',
            ],
            [
                '{0} There are no apples|{1} There is one apple|]1,19] There'
                . ' are %count% apples|[20,Inf] There are many apples',
                true,
                'en',
            ],
            [
                '{0} There are no apples|[20,Inf] There are many apples|'
                . 'There is one apple|a_few: There are %count% apples',
                true,
                'en',
            ],
            [
                '{0 There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
                false,
                'de',
            ],
            [
                '{0} There are no apples|{1} There is one apple|]1,I] There are %count% apples',
                false,
                'en',
            ],
            [
                '{0} There are no apples|{1} There is one apple|]1Inf[ There are %count% apples',
                false,
                'en',
            ],
            [
                'Il y a %count% pomme|Il y a %count% pommes',
                true,
                'fr',
            ],
            [
                'There is one apple|There are %count% apples',
                true,
                'en',
            ],
        ];
    }

    /**
     * Tests if TranslationChecker#check method works as expected.
     *
     * @param string $message
     * @param bool   $expected
     * @param string $locale
     *
     * @dataProvider getTestCheckData
     */
    public function testCheck($message, $expected, $locale)
    {
        $this->assertEquals($expected, TranslationChecker::check($message, $locale));
    }
}
