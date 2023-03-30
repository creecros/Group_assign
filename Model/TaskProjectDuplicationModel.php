<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Model\TaskProjectDuplicationModel as KanboardTaskProjectDuplicationModel;

/**
 * Task Project Duplication
 *
 * @package  Kanboard\Plugins\Group_assign
 * @author   Craig Crosby
 */
class TaskProjectDuplicationModel extends KanboardTaskProjectDuplicationModel
{
    /**
     * Duplicate a task to another project
     *
     * @access public
     * @param  integer    $task_id
     * @param  integer    $project_id
     * @param  integer    $swimlane_id
     * @param  integer    $column_id
     * @param  integer    $category_id
     * @param  integer    $owner_id
     * @return boolean|integer
     */
    public function duplicateToProject($task_id, $project_id, $swimlane_id = null, $column_id = null, $category_id = null, $owner_id = null, $owner_gp = 0, $owner_ms = 0)
    {
        $values = $this->prepare($task_id, $project_id, $swimlane_id, $column_id, $category_id, $owner_id);
        $new_task_id = parent::duplicateToProject($task_id, $project_id, $swimlane_id, $column_id, $category_id, $owner_id);

        if ($new_task_id !== false) {
            // Check if the group is allowed for the destination project
            $group_id = $this->db->table(TaskModel::TABLE)->eq('id', $task_id)->findOneColumn('owner_gp');
            if ($group_id > 0) {
                $group_in_project = $this->db
                    ->table(ProjectGroupRoleModel::TABLE)
                    ->eq('project_id', $values['project_id'])
                    ->eq('group_id', $group_id)
                    ->exists();
                if ($group_in_project) {
                    $this->db->table(TaskModel::TABLE)->eq('id', $new_task_id)->update(['owner_gp' => $group_id]);
                }
            }

            // Check if the other assignees are allowed for the destination project
            $ms_id = $this->db->table(TaskModel::TABLE)->eq('id', $task_id)->findOneColumn('owner_ms');
            if ($ms_id > 0) {
                $users_in_ms = $this->multiselectMemberModel->getMembers($ms_id);
                $new_ms_id = $this->multiselectModel->create();
                $this->db->table(TaskModel::TABLE)->eq('id', $new_task_id)->update(['owner_ms' => $new_ms_id]);
                foreach ($users_in_ms as $user) {
                    if ($this->projectPermissionModel->isAssignable($values['project_id'], $user['id'])) {
                        $this->multiselectMemberModel->addUser($new_ms_id, $user['id']);
                    }
                }
            }
        }

        return $new_task_id;
    }
}
