<?php

require_once 'tests/units/Base.php';

use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Group_assign\Helper\NewTaskHelper;

class NewTaskHelperTest extends Base
{
    public function setUp()
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();
    }
    public function testSelectPriority()
    {
        $helper = new NewTaskHelper($this->container);
        $this->assertNotEmpty($helper->renderPriorityField(array('priority_end' => '1', 'priority_start' => '5', 'priority_default' => '2'), array()));
        $this->assertNotEmpty($helper->renderPriorityField(array('priority_end' => '3', 'priority_start' => '1', 'priority_default' => '2'), array()));
    }
    public function testFormatPriority()
    {
        $helper = new NewTaskHelper($this->container);
        $this->assertEquals(
            '<span class="task-priority" title="Task priority">P2</span>',
            $helper->renderPriority(2)
        );
        $this->assertEquals(
            '<span class="task-priority" title="Task priority">-P6</span>',
            $helper->renderPriority(-6)
        );
    }
}
