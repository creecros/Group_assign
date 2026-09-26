<?php

namespace Kanboard\Plugin\Group_assign\Controller;

use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Model\SwimlaneModel;
use Kanboard\Model\ColumnModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\CategoryModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\TaskProjectDuplicationModel;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\ColorModel;
use Kanboard\Controller\BaseController;
use Kanboard\Core\Controller\PageNotFoundException;

/**
 * Group Assign Task Modification controller
 *
 * @package  Kanboard\Plugin\Group_assign\
 * @author   Craig Crosby
 */
class GroupAssignTaskModificationController extends BaseController
{
    public function assignToMe()
    {
        $task = $this->getTask();
        $values = ['id' => $task['id'], 'owner_id' => $this->userSession->getId()];

        if (! $this->helper->projectRole->canUpdateTask($task)) {
            throw new AccessForbiddenException(t('You are not allowed to update tasks assigned to someone else.'));
        }

        $this->taskModificationModel->update($values);
        $this->redirectAfterQuickAction($task);
    }

    /**
     * Set the start date automatically
     *
     * @access public
     */
    public function start()
    {
        $task = $this->getTask();
        $values = ['id' => $task['id'], 'date_started' => time()];

        if (! $this->helper->projectRole->canUpdateTask($task)) {
            throw new AccessForbiddenException(t('You are not allowed to update tasks assigned to someone else.'));
        }

        $this->taskModificationModel->update($values);
        $this->redirectAfterQuickAction($task);
    }

    protected function redirectAfterQuickAction(array $task)
    {
        switch ($this->request->getStringParam('redirect')) {
            case 'board':
                $this->response->redirect($this->helper->url->to('BoardViewController', 'show', ['project_id' => $task['project_id']]));
                break;
            case 'list':
                $this->response->redirect($this->helper->url->to('TaskListController', 'show', ['project_id' => $task['project_id']]));
                break;
            case 'dashboard':
                $this->response->redirect($this->helper->url->to('DashboardController', 'show', [], 'project-tasks-'.$task['project_id']));
                break;
            case 'dashboard-tasks':
                $this->response->redirect($this->helper->url->to('DashboardController', 'tasks', ['user_id' => $this->userSession->getId()]));
                break;
            default:
                $this->response->redirect($this->helper->url->to('TaskViewController', 'show', ['project_id' => $task['project_id'], 'task_id' => $task['id']]));
        }
    }

    /**
     * Display a form to edit a task
     *
     * @access public
     * @param array $values
     * @param array $errors
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @throws \Kanboard\Core\Controller\PageNotFoundException
     */
    public function edit(array $values = array(), array $errors = array())
    {
        $task = $this->getTask();

        if (! $this->helper->projectRole->canUpdateTask($task)) {
            throw new AccessForbiddenException(t('You are not allowed to update tasks assigned to someone else.'));
        }

        $project = $this->projectModel->getById($task['project_id']);

        if (empty($values)) {
            $values = $task;
        }

        $values = $this->hook->merge('controller:task:form:default', $values, array('default_values' => $values));
        $values = $this->hook->merge('controller:task-modification:form:default', $values, array('default_values' => $values));

        $params = array(
            'project' => $project,
            'values' => $values,
            'errors' => $errors,
            'task' => $task,
            'tags' => $this->taskTagModel->getList($task['id']),
            'users_list' => $this->projectUserRoleModel->getAssignableUsersList($task['project_id']),
            'categories_list' => $this->categoryModel->getList($task['project_id']),
        );

        $this->renderTemplate($task, $params);
    }

    protected function renderTemplate(array &$task, array &$params)
    {
        if (empty($task['external_uri'])) {
            $this->response->html($this->template->render('task_modification/show', $params));
        } else {
            try {
                $taskProvider = $this->externalTaskManager->getProvider($task['external_provider']);
                $params['template'] = $taskProvider->getModificationFormTemplate();
                $params['external_task'] = $taskProvider->fetch($task['external_uri']);
            } catch (ExternalTaskAccessForbiddenException $e) {
                throw new AccessForbiddenException($e->getMessage());
            } catch (ExternalTaskException $e) {
                $params['error_message'] = $e->getMessage();
            }

            $this->response->html($this->template->render('external_task_modification/show', $params));
        }
    }

    /**
     * Validate and update a task
     *
     * @access public
     */
    public function update()
    {
        $task = $this->getTask();
        $values = $this->request->getValues();
        $values['id'] = $task['id'];
        $values['project_id'] = $task['project_id'];

        $other_assignees = array();
        $assignment_errors = array();
        $new_ms_id = 0;
        $assignments_processed = false;

        if ($this->helper->projectRole->canChangeAssignee($task)) {
            $assignments_valid = $this->prepareGroupAssignmentValues($task['project_id'], $values, $other_assignees, $assignment_errors);
            $assignments_processed = true;
        } else {
            unset($values['owner_gp']);
            unset($values['owner_ms']);
            $assignments_valid = true;
        }

        list($valid, $errors) = $this->taskValidator->validateModification($values);
        $errors = array_merge($errors, $assignment_errors);
        $valid = $valid && $assignments_valid;

        if ($valid && $assignments_processed) {
            $new_ms_id = $this->groupAssignmentModel->createMultiselect($other_assignees);
            $values['owner_ms'] = $new_ms_id;
        }

        $updated = $valid && $this->updateTask($task, $values, $errors);

        if ($updated) {
            if ($assignments_processed) {
                $this->groupAssignmentModel->removeMultiselectIfUnused((int) $task['owner_ms'], $new_ms_id);
            }

            if ($this->groupAssignmentModel->assignmentsChanged($task, $values, $assignments_processed ? $other_assignees : null)) {
                $this->groupAssignmentModel->assigneeChanged($task, $values);
            }

            $this->flash->success(t('Task updated successfully.'));
            $this->response->redirect($this->helper->url->to('TaskViewController', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])), true);
        } else {
            if ($new_ms_id > 0 && (int) $this->db->table(TaskModel::TABLE)->eq('id', $task['id'])->findOneColumn('owner_ms') !== $new_ms_id) {
                $this->multiselectModel->remove($new_ms_id);
            }

            $this->flash->failure(t('Unable to update your task.'));
            $this->edit($values, $errors);
        }
    }

    private function prepareGroupAssignmentValues($project_id, array &$values, array &$other_assignees, array &$errors)
    {
        if (array_key_exists('owner_gp', $values)) {
            $group_id = $this->groupAssignmentModel->normalizeGroupId($values['owner_gp']);
            if ($group_id === false || ! $this->groupAssignmentModel->validateGroupAssignment($project_id, $group_id)) {
                $errors['owner_gp'] = array(t('The assigned group is not allowed in this project.'));
            } else {
                $values['owner_gp'] = $group_id;
            }
        }

        $raw_other_assignees = isset($values['owner_ms']) ? $values['owner_ms'] : array();
        if (! is_array($raw_other_assignees)) {
            $errors['owner_ms'] = array(t('The other assignees are not allowed in this project.'));
        } else {
            $other_assignees = $this->groupAssignmentModel->normalizeOtherAssignees($project_id, $raw_other_assignees);
            if ($other_assignees === false) {
                $errors['owner_ms'] = array(t('The other assignees are not allowed in this project.'));
                $other_assignees = array();
            }
        }

        $values['owner_ms'] = 0;

        return empty($errors);
    }

    protected function updateTask(array &$task, array &$values, array &$errors)
    {
        if (isset($values['owner_id']) && $values['owner_id'] != $task['owner_id'] && !$this->helper->projectRole->canChangeAssignee($task)) {
            throw new AccessForbiddenException(t('You are not allowed to change the assignee.'));
        }

        if (! $this->helper->projectRole->canUpdateTask($task)) {
            throw new AccessForbiddenException(t('You are not allowed to update tasks assigned to someone else.'));
        }

        $result = $this->taskModificationModel->update($values);

        if ($result && ! empty($task['external_uri'])) {
            try {
                $taskProvider = $this->externalTaskManager->getProvider($task['external_provider']);
                $result = $taskProvider->save($task['external_uri'], $values, $errors);
            } catch (ExternalTaskAccessForbiddenException $e) {
                throw new AccessForbiddenException($e->getMessage());
            } catch (ExternalTaskException $e) {
                $this->logger->error($e->getMessage());
                $result = false;
            }
        }

        return $result;
    }
}
