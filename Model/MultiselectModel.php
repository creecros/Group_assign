<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Core\Base;

/**
 * Multiselect Model
 *
 * @package  Kanboard\Plugin\Group_assign
 * @author   Craig Crosby
 */
class MultiselectModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    public const TABLE = 'multiselect';

    /**
     * Get query to fetch all groups
     *
     * @access public
     * @return \PicoDb\Table
     */
    public function getQuery()
    {
        return $this->db->table(self::TABLE)
            ->columns('id', 'external_id')
            ->subquery('SELECT COUNT(*) FROM '.MultiselectMemberModel::TABLE.' WHERE group_id='.self::TABLE.'.id', 'nb_users');
    }

    /**
     * Get a specific group by id
     *
     * @access public
     * @param  integer $group_id
     * @return array
     */
    public function getById($group_id)
    {
        return $this->db->table(self::TABLE)->eq('id', $group_id)->findOne();
    }

    /**
     * Get a specific group by externalID
     *
     * @access public
     * @param  string $external_id
     * @return array
     */
    public function getByExternalId($external_id)
    {
        return $this->db->table(self::TABLE)->eq('external_id', $external_id)->findOne();
    }

    /**
     * Get specific groups by externalIDs
     *
     * @access public
     * @param  string[] $external_ids
     * @return array
     */
    public function getByExternalIds(array $external_ids)
    {
        if (empty($external_ids)) {
            return [];
        }

        return $this->db->table(self::TABLE)->in('external_id', $external_ids)->findAll();
    }

    /**
     * Get all groups
     *
     * @access public
     * @return array
     */
    public function getAll()
    {
        return $this->getQuery()->asc('id')->findAll();
    }

    /**
     * Remove a group
     *
     * @access public
     * @param  integer $group_id
     * @return boolean
     */
    public function remove($group_id)
    {
        return $this->db->table(self::TABLE)->eq('id', $group_id)->remove();
    }

    /**
     * Create a new group
     *
     * @access public
     * @param  string  $external_id
     * @return integer|boolean
     */
    public function create($external_id = '')
    {
        return $this->db->table(self::TABLE)->persist(array(
            'external_id' => $external_id,
        ));
    }

    /**
     * Update existing group
     *
     * @access public
     * @param  array $values
     * @return boolean
     */
    public function update(array $values)
    {
        return $this->db->table(self::TABLE)->eq('id', $values['id'])->update($values);
    }

    /**
     * Get groupId from externalGroupId and create the group if not found
     *
     * @access public
     * @param  string $name
     * @param  string $external_id
     * @return bool|integer
     */
    public function getOrCreateExternalGroupId($name, $external_id)
    {
        $group_id = $this->db->table(self::TABLE)->eq('external_id', $external_id)->findOneColumn('id');

        if (empty($group_id)) {
            $group_id = $this->create($external_id);
        }

        return $group_id;
    }
}
