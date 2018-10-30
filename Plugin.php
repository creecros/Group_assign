<?php

namespace Kanboard\Plugin\Group_assign;

use Kanboard\Core\Plugin\Base;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Plugin\Group_assign\Model\NewTaskFinderModel;
use Kanboard\Plugin\Group_assign\Helper\NewTaskHelper;
use Kanboard\Plugin\Group_assign\Filter\TaskAssigneeFilter;
use Kanboard\Plugin\Group_assign\Action\EmailGroup;
use Kanboard\Plugin\Group_assign\Action\EmailGroupDue;
use Kanboard\Plugin\Group_assign\Action\AssignGroup;
use PicoDb\Table;

class Plugin extends Base
{
    global $SCHEMA_VERSION;
    
    public function initialize()
    {
        $SCHEMA_VERSION = $this->db->getDriver()->getSchemaVersion();
        
        //Helpers
        $this->helper->register('newTaskHelper', '\Kanboard\Plugin\Group_assign\Helper\NewTaskHelper');
        
        //Models
        if ($SCHEMA_VERSION >= 132) {
            $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                return new NewTaskFinderModel($c);
            });
        } else {
            $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                return new OldTaskFinderModel($c);
            });
        }
        
        //Task - Template - details.php
        $this->template->hook->attach('template:task:details:third-column', 'group_assign:task/details');
        
        //Forms - task_creation.php and task_modification.php
        $this->template->setTemplateOverride('task_creation/show', 'group_assign:task_creation/show');
        $this->template->setTemplateOverride('task_modification/show', 'group_assign:task_modification/show');
      
        //Board
         $this->template->hook->attach('template:board:private:task:before-title', 'group_assign:board/group');
        
        //Filter
        $this->container->extend('taskLexer', function($taskLexer, $c) {
            $taskLexer->withFilter(TaskAssigneeFilter::getInstance()->setCurrentUserId($c['userSession']->getId()));
            return $taskLexer;
        });
        
        //Actions
        $this->actionManager->register(new EmailGroup($this->container));
        $this->actionManager->register(new EmailGroupDue($this->container));
        $this->actionManager->register(new AssignGroup($this->container));
        
        //Params
        $this->template->setTemplateOverride('action_creation/params', 'group_assign:action_creation/params');

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
        return '0.0.3';
    }
    public function getPluginHomepage()
    {
        return 'https://github.com/creecros/group_assign';
    }
}
