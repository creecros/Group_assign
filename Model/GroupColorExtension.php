<?php

namespace Kanboard\Plugin\Group_assign\Model;

use Kanboard\Model\GroupModel;

class GroupColorExtension extends GroupModel
{

  public function getGroupColor($str) {
    $code = dechex(crc32($str));
    $code = substr($code, 0, 6);
    return $code;
  }
}
