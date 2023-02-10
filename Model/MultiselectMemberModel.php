<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Model\UserModel;
use Kanboard\Model\TaskModel;
use Kanboard\Core\Queue\QueueManager;
use Kanboard\Core\Base;

/**
 * Multiselect Member Model
 *
 * @package  Kanboard\Plugin\Group_assign
 * @author   Craig Crosby
 */
class MultiselectMemberModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    public const TABLE = 'multiselect_has_users';

    /**
     * Get query to fetch all users
     *
     * @access public
     * @param  integer $group_id
     * @return \PicoDb\Table
     */
    public function getQuery($group_id)
    {
        return $this->db->table(self::TABLE)
            ->join(UserModel::TABLE, 'id', 'user_id')
            ->eq('group_id', $group_id);
    }

    /**
     * Get all users
     *
     * @access public
     * @param  integer $group_id
     * @return array
     */
    public function getMembers($group_id)
    {
        return $this->getQuery($group_id)->findAll();
    }

    /**
     * Get all not members
     *
     * @access public
     * @param  integer $group_id
     * @return array
     */
    public function getNotMembers($group_id)
    {
        $subquery = $this->db->table(self::TABLE)
            ->columns('user_id')
            ->eq('group_id', $group_id);

        return $this->db->table(UserModel::TABLE)
            ->notInSubquery('id', $subquery)
            ->eq('is_active', 1)
            ->findAll();
    }

    /**
     * Add user to a group
     *
     * @access public
     * @param  integer $group_id
     * @param  integer $user_id
     * @return boolean
     */
    public function addUser($group_id, $user_id)
    {
        return $this->db->table(self::TABLE)->insert(array(
            'group_id' => $group_id,
            'user_id' => $user_id,
        ));
    }

    /**
     * Remove user from a group
     *
     * @access public
     * @param  integer $group_id
     * @param  integer $user_id
     * @return boolean
     */
    public function removeUser($group_id, $user_id)
    {
        return $this->db->table(self::TABLE)
            ->eq('group_id', $group_id)
            ->eq('user_id', $user_id)
            ->remove();
    }

    /**
     * Remove all users from a group
     *
     * @access public
     * @param  integer $group_id
     * @param  integer $user_id
     * @return boolean
     */
    public function removeAllUsers($group_id)
    {
        return $this->db->table(self::TABLE)
            ->eq('group_id', $group_id)
            ->remove();
    }

    /**
     * Check if a user is member
     *
     * @access public
     * @param  integer $group_id
     * @param  integer $user_id
     * @return boolean
     */
    public function isMember($group_id, $user_id)
    {
        return $this->db->table(self::TABLE)
            ->eq('group_id', $group_id)
            ->eq('user_id', $user_id)
            ->exists();
    }

    /**
     * Get all groups for a given user
     *
     * @access public
     * @param  integer $user_id
     * @return array
     */
    public function getGroups($user_id)
    {
        return $this->db->table(self::TABLE)
            ->columns(MultiselectModel::TABLE.'.id', MultiselectModel::TABLE.'.external_id')
            ->join(MultiselectModel::TABLE, 'id', 'group_id')
            ->eq(self::TABLE.'.user_id', $user_id)
            ->asc(MultiselectModel::TABLE.'.id')
            ->findAll();
    }

    /**
     * Fire Assignee Change
     *
     * @access protected
     * @param  array $task
     * @param  array $changes
     */
    public function assigneeChanged(array $task, array $changes)
    {
        $events = array();
        $events[] = TaskModel::EVENT_ASSIGNEE_CHANGE;

        if (! empty($events)) {
            $this->queueManager->push(
                $this->taskEventJob
                ->withParams($task['id'], $events, $changes, array(), $task)
            );
        }
    }
}
