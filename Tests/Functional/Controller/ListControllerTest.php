<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListControllerTest extends WebTestCase
{
    /**
     * Tests if list is returning 200 response.
     */
    public function testListRoute()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/translate/list');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
