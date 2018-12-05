                    <?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
                    <li>
                        <strong><?= t('Other Assignees:') ?></strong>
                    </li>
                    <?php foreach ($this->task->multiselectMemberModel->getMembers($task['owner_ms']) as $user) : ?>
                    <li>
                        <?= '- '. $user['user_id'] ?>
                    </li>
                    <?php end foreach ?>
                    <?php endif ?>
