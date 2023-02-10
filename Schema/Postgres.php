<?php

namespace Kanboard\Plugin\group_assign\Schema;

use PDO;

const VERSION = 2;

function version_2(PDO $pdo)
{
    $pdo->exec("ALTER TABLE tasks ADD COLUMN owner_ms INT DEFAULT '0'");

    $pdo->exec("
        CREATE TABLE multiselect (
            id SERIAL PRIMARY KEY,
            external_id VARCHAR(255) DEFAULT ''
        )
    ");

    $pdo->exec("
        CREATE TABLE multiselect_has_users (
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            FOREIGN KEY(group_id) REFERENCES multiselect(id) ON DELETE CASCADE,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(group_id, user_id)
        )
    ");
}

function version_1(PDO $pdo)
{
    $pdo->exec("ALTER TABLE tasks ADD COLUMN owner_gp INT DEFAULT '0'");
}
