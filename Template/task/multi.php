                    <?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
                    <li>
                        <strong><?= t('Other Assignees:') ?></strong>
                    </li>
                    <?php foreach ($this->task->multiselectMemberModel->getMembers($task['owner_ms']) as $user) : ?>
                        <?php 
                            $userinfo = $this->task->userModel->getById($user['user_id']);
                        ?>
                        <?= $this->helper->SmallAvatarHelperExtend->smallMultiple($task['owner_ms'], 'avatar-inline') ?>
                    <?php endforeach ?>
                    <?php endif ?>
