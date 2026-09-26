<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Plugin\Group_assign\Model\TaskProjectMoveModel;
use Kanboard\Plugin\Group_assign\Model\TaskRecurrenceModel;
use Kanboard\Plugin\Group_assign\Plugin;


class PluginTest extends Base
{
    public function testPlugin()
    {
        $plugin = new Plugin($this->container);
        $this->assertSame(null, $plugin->initialize());
        $this->assertNotEmpty($plugin->getPluginName());
        $this->assertNotEmpty($plugin->getPluginDescription());
        $this->assertNotEmpty($plugin->getPluginAuthor());
        $this->assertNotEmpty($plugin->getPluginVersion());
        $this->assertNotEmpty($plugin->getPluginHomepage());
    }

    public function testPluginOverridesTaskLifecycleServices()
    {
        $plugin = new Loader($this->container);
        $plugin->scan();

        $this->assertInstanceOf(TaskProjectMoveModel::class, $this->container['taskProjectMoveModel']);
        $this->assertInstanceOf(TaskRecurrenceModel::class, $this->container['taskRecurrenceModel']);
    }
}
