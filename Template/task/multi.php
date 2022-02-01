                    <?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
                    <li>
                        <strong><?= t('Other Assignees:') ?></strong>
                    </li>
                    <?= $this->helper->sizeAvatarHelperExtend->sizeMultiple($task['owner_ms'], 'avatar-inline', 20) ?>
                    <?php endif ?>
