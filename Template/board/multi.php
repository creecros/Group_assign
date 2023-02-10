<?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>

<?php if ($this->user->userMetadataModel->exists($this->user->getid(), "boardcustomizer_compactlayout")) {
    /* compact card layout */
    ?>

    <strong class="assigned-other-label"><small><?= t('Other Assignees:') ?></small></strong>
    <?= $this->helper->sizeAvatarHelperExtend->sizeMultiple($task['owner_ms'], 'avatar-inline avatar-bdyn', $this->task->configModel->get('b_av_size', '20')) ?>

<?php
} else {
    ?>

    <strong class="assigned-other-label"><small><?= t('Other Assignees:') ?></small></strong>
    <?= $this->helper->sizeAvatarHelperExtend->sizeMultiple($task['owner_ms'], 'avatar-inline avatar-bdyn', 13) ?>
    <br>

<?php
}
?>

<?php endif ?>
