<?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
<strong><small><?= t('Other Assignees:') ?></small></strong>
    <?= $this->helper->smallAvatarHelperExtend->miniMultiple($task['owner_ms'], 'avatar-inline') ?>
<br>
<?php endif ?>
