<span>
<?php if ($task['assigned_groupname']): ?>
     <?= $this->text->e($task['assigned_groupname'] ?: $task['owner_gp']) ?>
<?php endif ?>
</span>
