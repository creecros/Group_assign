<span>
<?php if ($task['assigned_groupname']): ?>
     <strong><?= t('Assigned Group:') ?></strong>
     <span class="assigned-group"><?= $this->text->e($task['assigned_groupname'] ?: $task['owner_gp']) ?></span>
     <br>
<?php endif ?>
</span>
