<?php

use PHPUnit\Framework\TestCase;
use extas\components\dashboards\DashboardList;

/**
 * Class DashboardListTest
 *
 * @author jeyroik@gmail.com
 */
class DashboardListTest extends TestCase
{
    public function testRender()
    {
        $list = new DashboardList([
            DashboardList::FIELD__TITLE => 'Test title',
            DashboardList::FIELD__SELECTED => 'test',
            DashboardList::FIELD__ITEMS => [
                [
                    'name' => 'test',
                    'title' => 'Test item'
                ]
            ]
        ]);

        $result = $list->render();
        $this->assertNotEmpty($result);
    }
}
