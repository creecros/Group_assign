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
class SmallAvatarHelperExtend extends AvatarHelper
{

    public function smallMultiple($owner_ms, $css = '') {
        $assignees = $this->multiselectMemberModel->getMembers($owner_ms);
        $html = "";
        foreach ($assignees as $assignee) {
            $user = $this->userModel->getById($assignee['user_id']);
            $html .= $this->render($assignee['user_id'], $user['username'], $user['name'], $user['email'], $user['avatar_path'], $css, 20);
        }
        return $html;
    }    
    
    public function miniMultiple($owner_ms, $css = '') {
        $assignees = $this->multiselectMemberModel->getMembers($owner_ms);
        $html = "";
        foreach ($assignees as $assignee) {
            $user = $this->userModel->getById($assignee['user_id']);
            $html .= $this->render($assignee['user_id'], $user['username'], $user['name'], $user['email'], $user['avatar_path'], $css, 13);
        }
        return $html;
    }
 }
