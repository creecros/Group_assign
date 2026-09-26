<?php

namespace Kanboard\Plugin\Group_assign\Api\Procedure;

use Kanboard\Api\Authorization\ProjectAuthorization;
use Kanboard\Api\Authorization\TaskAuthorization;
use Kanboard\Api\Procedure\BaseProcedure;

/**
 * CreateTask with Group or Other Assignees API Procedure
 *
 * @package  Kanboard\Plugin\Group_assign
 * @author   Craig Crosby
 */
class GroupAssignTaskProcedures extends BaseProcedure
{
    public function createTaskGroupAssign(
        $title,
        $project_id,
        $color_id = '',
        $column_id = 0,
        $owner_id = 0,
        $creator_id = 0,
        $date_due = '',
        $description = '',
        $category_id = 0,
        $score = 0,
        $swimlane_id = null,
        $priority = 0,
        $recurrence_status = 0,
        $recurrence_trigger = 0,
        $recurrence_factor = 0,
        $recurrence_timeframe = 0,
        $recurrence_basedate = 0,
        $reference = '',
        array $tags = array(),
        $date_started = '',
        $time_spent = null,
        $time_estimated = null,
        $group_id = 0,
        array $other_assignees = array()
    )
    {
        $project_id = (int) $project_id;
        if ($project_id <= 0 || ! $this->projectExists($project_id)) {
            return false;
        }
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'createTaskGroupAssign', $project_id);

        $owner_id = (int) $owner_id;
        $creator_id = (int) $creator_id;
        $group_id = $this->groupAssignmentModel->normalizeGroupId($group_id);
        $other_assignees = $this->groupAssignmentModel->normalizeOtherAssignees($project_id, $other_assignees);
        if ($group_id === false || $other_assignees === false || ! $this->groupAssignmentModel->validateGroupAssignment($project_id, $group_id)) {
            return false;
        }

        if ($owner_id !== 0 && ! $this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        if ($creator_id !== 0 && ! $this->projectPermissionModel->isAssignable($project_id, $creator_id)) {
            return false;
        }

        if ($this->userSession->isLogged()) {
            $creator_id = $this->userSession->getId();
        }

        $values = array(
            'title' => $title,
            'project_id' => $project_id,
            'color_id' => $color_id,
            'column_id' => (int) $column_id,
            'owner_id' => $owner_id,
            'creator_id' => $creator_id,
            'date_due' => $date_due,
            'description' => $description,
            'category_id' => (int) $category_id,
            'score' => (int) $score,
            'swimlane_id' => $swimlane_id === null ? null : (int) $swimlane_id,
            'recurrence_status' => (int) $recurrence_status,
            'recurrence_trigger' => (int) $recurrence_trigger,
            'recurrence_factor' => (int) $recurrence_factor,
            'recurrence_timeframe' => (int) $recurrence_timeframe,
            'recurrence_basedate' => (int) $recurrence_basedate,
            'reference' => $reference,
            'priority' => (int) $priority,
            'tags' => $tags,
            'date_started' => $date_started,
            'time_spent' => $time_spent,
            'time_estimated' => $time_estimated,
            'owner_gp' => $group_id,
            'owner_ms' => 0,
        );

        list($valid, ) = $this->taskValidator->validateCreation($values);
        if (! $valid) {
            return false;
        }

        $values['owner_ms'] = $this->groupAssignmentModel->createMultiselect($other_assignees);
        $task_id = $this->taskCreationModel->create($values);
        if ($task_id === 0 && $values['owner_ms'] > 0) {
            $this->multiselectModel->remove($values['owner_ms']);
        }

        return $task_id;
    }

    public function updateTaskGroupAssign(
        $id,
        $title = null,
        $color_id = null,
        $owner_id = null,
        $date_due = null,
        $description = null,
        $category_id = null,
        $score = null,
        $priority = null,
        $recurrence_status = null,
        $recurrence_trigger = null,
        $recurrence_factor = null,
        $recurrence_timeframe = null,
        $recurrence_basedate = null,
        $reference = null,
        $tags = null,
        $date_started = null,
        $time_spent = null,
        $time_estimated = null,
        $group_id = 0,
        array $other_assignees = array()
    )
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'updateTaskGroupAssign', $id);
        $project_id = $this->taskFinderModel->getProjectId($id);

        if ($project_id === 0) {
            return false;
        }

        $owner_id = $owner_id === null ? null : (int) $owner_id;
        $group_id = $this->groupAssignmentModel->normalizeGroupId($group_id);
        $other_assignees = $this->groupAssignmentModel->normalizeOtherAssignees($project_id, $other_assignees);
        if ($group_id === false || $other_assignees === false || ! $this->groupAssignmentModel->validateGroupAssignment($project_id, $group_id)) {
            return false;
        }

        if ($owner_id !== null && $owner_id != 0 && ! $this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        $values = $this->filterValues(array(
            'id' => (int) $id,
            'title' => $title,
            'color_id' => $color_id,
            'owner_id' => $owner_id,
            'date_due' => $date_due,
            'description' => $description,
            'category_id' => $category_id === null ? null : (int) $category_id,
            'score' => $score === null ? null : (int) $score,
            'recurrence_status' => $recurrence_status === null ? null : (int) $recurrence_status,
            'recurrence_trigger' => $recurrence_trigger === null ? null : (int) $recurrence_trigger,
            'recurrence_factor' => $recurrence_factor === null ? null : (int) $recurrence_factor,
            'recurrence_timeframe' => $recurrence_timeframe === null ? null : (int) $recurrence_timeframe,
            'recurrence_basedate' => $recurrence_basedate === null ? null : (int) $recurrence_basedate,
            'reference' => $reference,
            'priority' => $priority === null ? null : (int) $priority,
            'tags' => $tags,
            'date_started' => $date_started,
            'time_spent' => $time_spent,
            'time_estimated' => $time_estimated,
            'owner_gp' => $group_id,
            'owner_ms' => 0,
        ));

        list($valid) = $this->taskValidator->validateApiModification($values);
        if (! $valid) {
            return false;
        }

        return $this->updateTaskWithOtherAssignees($values, $other_assignees);
    }

    public function getTaskGroupAssign($id)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'getTaskGroupAssign', $id);

        $task = $this->taskFinderModel->getById($id);
        if (empty($task)) {
            return false;
        }

        $otherAssignees = array();
        if ((int) $task['owner_ms'] > 0) {
            $otherAssignees = $this->filterPublicUsers($this->multiselectMemberModel->getMembers((int) $task['owner_ms']));
        }

        return array(
            'task_id' => (int) $id,
            'group_id' => (int) $task['owner_gp'],
            'other_assignee_ids' => array_map('intval', array_column($otherAssignees, 'id')),
            'other_assignees' => $otherAssignees,
            'multiselect_id' => (int) $task['owner_ms'],
        );
    }

    public function patchTaskGroupAssign($id, $group_id = null, $other_assignees = null)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'patchTaskGroupAssign', $id);
        $project_id = $this->taskFinderModel->getProjectId($id);

        if ($project_id === 0) {
            return false;
        }

        $values = array('id' => (int) $id);
        if ($group_id !== null) {
            $group_id = $this->groupAssignmentModel->normalizeGroupId($group_id);
            if ($group_id === false || ! $this->groupAssignmentModel->validateGroupAssignment($project_id, $group_id)) {
                return false;
            }
            $values['owner_gp'] = $group_id;
        }

        if ($other_assignees !== null) {
            if (! is_array($other_assignees)) {
                return false;
            }
            $other_assignees = $this->groupAssignmentModel->normalizeOtherAssignees($project_id, $other_assignees);
            if ($other_assignees === false) {
                return false;
            }
            $values['owner_ms'] = 0;
        }

        if (count($values) === 1) {
            return true;
        }

        list($valid) = $this->taskValidator->validateApiModification($values);
        if (! $valid) {
            return false;
        }

        return $this->updateTaskWithOtherAssignees($values, array_key_exists('owner_ms', $values) ? $other_assignees : null);
    }

    private function projectExists($project_id)
    {
        return $this->projectModel->getById($project_id) !== false;
    }


    private function filterPublicUsers(array $users)
    {
        return array_map(function (array $user) {
            return array_intersect_key($user, array_flip(array(
                'id',
                'username',
                'name',
                'email',
                'avatar_path',
                'is_active',
            )));
        }, $users);
    }


    private function updateTaskWithOtherAssignees(array $values, array $other_assignees = null)
    {
        $task = $this->taskFinderModel->getById($values['id']);
        if (empty($task)) {
            return false;
        }

        $previous_ms_id = (int) $task['owner_ms'];
        if ($other_assignees !== null) {
            $values['owner_ms'] = $this->groupAssignmentModel->createMultiselect($other_assignees);
        }

        $result = $this->taskModificationModel->update($values);

        if ($result) {
            $this->groupAssignmentModel->removeMultiselectIfUnused($previous_ms_id, isset($values['owner_ms']) ? $values['owner_ms'] : $previous_ms_id);
            if ($this->groupAssignmentModel->assignmentsChanged($task, $values, $other_assignees)) {
                $this->groupAssignmentModel->assigneeChanged($task, $values);
            }
        } elseif (isset($values['owner_ms']) && $values['owner_ms'] > 0) {
            $this->multiselectModel->remove($values['owner_ms']);
        }

        return $result;
    }


}
