        <?php if ($this->text->contains($param_name, 'group_id')): ?>
            <?= $groups = $this->projectGroupRoleModel->getGroups('project_id') ?>
            <?= $groupnames = array() ?>
            <?= $groupids = array() ?>
            <?= $groupids[] = 0 ?>
            <?= $groupnames[] = t('Unassigned') ?>
            <?php foreach ($groups as $group): ?>
                 <?= $groupnames[] = $group['name'] ?>
                 <?= $groupids[] = $group['id'] ?>
            <?php endforeach ?>
            <?= $groupvalues = array_combine($groupids, $groupnames) ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $groupvalues, $values) ?>
        <?php endif ?>
    <?php endforeach ?>
