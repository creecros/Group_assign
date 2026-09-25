<div class="panel">
    <?= $this->form->radio('enable_am_group_management', t('Enable group management for application managers'), 1, isset($values['enable_am_group_management'])&& $values['enable_am_group_management']==1) ?>
    <?= $this->form->radio('enable_am_group_management', t('Disable group management for application managers'), 2, isset($values['enable_am_group_management'])&& $values['enable_am_group_management']==2) ?>
</div>
