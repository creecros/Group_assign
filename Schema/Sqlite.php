<?php

namespace Kanboard\Plugin\group_assign\Schema;

use PDO;

const VERSION = 1;

function version_1(PDO $pdo)
{
    $pdo->exec("ALTER TABLE tasks ADD COLUMN owner_gp INTEGER DEFAULT '0'");
}
