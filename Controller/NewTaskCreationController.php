<?php

namespace namespace Kanboard\Plugin\Group_assign\Controller;

use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Model\SwimlaneModel;
use Kanboard\Model\ColumnModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\CategoryModel;
use Kanboard\Core\Controller\PageNotFoundException;

/**
 * Task Creation Controller
 *
 * @package  Kanboard\Controller
 * @author   Frederic Guillot
 */
class NewTaskCreationController extends \Kanboard\Controller\TaskCreationController
{
    /**
     * Display a form to create a new task
     *
     * @access public
     * @param  array $values
     * @param  array $errors
     * @throws PageNotFoundException
     */
    public function show(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();
        $swimlanesList = $this->swimlaneModel->getList($project['id'], false, true);
        $values += $this->prepareValues($project['is_private'], $swimlanesList);

        $values = $this->hook->merge('controller:task:form:default', $values, array('default_values' => $values));
        $values = $this->hook->merge('controller:task-creation:form:default', $values, array('default_values' => $values));

        $this->response->html($this->template->render('task_creation/show', array(
            'project' => $project,
            'errors' => $errors,
            'values' => $values + array('project_id' => $project['id']),
            'columns_list' => $this->columnModel->getList($project['id']),
            'users_list' => $this->projectUserRoleModel->getAssignableUsersList($project['id'], true, false, $project['is_private'] == 1),
            'categories_list' => $this->categoryModel->getList($project['id']),
            'swimlanes_list' => $swimlanesList,
            'groups' => $this->projectGroupRoleModel->getGroups($project['id']),
        )));
    }

}
