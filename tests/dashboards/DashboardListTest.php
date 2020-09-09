<?php
namespace tests\dashboards;

use Dotenv\Dotenv;
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
        $env = Dotenv::create(getcwd() . '/tests/');
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
                ]),
                $this->buildItem([
                    'name' => 'test_0',
                    'title' => 'Test item',
                    'description' => 'Test description'
                ])
            ]
        ]);

        $result = $list->render();
        $this->assertNotEmpty($result);
        $this->assertEquals($this->getBlueprint(), $result);
    }

    /**
     * @param $config
     * @return IHasName
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

    protected function getBlueprint()
    {
        return file_get_contents(getcwd() . '/tests/resources/list.html');
    }
}
