<?php

namespace Kanboard\Plugin\Group_assign\Action;

use Kanboard\Action\Base;
use Kanboard\Model\SubtaskModel;

class AssignSubtaskAssigneesToOtherAssignees extends Base
{
    /**
     * Get automatic action description.
     *
     * @return string
     */
    public function getDescription()
    {
        return t('Assign subtask assigness to other task assigness');
    }

    /**
     * Get the list of compatible events.
     *
     * @return array
     */
    public function getCompatibleEvents()
    {
        return [
            SubtaskModel::EVENT_CREATE_UPDATE,
        ];
    }

    /**
     * Get the required parameter for the action (defined by the user).
     *
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return [];
    }

    /**
     * Get the required parameter for the event.
     *
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return [
            'task' => [
                'id',
                'owner_ms',
            ],
            'subtask' => [
                'user_id',
            ],
        ];
    }

    /**
     * Execute the action (assign the given user).
     *
     * @param array $data Event data dictionary
     *
     * @return bool True if the action was executed or false when not executed
     */
    public function doAction(array $data)
    {
        if ($data['task']['owner_ms'] && $data['subtask']['user_id']) {
            return $this->multiselectMemberModel->addUser($data['task']['owner_ms'], $data['subtask']['user_id']);
        } elseif ($data['subtask']['user_id']) {
            $ms_id = $this->multiselectModel->create();
            $this->multiselectMemberModel->addUser($ms_id, $data['subtask']['user_id']);

            $values = [
                'id' => $data['task']['id'],
                'owner_ms' => $ms_id,
            ];

            return $this->taskModificationModel->update($values);
        }
    }

    /**
     * Check if the event data meet the action condition.
     *
     * @param array $data Event data dictionary
     *
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return true;
    }
}
