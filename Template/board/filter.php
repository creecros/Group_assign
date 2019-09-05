<?php if (isset($grouplist) && !empty($grouplist)) : ?>
</div>
<div class="input-addon-item">
    <div class="dropdown">
        <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('Group filters') ?>"><i class="fa fa-users fa-fw"></i><i class="fa fa-caret-down"></i></a>
        <ul>
            <?php foreach ($grouplist as $group) : ?>
            <li><a href="#" class="filter-helper" data-unique-filter='allassignees:"<?= $this->text->e($group) ?>"'><?= $this->text->e($group) ?></a></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>