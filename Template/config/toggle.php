<div class="panel">
    <?= $this->form->radio('enable_am_group_management', t('Enable Group Managment for Application Managers'), 1, isset($values['enable_am_group_management'])&& $values['enable_am_group_management']==1) ?>
    <?= $this->form->radio('enable_am_group_management', t('Disable Group Managment for Application Managers'), 2, isset($values['enable_am_group_management'])&& $values['enable_am_group_management']==2) ?>
</div>
