<?php

use PHPUnit\Framework\TestCase;
use extas\components\dashboards\DashboardList;
use extas\interfaces\IHasName;
use extas\components\THasName;
use extas\components\Item;

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
            DashboardList::FIELD__NAME => 'test_list',
            DashboardList::FIELD__TITLE => 'Test title',
            DashboardList::FIELD__SELECTED => 'test',
            DashboardList::FIELD__ITEMS => [
                $this->buildItem([
                    'name' => 'test',
                    'title' => 'Test item',
                    'description' => 'Test description'
                ])
            ]
        ]);

        $result = $list->render();
        $mustBe = '<select name="test_list" title="Test title" class="form-control">
    <option value="test" title="Test description" selected>Test item</option>
</select>';
        $this->assertNotEmpty($result);
        $this->assertEquals($mustBe, $result);
    }

    /**
     * @param $config
     * @return \extas\components\Item|__anonymous@962
     */
    protected function buildItem($config)
    {
        return new class ($config) extends Item implements IHasName {
            use THasName;
            protected function getSubjectForExtension(): string
            {
                return '';
            }
        };
    }
}
