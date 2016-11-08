<?php

namespace ONGR\TranslationsBundle\Tests\Functional\Filter;

use ONGR\ElasticsearchDSL\Search;
use ONGR\TranslationsBundle\Filter\SizeFilter;
use Symfony\Component\HttpFoundation\Request;

class SizeFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testModifySearch()
    {
        $search = new Search();
        $filter = new SizeFilter();
        $filter->setSize(100);
        $filter->modifySearch($search);

        $this->assertEquals(['size' => 100], $search->toArray());
    }

    public function testGetState()
    {
        $state = (new SizeFilter())->getState(new Request());

        $this->assertTrue($state->isActive());
    }
}
