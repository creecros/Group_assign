<?php

namespace Kanboard\Plugin\Group_assign\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Action\Base;

class AssignGroup extends Base
{
    /**
     * Get automatic action description
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return t('Assign the task to a specific group');
    }

    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_CREATE_UPDATE,
            TaskModel::EVENT_MOVE_COLUMN,
        );
    }

    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
            'group_id' => t('Group'),
        );
    }

    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array(
            'task_id',
            'task' => array(
                'project_id',
                'column_id',
            ),
        );
    }

    /**
     * Execute the action (assign the given user)
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool            True if the action was executed or false when not executed
     */
    public function doAction(array $data)
    {
        $group_id = $this->groupAssignmentModel->normalizeGroupId($this->getParam('group_id'));
        if ($group_id === false || ! $this->groupAssignmentModel->validateGroupAssignment($data['task']['project_id'], $group_id)) {
            return false;
        }

        $task = $this->taskFinderModel->getById($data['task_id']);
        if (empty($task)) {
            return false;
        }

        $values = array(
            'id' => $data['task_id'],
            'owner_gp' => $group_id,
        );
        $result = $this->taskModificationModel->update($values);
        if ($result && $this->groupAssignmentModel->assignmentsChanged($task, $values)) {
            $this->groupAssignmentModel->assigneeChanged($task, $values);
        }

        return $result;
    }

    /**
     * Check if the event data meet the action condition
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return $data['task']['column_id'] == $this->getParam('column_id');
    }
}
