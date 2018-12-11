<span>
<?php if ($task['assigned_groupname']): ?>
     <strong><?= t('Assigned Group:') ?></strong>
     <?= $this->text->e($task['assigned_groupname'] ?: $task['owner_gp']) ?>
<?php endif ?>
</span>
<br>
