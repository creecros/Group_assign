<?php

namespace Kanboard\Plugin\Group_assign\Filter;

use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Core\Filter\FilterInterface;
use Kanboard\Filter\BaseFilter;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;
use Kanboard\Model\GroupMemberModel;
use Kanboard\Model\GroupModel;
use PicoDb\Database;

class TaskAllAssigneeFilter extends BaseFilter implements FilterInterface
{
    /**
     * Database object
     *
     * @access private
     * @var Database
     */
    private $db;
    /**
     * Set database object
     *
     * @access public
     * @param  Database $db
     * @return TaskAssigneeFilter
     */
    public function setDatabase(Database $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Current user id
     *
     * @access private
     * @var int
     */
    private $currentUserId = 0;

    /**
     * Set current user id
     *
     * @access public
     * @param  integer $userId
     * @return TaskAssigneeFilter
     */
    public function setCurrentUserId($userId)
    {
        $this->currentUserId = $userId;
        return $this;
    }

    /**
     * Get search attribute
     *
     * @access public
     * @return string[]
     */
    public function getAttributes()
    {
        return array('allassignees');
    }

    /**
     * Apply filter
     *
     * @access public
     * @return string
     */
    public function apply()
    {
        if (is_int($this->value) || ctype_digit($this->value)) {
            $this->applyUserAssignmentConditions((int) $this->value);
        } else {
            switch ($this->value) {
                case 'me':
                    $this->applyUserAssignmentConditions((int) $this->currentUserId);
                    break;
                case 'nobody':
                    $this->query->eq(TaskModel::TABLE.'.owner_id', 0);
                    $this->query->eq(TaskModel::TABLE.'.owner_gp', 0);
                    $this->query->eq(TaskModel::TABLE.'.owner_ms', 0);
                    break;
                default:
                    $this->query->beginOr();
                    $this->query->ilike(UserModel::TABLE.'.username', '%'.$this->value.'%');
                    $this->query->ilike(UserModel::TABLE.'.name', '%'.$this->value.'%');
                    $this->query->inSubquery(TaskModel::TABLE.'.owner_gp', $this->getGroupByNameSubQuery());
                    $this->query->inSubquery(TaskModel::TABLE.'.owner_gp', $this->getGroupMemberByUserSearchSubQuery());
                    $this->query->inSubquery(TaskModel::TABLE.'.owner_ms', $this->getMultiselectMemberByUserSearchSubQuery());
                    $this->query->closeOr();
            }
        }
    }
    private function applyUserAssignmentConditions($user_id)
    {
        $this->query->beginOr();
        $this->query->eq(TaskModel::TABLE.'.owner_id', $user_id);
        $this->query->eq(TaskModel::TABLE.'.owner_gp', $user_id);
        $this->query->inSubquery(TaskModel::TABLE.'.owner_gp', $this->getGroupMemberByUserIdSubQuery($user_id));
        $this->query->inSubquery(TaskModel::TABLE.'.owner_ms', $this->getMultiselectMemberByUserIdSubQuery($user_id));
        $this->query->closeOr();
    }

    private function getGroupMemberByUserIdSubQuery($user_id)
    {
        return $this->db->table(GroupMemberModel::TABLE)
            ->columns('group_id')
            ->eq('user_id', $user_id);
    }

    private function getMultiselectMemberByUserIdSubQuery($user_id)
    {
        return $this->db->table(MultiselectMemberModel::TABLE)
            ->columns('group_id')
            ->eq('user_id', $user_id);
    }

    private function getGroupByNameSubQuery()
    {
        return $this->db->table(GroupModel::TABLE)
            ->columns('id')
            ->eq('name', $this->value);
    }

    private function getGroupMemberByUserSearchSubQuery()
    {
        return $this->db->table(GroupMemberModel::TABLE)
            ->columns('group_id')
            ->inSubquery('user_id', $this->getUserIdSubQuery());
    }

    private function getMultiselectMemberByUserSearchSubQuery()
    {
        return $this->db->table(MultiselectMemberModel::TABLE)
            ->columns('group_id')
            ->inSubquery('user_id', $this->getUserIdSubQuery());
    }

    private function getUserIdSubQuery()
    {
        return $this->db->table(UserModel::TABLE)
            ->columns(UserModel::TABLE.'.id')
            ->beginOr()
            ->ilike(UserModel::TABLE.'.username', '%'.$this->value.'%')
            ->ilike(UserModel::TABLE.'.name', '%'.$this->value.'%')
            ->closeOr();
    }
}
