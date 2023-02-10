<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Plugin\Group_assign\Model\OldTaskFinderModel;

/**
 * New Task Finder model
 * Extends Group_assign Model
 *
 * @package  Kanboard\Plugin\Group_assign\Model
 */
class OldMetaMagikSubQuery extends OldTaskFinderModel
{
    public const METADATA_TABLE = 'task_has_metadata';
    /**
     * Extended query
     *
     * @access public
     * @return \PicoDb\Table
     */
    public function getExtendedQuery()
    {
        // add subquery to original Model, changing only what we want
        return parent::getExtendedQuery()
            ->subquery('(SELECT COUNT(*) FROM '.self::METADATA_TABLE.' WHERE task_id=tasks.id)', 'nb_metadata');
    }
}
