<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Model\TaskDuplicationModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Model\ProjectPermissionModel;
use Kanboard\Model\TaskLinkModel;

/**
 * Task Project Move
 *
 * @package  Kanboard\Plugins\Group_assign
 * @author   Craig Crosby
 */
class TaskProjectMoveModel extends TaskDuplicationModel
{
    /**
     * Move a task to another project
     *
     * @access public
     * @param  integer    $task_id
     * @param  integer    $project_id
     * @param  integer    $swimlane_id
     * @param  integer    $column_id
     * @param  integer    $category_id
     * @param  integer    $owner_id
     * @return boolean
     */
    public function moveToProject($task_id, $project_id, $swimlane_id = null, $column_id = null, $category_id = null, $owner_id = null)
    {
        $task = $this->taskFinderModel->getById($task_id);
        $values = $this->prepare($project_id, $swimlane_id, $column_id, $category_id, $owner_id, $task);

        $this->checkDestinationProjectValues($values);
        $this->tagDuplicationModel->syncTaskTagsToAnotherProject($task_id, $project_id);

        // Check if the group is allowed for the destination project and unassign if not
        $group_id = $this->db->table(TaskModel::TABLE)->eq('id', $task_id)->findOneColumn('owner_gp');
        if ($group_id > 0) {
            $group_in_project = $this->db
              ->table(ProjectGroupRoleModel::TABLE)
              ->eq('project_id', $project_id)
              ->eq('group_id', $group_id)
              ->exists();
            if (!$group_in_project) {
                $this->db->table(TaskModel::TABLE)->eq('id', $task_id)->update(['owner_gp' => 0]);
            }
        }

        // Check if the other assignees are allowed for the destination project and remove from ms group if not
        $ms_id = $this->db->table(TaskModel::TABLE)->eq('id', $task_id)->findOneColumn('owner_ms');
        if ($ms_id > 0) {
            $users_in_ms = $this->multiselectMemberModel->getMembers($ms_id);
            foreach ($users_in_ms as $user) {
                if (! $this->projectPermissionModel->isAssignable($project_id, $user['id'])) {
                    $this->multiselectMemberModel->removeUser($ms_id, $user['id']);
                }
            }
        }


        if ($this->db->table(TaskModel::TABLE)->eq('id', $task_id)->update($values)) {
            $this->queueManager->push($this->taskEventJob->withParams($task_id, array(TaskModel::EVENT_MOVE_PROJECT), $values));
        }

        return true;
    }

    /**
     * Prepare new task values
     *
     * @access protected
     * @param  integer $project_id
     * @param  integer $swimlane_id
     * @param  integer $column_id
     * @param  integer $category_id
     * @param  integer $owner_id
     * @param  array   $task
     * @return array
     */
    protected function prepare($project_id, $swimlane_id, $column_id, $category_id, $owner_id, array $task)
    {
        $values = array();
        $values['is_active'] = 1;
        $values['project_id'] = $project_id;
        $values['column_id'] = $column_id !== null ? $column_id : $task['column_id'];
        $values['position'] = $this->taskFinderModel->countByColumnId($project_id, $values['column_id']) + 1;
        $values['swimlane_id'] = $swimlane_id !== null ? $swimlane_id : $task['swimlane_id'];
        $values['category_id'] = $category_id !== null ? $category_id : $task['category_id'];
        $values['owner_id'] = $owner_id !== null ? $owner_id : $task['owner_id'];
        $values['priority'] = $task['priority'];
        return $values;
    }
}
