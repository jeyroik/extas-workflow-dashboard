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
    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());
    }

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
