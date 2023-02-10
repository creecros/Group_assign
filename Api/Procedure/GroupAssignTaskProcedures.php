<?php

namespace Kanboard\Plugin\Group_assign\Api\Procedure;

use Kanboard\Api\Authorization\SubtaskAuthorization;
use Kanboard\Api\Authorization\ProjectAuthorization;
use Kanboard\Api\Authorization\TaskAuthorization;
use Kanboard\Api\Procedure\BaseProcedure;
use Kanboard\Filter\TaskProjectFilter;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ProjectPermissionModel;
use Kanboard\Validator\TaskValidator;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;

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
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'createTaskGroupAssign', $project_id);

        if ($owner_id !== 0 && ! $this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        if ($this->userSession->isLogged()) {
            $creator_id = $this->userSession->getId();
        }

        if (!empty($other_assignees)) {
            $ms_id = $this->multiselectModel->create();
            foreach ($other_assignees as $user) {
                $this->multiselectMemberModel->addUser($ms_id, $user);
            }
        }


        $values = array(
            'title' => $title,
            'project_id' => $project_id,
            'color_id' => $color_id,
            'column_id' => $column_id,
            'owner_id' => $owner_id,
            'creator_id' => $creator_id,
            'date_due' => $date_due,
            'description' => $description,
            'category_id' => $category_id,
            'score' => $score,
            'swimlane_id' => $swimlane_id,
            'recurrence_status' => $recurrence_status,
            'recurrence_trigger' => $recurrence_trigger,
            'recurrence_factor' => $recurrence_factor,
            'recurrence_timeframe' => $recurrence_timeframe,
            'recurrence_basedate' => $recurrence_basedate,
            'reference' => $reference,
            'priority' => $priority,
            'tags' => $tags,
            'date_started' => $date_started,
            'time_spent' => $time_spent,
            'time_estimated' => $time_estimated,
            'owner_gp' => $group_id,
            'owner_ms' => (!empty($other_assignees)) ? $ms_id : 0,
        );

        list($valid, ) = $this->taskValidator->validateCreation($values);

        return $valid ? $this->taskCreationModel->create($values) : false;
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
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'updateTask', $id);
        $project_id = $this->taskFinderModel->getProjectId($id);

        if ($project_id === 0) {
            return false;
        }

        if ($owner_id !== null && $owner_id != 0 && ! $this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        if (!empty($other_assignees)) {
            $ms_id = $this->multiselectModel->create();
            foreach ($other_assignees as $user) {
                $this->multiselectMemberModel->addUser($ms_id, $user);
            }
        }

        $values = $this->filterValues(array(
            'id' => $id,
            'title' => $title,
            'color_id' => $color_id,
            'owner_id' => $owner_id,
            'date_due' => $date_due,
            'description' => $description,
            'category_id' => $category_id,
            'score' => $score,
            'recurrence_status' => $recurrence_status,
            'recurrence_trigger' => $recurrence_trigger,
            'recurrence_factor' => $recurrence_factor,
            'recurrence_timeframe' => $recurrence_timeframe,
            'recurrence_basedate' => $recurrence_basedate,
            'reference' => $reference,
            'priority' => $priority,
            'tags' => $tags,
            'date_started' => $date_started,
            'time_spent' => $time_spent,
            'time_estimated' => $time_estimated,
            'owner_gp' => $group_id,
            'owner_ms' => (!empty($other_assignees)) ? $ms_id : 0,
        ));

        list($valid) = $this->taskValidator->validateApiModification($values);
        return $valid && $this->taskModificationModel->update($values);
    }
}
