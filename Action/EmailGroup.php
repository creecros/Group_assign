<?php

namespace Kanboard\Plugin\Group_assign\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class EmailGroup extends Base
{
   
    public function getDescription()
    {
        return t('Send a task by email to assigned group members');
    }

   
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_MOVE_COLUMN,
            TaskModel::EVENT_CLOSE,
            TaskModel::EVENT_CREATE,
        );
    }

   
    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
	    'subject' => t('Email subject'),
        );
    }

   
    public function getEventRequiredParameters()
    {
        return array(
            'task_id',
            'task' => array(
                'project_id',
                'column_id',
		'owner_id',
		'owner_gp',
            ),
        );
    }

    
    public function doAction(array $data)
    {
	$groupmembers = $this->groupMemberModel->getMembers($data['task']['owner_gp']);
	
             if (! empty($groupmembers)) {
	       foreach ($groupmembers as $members) {
               $user = $this->userModel->getById($members['id']);
               if (! empty($user['email'])) {
                 $this->emailClient->send(
                   $user['email'],
                   $user['name'] ?: $user['username'],
                   $this->getParam('subject'),
                   $this->template->render('notification/task_create', array(
                    'task' => $data['task'],
                    ))
                 );
	       }
	       }
             return true;
	     }

        return false;
    }


   
    public function hasRequiredCondition(array $data)
    {
        return $data['task']['column_id'] == $this->getParam('column_id');
    }
}
