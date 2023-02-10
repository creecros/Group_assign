<?php

namespace Kanboard\Plugin\Group_assign\Model;

use DateTime;
use Kanboard\Model\GroupMemberModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Model\TimezoneModel;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\ColorModel;
use Kanboard\Core\Base;

/**
 * Group_assign Calendar Model
 *
 * @package  Kanboard\Plugin\Group_assign
 * @author   Craig Crosby
 */
class GroupAssignCalendarModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    public const TABLE = 'tasks';
    /**
     * Get query to fetch all users
     *
     * @access public
     * @param  integer $group_id
     * @return \PicoDb\Table
     */
    public function getUserCalendarEvents($user_id, $start, $end)
    {
        $getMS_Ids = $this->db->table(MultiselectMemberModel::TABLE)
            ->eq('user_id', $user_id)
            ->findAllByColumn('group_id');

        $getGr_Ids = $this->db->table(GroupMemberModel::TABLE)
            ->eq('user_id', $user_id)
            ->findAllByColumn('group_id');

        $tasks = $this->db->table(self::TABLE)
           ->beginOr()
           ->in('owner_gp', $getGr_Ids)
           ->in('owner_ms', $getMS_Ids)
           ->closeOr()
           ->gte('date_due', strtotime($start))
           ->lte('date_due', strtotime($end))
           ->neq('is_active', 0)
           ->findAll();

        $events = array();

        foreach ($tasks as $task) {
            $startDate = new DateTime();
            $startDate->setTimestamp($task['date_started']);

            $endDate = new DateTime();
            $endDate->setTimestamp($task['date_due']);

            if ($startDate == 0) {
                $startDate = $endDate;
            }

            $allDay = $startDate == $endDate && $endDate->format('Hi') == '0000';
            $format = $allDay ? 'Y-m-d' : 'Y-m-d\TH:i:s';

            $events[] = array(
                'timezoneParam' => $this->timezoneModel->getCurrentTimezone(),
                'id' => $task['id'],
                'title' => t('#%d', $task['id']).' '.$task['title'],
                'backgroundColor' => $this->colorModel->getBackgroundColor('dark_grey'),
                'borderColor' => $this->colorModel->getBorderColor($task['color_id']),
                'textColor' => 'white',
                'url' => $this->helper->url->to('TaskViewController', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id'])),
                'start' => $startDate->format($format),
                'end' => $endDate->format($format),
                'editable' => $allDay,
                'allday' => $allDay,
            );
        }

        return $events;
    }
}
