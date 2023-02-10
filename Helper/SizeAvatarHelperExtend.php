<?php

namespace Kanboard\Plugin\Group_assign\Helper;

use Kanboard\Helper\AvatarHelper;
use Kanboard\Core\Base;

/**
 * Avatar Helper
 *
 * @package helper
 * @author  Frederic Guillot
 */
class sizeAvatarHelperExtend extends AvatarHelper
{
    public function sizeMultiple($owner_ms, $css = '', $size = 20)
    {
        $assignees = $this->multiselectMemberModel->getMembers($owner_ms);
        $html = "";
        foreach ($assignees as $assignee) {
            $user = $this->userModel->getById($assignee['user_id']);
            $html .= $this->render($assignee['user_id'], $user['username'], $user['name'], $user['email'], $user['avatar_path'], $css, $size);
        }
        return $html;
    }
}
