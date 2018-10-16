<?php

require_once 'tests/units/Base.php';

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
}
