<?php

namespace Kanboard\Plugin\group_assign\Schema;

use PDO;

const VERSION = 3;

function version_3(PDO $pdo)
{
    $pdo->exec('ALTER TABLE multiselect CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('ALTER TABLE multiselect_has_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

function version_2(PDO $pdo)
{
    $pdo->exec("ALTER TABLE `tasks` ADD COLUMN `owner_ms` INT DEFAULT '0'");

    $pdo->exec("
        CREATE TABLE `multiselect` (
            id INT NOT NULL AUTO_INCREMENT,
            external_id VARCHAR(255) DEFAULT '',
            PRIMARY KEY(id)
        ) ENGINE=InnoDB CHARSET=utf8
    ");
    $pdo->exec("
        CREATE TABLE multiselect_has_users (
            group_id INT NOT NULL,
            user_id INT NOT NULL,
            FOREIGN KEY(group_id) REFERENCES `multiselect`(id) ON DELETE CASCADE,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(group_id, user_id)
        ) ENGINE=InnoDB CHARSET=utf8
    ");
}

function version_1(PDO $pdo)
{
    $pdo->exec("ALTER TABLE `tasks` ADD COLUMN `owner_gp` INT DEFAULT '0'");
}
