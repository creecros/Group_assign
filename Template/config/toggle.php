<div class="panel">
    <?= $this->form->radio('enable_pm_group_management', 'Enable Group Managment for Project Managers' , 1, isset($values['enable_pm_group_management'])&& $values['enable_pm_group_management']==1) ?>
    <?= $this->form->radio('enable_pm_group_management', 'Disable Group Managment for Project Managers' , 2, isset($values['enable_pm_group_management'])&& $values['enable_pm_group_management']==2) ?>
</div>
