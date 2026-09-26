<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;

use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\Role;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;

class GroupAssignLifecycleTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    public function testDuplicateDoesNotCreateEmptyMultiselectContainer()
    {
        $projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        $taskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Task without other assignees',
            'owner_id' => 1,
        ));

        $duplicatedTaskId = $this->container['taskDuplicationModel']->duplicate($taskId);
        $duplicatedTask = $this->container['taskFinderModel']->getById($duplicatedTaskId);

        $this->assertSame(0, (int) $duplicatedTask['owner_ms']);

        $emptyMultiselectId = (new MultiselectModel($this->container))->create();
        $emptyTaskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Task with empty other-assignee container',
            'owner_id' => 1,
            'owner_ms' => $emptyMultiselectId,
        ));

        $duplicatedEmptyTaskId = $this->container['taskDuplicationModel']->duplicate($emptyTaskId);
        $duplicatedEmptyTask = $this->container['taskFinderModel']->getById($duplicatedEmptyTaskId);

        $this->assertSame(0, (int) $duplicatedEmptyTask['owner_ms']);
    }

    public function testMoveClearsMultiselectWhenAllMembersAreInvalidInDestinationProject()
    {
        $projectModel = new ProjectModel($this->container);
        $sourceProjectId = $projectModel->create(array('name' => 'Source Project'));
        $destinationProjectId = $projectModel->create(array('name' => 'Destination Project'));
        $userId = (new UserModel($this->container))->create(array('username' => 'source-only-user'));

        $this->assertTrue((new ProjectUserRoleModel($this->container))->addUser($sourceProjectId, $userId, Role::PROJECT_MEMBER));

        $multiselectId = (new MultiselectModel($this->container))->create();
        $this->assertTrue((new MultiselectMemberModel($this->container))->addUser($multiselectId, $userId));
        $taskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $sourceProjectId,
            'title' => 'Move task',
            'owner_id' => 1,
            'owner_ms' => $multiselectId,
        ));

        $this->assertTrue($this->container['taskProjectMoveModel']->moveToProject($taskId, $destinationProjectId));
        $task = $this->container['taskFinderModel']->getById($taskId);

        $this->assertSame(0, (int) $task['owner_ms']);
        $this->assertEmpty((new MultiselectModel($this->container))->getById($multiselectId));
    }
}
