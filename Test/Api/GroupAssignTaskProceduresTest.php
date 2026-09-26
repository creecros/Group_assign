<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;

use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\Role;
use Kanboard\Model\GroupModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Plugin\Group_assign\Api\Procedure\GroupAssignTaskProcedures;

class GroupAssignTaskProceduresTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    public function testCreateReturnsFalseForInvalidProject()
    {
        $procedure = new GroupAssignTaskProcedures($this->container);

        $this->assertFalse($procedure->createTaskGroupAssign('Task', 0));
    }

    public function testCreateAndGetGroupAssignments()
    {
        $fixture = $this->createAssignmentFixture();
        $procedure = new GroupAssignTaskProcedures($this->container);

        $taskId = $this->createAssignedTask($procedure, $fixture);

        $this->assertSame(1, $taskId);
        $assignment = $procedure->getTaskGroupAssign($taskId);
        $this->assertSame($taskId, $assignment['task_id']);
        $this->assertSame($fixture['group_id'], $assignment['group_id']);
        $this->assertSame(array($fixture['user_id']), $assignment['other_assignee_ids']);
        $this->assertSame($fixture['user_id'], (int) $assignment['other_assignees'][0]['id']);
        $this->assertGreaterThan(0, $assignment['multiselect_id']);
    }

    public function testPatchPreservesOmittedAssignments()
    {
        $fixture = $this->createAssignmentFixture();
        $procedure = new GroupAssignTaskProcedures($this->container);
        $taskId = $this->createAssignedTask($procedure, $fixture);

        $this->assertTrue($procedure->patchTaskGroupAssign($taskId, $fixture['second_group_id']));
        $assignment = $procedure->getTaskGroupAssign($taskId);
        $oldMultiselectId = $assignment['multiselect_id'];
        $this->assertSame($fixture['second_group_id'], $assignment['group_id']);
        $this->assertSame(array($fixture['user_id']), $assignment['other_assignee_ids']);

        $this->assertTrue($procedure->patchTaskGroupAssign($taskId, null, array($fixture['second_user_id'])));
        $assignment = $procedure->getTaskGroupAssign($taskId);
        $this->assertSame($fixture['second_group_id'], $assignment['group_id']);
        $this->assertSame(array($fixture['second_user_id']), $assignment['other_assignee_ids']);
        $this->assertEmpty((new MultiselectModel($this->container))->getById($oldMultiselectId));

        $this->assertTrue($procedure->patchTaskGroupAssign($taskId, 0, array()));
        $assignment = $procedure->getTaskGroupAssign($taskId);
        $this->assertSame(0, $assignment['group_id']);
        $this->assertSame(array(), $assignment['other_assignee_ids']);
        $this->assertSame(0, $assignment['multiselect_id']);
    }

    public function testPatchRejectsInvalidAssignmentsWithoutChangingTask()
    {
        $fixture = $this->createAssignmentFixture();
        $procedure = new GroupAssignTaskProcedures($this->container);
        $taskId = $this->createAssignedTask($procedure, $fixture);

        $this->assertFalse($procedure->patchTaskGroupAssign($taskId, 999));
        $this->assertFalse($procedure->patchTaskGroupAssign($taskId, 'not-a-group'));
        $this->assertFalse($procedure->patchTaskGroupAssign($taskId, null, array(999)));
        $this->assertFalse($procedure->patchTaskGroupAssign($taskId, null, array($fixture['user_id'].'abc')));

        $assignment = $procedure->getTaskGroupAssign($taskId);
        $this->assertSame($fixture['group_id'], $assignment['group_id']);
        $this->assertSame(array($fixture['user_id']), $assignment['other_assignee_ids']);
    }

    public function testUpdateClearsOmittedAssignmentsAndPreservesNullOwner()
    {
        $fixture = $this->createAssignmentFixture();
        $procedure = new GroupAssignTaskProcedures($this->container);
        $taskId = $procedure->createTaskGroupAssign(
            'Task',
            $fixture['project_id'],
            '',
            0,
            $fixture['user_id'],
            0,
            '',
            '',
            0,
            0,
            null,
            0,
            0,
            0,
            0,
            0,
            0,
            '',
            array(),
            '',
            null,
            null,
            $fixture['group_id'],
            array($fixture['user_id'])
        );

        $this->assertTrue($procedure->updateTaskGroupAssign($taskId));
        $task = $this->container['taskFinderModel']->getById($taskId);
        $assignment = $procedure->getTaskGroupAssign($taskId);

        $this->assertSame($fixture['user_id'], (int) $task['owner_id']);
        $this->assertSame(0, $assignment['group_id']);
        $this->assertSame(array(), $assignment['other_assignee_ids']);
        $this->assertSame(0, $assignment['multiselect_id']);
    }

    public function testApiRejectsUnassignableGroupUsersOwnerAndCreator()
    {
        $fixture = $this->createAssignmentFixture();
        $procedure = new GroupAssignTaskProcedures($this->container);
        $unassignableUserId = (new UserModel($this->container))->create(array('username' => 'outsider'));
        $unassignableGroupId = (new GroupModel($this->container))->create('Outsider Group');

        $this->assertFalse($procedure->createTaskGroupAssign('Task', $fixture['project_id'], '', 0, $unassignableUserId));
        $this->assertFalse($procedure->createTaskGroupAssign('Task', $fixture['project_id'], '', 0, 0, $unassignableUserId));
        $this->assertFalse($procedure->createTaskGroupAssign('Task', $fixture['project_id'], '', 0, 0, 0, '', '', 0, 0, null, 0, 0, 0, 0, 0, 0, '', array(), '', null, null, $unassignableGroupId));
        $this->assertFalse($procedure->createTaskGroupAssign('Task', $fixture['project_id'], '', 0, 0, 0, '', '', 0, 0, null, 0, 0, 0, 0, 0, 0, '', array(), '', null, null, 0, array($unassignableUserId)));
    }

    private function createAssignedTask(GroupAssignTaskProcedures $procedure, array $fixture)
    {
        return $procedure->createTaskGroupAssign(
            'Task',
            $fixture['project_id'],
            '',
            0,
            0,
            0,
            '',
            '',
            0,
            0,
            null,
            0,
            0,
            0,
            0,
            0,
            0,
            '',
            array(),
            '',
            null,
            null,
            $fixture['group_id'],
            array($fixture['user_id'])
        );
    }

    private function createAssignmentFixture()
    {
        $projectModel = new ProjectModel($this->container);
        $userModel = new UserModel($this->container);
        $groupModel = new GroupModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $projectGroupRoleModel = new ProjectGroupRoleModel($this->container);

        $projectId = $projectModel->create(array('name' => 'Project'));
        $userId = $userModel->create(array('username' => 'user1'));
        $secondUserId = $userModel->create(array('username' => 'user2'));
        $groupId = $groupModel->create('Group 1');
        $secondGroupId = $groupModel->create('Group 2');

        $this->assertTrue($projectUserRoleModel->addUser($projectId, $userId, Role::PROJECT_MEMBER));
        $this->assertTrue($projectUserRoleModel->addUser($projectId, $secondUserId, Role::PROJECT_MEMBER));
        $this->assertTrue($projectGroupRoleModel->addGroup($projectId, $groupId, Role::PROJECT_MEMBER));
        $this->assertTrue($projectGroupRoleModel->addGroup($projectId, $secondGroupId, Role::PROJECT_MEMBER));

        return array(
            'project_id' => $projectId,
            'user_id' => $userId,
            'second_user_id' => $secondUserId,
            'group_id' => $groupId,
            'second_group_id' => $secondGroupId,
        );
    }
}
