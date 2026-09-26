<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Core\Base;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Model\TaskModel;

class GroupAssignmentModel extends Base
{
    public function normalizeGroupId($group_id)
    {
        if (is_int($group_id)) {
            return $group_id >= 0 ? $group_id : false;
        }

        if (is_string($group_id) && ctype_digit($group_id)) {
            return (int) $group_id;
        }

        return false;
    }

    public function normalizeUserId($user_id)
    {
        if (is_int($user_id)) {
            return $user_id > 0 ? $user_id : false;
        }

        if (is_string($user_id) && ctype_digit($user_id)) {
            $user_id = (int) $user_id;
            return $user_id > 0 ? $user_id : false;
        }

        return false;
    }

    public function validateGroupAssignment($project_id, $group_id)
    {
        return $group_id === 0 || $this->db
            ->table(ProjectGroupRoleModel::TABLE)
            ->eq('project_id', $project_id)
            ->eq('group_id', $group_id)
            ->exists();
    }

    public function normalizeOtherAssignees($project_id, array $other_assignees)
    {
        $users = array();

        foreach ($other_assignees as $user_id) {
            if ($user_id === '' || $user_id === null || $user_id === 0 || $user_id === '0') {
                continue;
            }

            $user_id = $this->normalizeUserId($user_id);
            if ($user_id === false || ! $this->projectPermissionModel->isAssignable($project_id, $user_id)) {
                return false;
            }

            $users[$user_id] = $user_id;
        }

        return array_values($users);
    }

    public function createMultiselect(array $user_ids)
    {
        if (empty($user_ids)) {
            return 0;
        }

        $ms_id = $this->multiselectModel->create();
        foreach ($user_ids as $user_id) {
            $this->multiselectMemberModel->addUser($ms_id, $user_id);
        }

        return $ms_id;
    }

    public function getMemberIds($ms_id)
    {
        if ((int) $ms_id <= 0) {
            return array();
        }

        $member_ids = array();
        foreach ($this->multiselectMemberModel->getMembers((int) $ms_id) as $member) {
            $member_ids[] = (int) $member['id'];
        }

        sort($member_ids);
        return $member_ids;
    }

    public function removeMultiselectIfUnused($ms_id, $replacement_ms_id = 0)
    {
        $ms_id = (int) $ms_id;
        $replacement_ms_id = (int) $replacement_ms_id;

        if ($ms_id <= 0 || $ms_id === $replacement_ms_id) {
            return;
        }

        if (! $this->db->table(TaskModel::TABLE)->eq('owner_ms', $ms_id)->exists()) {
            $this->multiselectModel->remove($ms_id);
        }
    }

    public function assignmentsChanged(array $task, array $values, array $new_member_ids = null)
    {
        if (array_key_exists('owner_gp', $values) && (int) $values['owner_gp'] !== (int) $task['owner_gp']) {
            return true;
        }

        if ($new_member_ids !== null) {
            $new_member_ids = array_map('intval', $new_member_ids);
            sort($new_member_ids);
            return $this->getMemberIds($task['owner_ms']) !== $new_member_ids;
        }

        return false;
    }

    public function assigneeChanged(array $task, array $values)
    {
        $this->multiselectMemberModel->assigneeChanged($task, $values);
    }
}
