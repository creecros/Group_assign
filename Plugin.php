<?php

namespace Kanboard\Plugin\group_assign;

use Kanboard\Core\Plugin\Base;
use Kanboard\Model\TaskModel;
//use Kanboard\Plugin\group_assign\Filter\group_assign_filter; //Needs work
use Kanboard\Model\TaskFinderModel;
use PicoDb\Table;

class Plugin extends Base
{
    public function initialize()
    {
        // $this->template->setTemplateOverride('header', 'theme:layout/header');

        //Models
        $this->hook->on();
        $this->hook->on();
        
        //Details
        $this->template->setTemplateOverride();
        $this->template->setTemplateOverride();
        
        //Task creation and modify
        $this->template->setTemplateOverride('task_creation/show', 'group_assign:task_creation/show');
        $this->template->setTemplateOverride('task_modification/show', 'group_assign:task_modification/show');
      
        //Board 
        $this->template->hook->attach();
        $this->template->hook->attach();
        
    }


    public function getPluginName()
    {
        return 'Group Assign';
    }
    public function getPluginDescription()
    {
        return t('Add group assignment to tasks');
    }
    public function getPluginAuthor()
    {
        return 'Craig Crosby';
    }
    public function getPluginVersion()
    {
        return '0.0.1';
    }
    public function getPluginHomepage()
    {
        return 'https://github.com/creecros/group_assign';
    }
}
