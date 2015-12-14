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

use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

/**
 * Tests list controller actions.
 */
class ListControllerTest extends AbstractElasticsearchTestCase
{
    /**
     * Tests if list is returning 200 response.
     */
    public function testListAction()
    {
        $this->getManager();

        $client = self::createClient();
        $crawler = $client->request('GET', '/translate/list');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
