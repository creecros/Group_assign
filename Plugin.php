<?php

namespace Kanboard\Plugin\Subtaskdate;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Model\TaskModel;
//use Kanboard\Plugin\Subtaskdate\Filter\SubTaskDueDateFilter; //Needs work
use Kanboard\Model\SubtaskModel;
use PicoDb\Table;

class Plugin extends Base
{
    public function initialize()
    {
        $this->template->setTemplateOverride('header', 'theme:layout/header');

        //Model
        $this->hook->on('model:subtask:creation:prepare', array($this, 'beforeSave'));
        $this->hook->on('model:subtask:modification:prepare', array($this, 'beforeSave'));
        
        //Forms
        $this->template->hook->attach('template:subtask:form:create', 'Subtaskdate:subtask/form');
        $this->template->hook->attach('template:subtask:form:edit', 'Subtaskdate:subtask/form');
        
        //Task creatioh and modify
        $this->template->setTemplateOverride('task_creation/show', 'group_assign:task_creation/show');
        $this->template->setTemplateOverride('task_modification/show', 'group_assign:task_modification/show');
      
        //Board Tooltip
        $this->template->hook->attach('template:board:tooltip:subtasks:header:before-assignee', 'Subtaskdate:subtask/table_header');
        $this->template->hook->attach('template:board:tooltip:subtasks:rows', 'Subtaskdate:subtask/table_rows');
        
    }
    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }
    public function beforeSave(array &$values)
    {
        $values = $this->dateParser->convert($values, array('due_date'));
        $this->helper->model->resetFields($values, array('due_date'));
    }
    public function applyDateFilter(Table $query)
    {
        $query->lte(SubtaskModel::TABLE.'.due_date', time());
    }
    public function getPluginName()
    {
        return 'Subtaskdate';
    }
    public function getPluginDescription()
    {
        return t('Add a new due date field to subtasks');
    }
    public function getPluginAuthor()
    {
        return 'Manuel Raposo';
    }
    public function getPluginVersion()
    {
        return '1.0.1';
    }
    public function getPluginHomepage()
    {
        return 'https://github.com/eSkiSo/Subtaskdate';
    }
}
