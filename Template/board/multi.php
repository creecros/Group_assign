<?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
      <strong><?= t('Other Assignees:') ?></strong>
    <?= $this->helper->smallAvatarHelperExtend->smallMultiple($task['owner_ms'], 'avatar-inline') ?>
<?php endif ?>
