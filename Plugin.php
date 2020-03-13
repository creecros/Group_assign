<?php

namespace Kanboard\Plugin\Group_assign;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Plugin\Group_assign\Model\NewTaskFinderModel;
use Kanboard\Plugin\Group_assign\Model\NewUserNotificationFilterModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Plugin\Group_assign\Model\OldTaskFinderModel;
use Kanboard\Plugin\Group_assign\Helper\NewTaskHelper;
use Kanboard\Plugin\Group_assign\Filter\TaskAllAssigneeFilter;
use Kanboard\Plugin\Group_assign\Action\EmailGroup;
use Kanboard\Plugin\Group_assign\Action\EmailGroupDue;
use Kanboard\Plugin\Group_assign\Action\EmailOtherAssignees;
use Kanboard\Plugin\Group_assign\Action\EmailOtherAssigneesDue;
use Kanboard\Plugin\Group_assign\Action\AssignGroup;
use Kanboard\Plugin\Group_assign\Model\GroupAssignCalendarModel;
use Kanboard\Plugin\Group_assign\Model\GroupAssignTaskDuplicationModel;
use Kanboard\Plugin\Group_assign\Model\TaskProjectDuplicationModel;
use Kanboard\Plugin\Group_assign\Model\TaskProjectMoveModel;
use Kanboard\Plugin\Group_assign\Model\TaskRecurrenceModel;
use Kanboard\Plugin\Group_assign\Model\NewMetaMagikSubquery;
use Kanboard\Plugin\Group_assign\Model\OldMetaMagikSubquery;
use PicoDb\Table;
use PicoDb\Database;
use Kanboard\Core\Security\Role;

class Plugin extends Base
{
    
    public function initialize()
    {
        //Events & Changes        
        $this->template->setTemplateOverride('task/changes', 'group_assign:task/changes');
        
        //Notifications
        $this->container['userNotificationFilterModel'] = $this->container->factory(function ($c) {
                return new NewUserNotificationFilterModel($c);
        });

        //Helpers
        $this->helper->register('newTaskHelper', '\Kanboard\Plugin\Group_assign\Helper\NewTaskHelper');
        $this->helper->register('smallAvatarHelperExtend', '\Kanboard\Plugin\Group_assign\Helper\SmallAvatarHelperExtend');
        
        
        //Models and backward compatibility
        
        $applications_version = str_replace('v', '', APP_VERSION);
        if (strpos(APP_VERSION, 'master') !== false && file_exists('ChangeLog')) { $applications_version = trim(file_get_contents('ChangeLog', false, null, 8, 6), ' '); }
        $clean_appversion = preg_replace('/\s+/', '', $applications_version);
        
        if (version_compare($clean_appversion, '1.2.5', '>')) {
            if (file_exists('plugins/MetaMagik')){
                $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                    return new NewMetaMagikSubquery($c);
                });
            } else {
                $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                    return new NewTaskFinderModel($c);
                });
            }
            $this->container['taskDuplicationModel'] = $this->container->factory(function ($c) {
                return new GroupAssignTaskDuplicationModel($c);
            });
            $this->container['taskProjectDuplicationModel '] = $this->container->factory(function ($c) {
                return new TaskProjectDuplicationModel ($c);
            });
            $this->container['taskProjectMoveModel '] = $this->container->factory(function ($c) {
                return new TaskProjectMoveModel ($c);
            });
            $this->container['taskRecurrenceModel '] = $this->container->factory(function ($c) {
                return new TaskRecurrenceModel ($c);
            });
        } else {
            if (file_exists('plugins/MetaMagik')){
                $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                    return new OldMetaMagikSubquery($c);
                });
            } else {
                $this->container['taskFinderModel'] = $this->container->factory(function ($c) {
                    return new OldTaskFinderModel($c);
                });
            }
            $this->container['taskDuplicationModel'] = $this->container->factory(function ($c) {
                return new GroupAssignTaskDuplicationModel($c);
            });
            $this->container['taskProjectDuplicationModel '] = $this->container->factory(function ($c) {
                return new TaskProjectDuplicationModel ($c);
            });
            $this->container['taskProjectMoveModel '] = $this->container->factory(function ($c) {
                return new TaskProjectMoveModel ($c);
            });
            $this->container['taskRecurrenceModel '] = $this->container->factory(function ($c) {
                return new TaskRecurrenceModel ($c);
            });
        }
        
        //Task - Template - details.php
        $this->template->hook->attach('template:task:details:third-column', 'group_assign:task/details');
        $this->template->hook->attach('template:task:details:third-column', 'group_assign:task/multi');
        
        //Forms - task_creation.php and task_modification.php
        $this->template->setTemplateOverride('task_creation/show', 'group_assign:task_creation/show');
        $this->template->setTemplateOverride('task_modification/show', 'group_assign:task_modification/show');
        
        //Board
        $this->template->hook->attach('template:board:private:task:before-title', 'group_assign:board/group');
        $this->template->hook->attach('template:board:private:task:before-title', 'group_assign:board/multi');
        $groupmodel = $this->projectGroupRoleModel;
        $this->template->hook->attachCallable('template:app:filters-helper:after', 'group_assign:board/filter', function($array = array()) use ($groupmodel) {
            if(!empty($array) && $array['id'] >= 1){
                return ['grouplist' => array_column($groupmodel->getGroups($array['id']), 'name')];
            } else {
                return ['grouplist' => array()];
            }
        });  
        
        //Filter
        $this->container->extend('taskLexer', function($taskLexer, $c) {
            $taskLexer->withFilter(TaskAllAssigneeFilter::getInstance()->setDatabase($c['db'])
                                                                    ->setCurrentUserId($c['userSession']->getId()));
            return $taskLexer;
        });
        
        //Actions
        $this->actionManager->register(new EmailGroup($this->container));
        $this->actionManager->register(new EmailGroupDue($this->container));
        $this->actionManager->register(new EmailOtherAssignees($this->container));
        $this->actionManager->register(new EmailOtherAssigneesDue($this->container));
        $this->actionManager->register(new AssignGroup($this->container));
        
        //Params
        $this->template->setTemplateOverride('action_creation/params', 'group_assign:action_creation/params');
        
        //CSS
        $this->hook->on('template:layout:css', array('template' => 'plugins/Group_assign/Assets/css/group_assign.css'));

        //JS
        $this->hook->on('template:layout:js', array('template' => 'plugins/Group_assign/Assets/js/group_assign.js'));

        //Calendar Events
        $container = $this->container;
        
        $this->hook->on('controller:calendar:user:events', function($user_id, $start, $end) use ($container) {
            $model = new GroupAssignCalendarModel($container);
            return $model->getUserCalendarEvents($user_id, $start, $end); // Return new events
        });
        
        //Roles

        $this->template->hook->attach('template:config:application', 'group_assign:config/toggle');
    
        if ($this->configModel->get('enable_am_group_management', '2') == 1) { 
            $this->applicationAccessMap->add('GroupListController', '*', Role::APP_MANAGER);
            $this->applicationAccessMap->add('GroupCreationController', '*', Role::APP_MANAGER);
            $this->template->setTemplateOverride('header/user_dropdown', 'group_assign:header/user_dropdown'); 
        }


    }
    
    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }
    
    public function getClasses()
    {
        return [
            'Plugin\Group_assign\Model' => [
                'MultiselectMemberModel', 'MultiselectModel', 'GroupColorExtension', 'TaskProjectDuplicationModel', 'TaskProjectMoveModel', 'TaskRecurrenceModel',
            ],
        ];
    }

    public function getPluginName()
    {
        return 'Group_assign';
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
        return '1.7.9';
    }
    public function getPluginHomepage()
    {
        return 'https://github.com/creecros/group_assign';
    }
}
