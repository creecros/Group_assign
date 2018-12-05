                    <?php if ($task['owner_ms'] > 0 && count($this->task->multiselectMemberModel->getMembers($task['owner_ms'])) > 0) : ?>
                    <li>
                        <strong><?= t('Other Assignees:') ?></strong>
                    </li>
                    <?php foreach ($this->task->multiselectMemberModel->getMembers($task['owner_ms']) as $user) : ?>
                    <li><small>
                        <?php 
                            $userinfo = $this->task->userModel->getById($user['user_id']);
                            $username = $useerinfo['name'];
                        ?>
                        <?= '- '. $username ?>
                      </small></li>
                    <?php endforeach ?>
                    <?php endif ?>
