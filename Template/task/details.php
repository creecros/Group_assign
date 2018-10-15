                    <li>
                        <strong><?= t('Assigned Group:') ?></strong>
                        <span>
                        <?php if ($task['assigned_groupname']): ?>
                            <?= $this->text->e($task['assigned_groupname'] ?: $task['owner_gp']) ?>
                        <?php else: ?>
                            <?= t('not assigned') ?>
                        <?php endif ?>
                        </span>
                    </li>
