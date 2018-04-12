<?php

namespace Kanboard\Plugin\Group_assign;

use Kanboard\Core\Plugin\Base;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Plugin\Group_assign\Model\NewTaskFinderModel;
use Kanboard\Plugin\Group_assign\Helper\NewTaskHelper;
use Kanboard\Plugin\Group_assign\Controller\TaskCreationController;
use PicoDb\Table;

class Plugin extends Base
{
    public function initialize()
    {
        
        $groups = $this->projectGroupRoleModel->getGroups($project['id']);

        //Helpers
        $this->helper->register('newTaskHelper', '\Kanboard\Plugin\Group_assign\Helper\NewTaskHelper');
        
        //Models
        $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
            return new NewTaskFinderModel($c);
        });
           
        //Task - Template - details.php
        
        //Forms - task_creation.php and task_modification.php
        $this->template->setTemplateOverride('task_creation/show', 'group_assign:task_creation/show');
        $this->template->setTemplateOverride('task_modification/show', 'group_assign:task_modification/show');
      
        //Board - Template - task_private.php, task_avatar.php, task_public.php
        
    }
    
     public function getClasses()
    {
      return array(
        'Plugin\Group_assign\Controller' => array(
          'TaskCreationController'
        ),
      );
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
