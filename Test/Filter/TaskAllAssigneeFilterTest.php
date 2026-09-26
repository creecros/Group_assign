<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;

use Kanboard\Core\Plugin\Loader;
use Kanboard\Model\GroupMemberModel;
use Kanboard\Model\GroupModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Group_assign\Filter\TaskAllAssigneeFilter;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectModel;
use Kanboard\Plugin\Group_assign\Model\NewTaskFinderModel;

class TaskAllAssigneeFilterTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    public function testFilterMatchesQuotedGroupName()
    {
        $projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        $groupId = (new GroupModel($this->container))->create("O'Reilly");
        $taskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Group task',
            'owner_id' => 1,
            'owner_gp' => $groupId,
        ));

        $this->assertSame(array($taskId), $this->getFilteredTaskIds("O'Reilly"));
    }

    public function testFilterMatchesNumericGroupId()
    {
        $projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        (new GroupModel($this->container))->create('Other Group');
        $groupId = (new GroupModel($this->container))->create('Numeric Group');
        $taskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Numeric group task',
            'owner_id' => 1,
            'owner_gp' => $groupId,
        ));

        $this->assertSame(array($taskId), $this->getFilteredTaskIds((string) $groupId));
    }

    public function testFilterMatchesUserGroupAndMultiselectAssignments()
    {
        $projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        $userId = (new UserModel($this->container))->create(array(
            'username' => 'other-user',
            'name' => 'Other User',
        ));
        $groupId = (new GroupModel($this->container))->create('Group');
        $multiselectId = (new MultiselectModel($this->container))->create();

        $this->assertTrue((new GroupMemberModel($this->container))->addUser($groupId, $userId));
        $this->assertTrue((new MultiselectMemberModel($this->container))->addUser($multiselectId, $userId));

        $groupTaskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Group member task',
            'owner_id' => 1,
            'owner_gp' => $groupId,
        ));
        $multiselectTaskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Multiselect member task',
            'owner_id' => 1,
            'owner_ms' => $multiselectId,
        ));

        $this->assertSame(array($groupTaskId, $multiselectTaskId), $this->getFilteredTaskIds('Other User'));
    }

    public function testDashboardQueryMatchesUserGroupAndMultiselectAssignments()
    {
        $projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        $userId = (new UserModel($this->container))->create(array('username' => 'dashboard-user'));
        $groupId = (new GroupModel($this->container))->create('Group');
        $otherGroupId = (new GroupModel($this->container))->create('Other Group');
        $multiselectId = (new MultiselectModel($this->container))->create();
        $otherMultiselectId = (new MultiselectModel($this->container))->create();

        $this->assertTrue((new GroupMemberModel($this->container))->addUser($groupId, $userId));
        $this->assertTrue((new MultiselectMemberModel($this->container))->addUser($multiselectId, $userId));

        $groupTaskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Dashboard group task',
            'owner_id' => 1,
            'owner_gp' => $groupId,
            'owner_ms' => $otherMultiselectId,
        ));
        $multiselectTaskId = (new TaskCreationModel($this->container))->create(array(
            'project_id' => $projectId,
            'title' => 'Dashboard multiselect task',
            'owner_id' => 1,
            'owner_gp' => $otherGroupId,
            'owner_ms' => $multiselectId,
        ));

        $taskIds = array_map('intval', (new NewTaskFinderModel($this->container))->getUserQuery($userId)->findAllByColumn('id'));
        sort($taskIds);

        $this->assertSame(array($groupTaskId, $multiselectTaskId), $taskIds);
    }

    private function getFilteredTaskIds($value)
    {
        $db = $this->container['db'];
        $query = $db
            ->table(TaskModel::TABLE)
            ->columns(TaskModel::TABLE.'.id')
            ->join(UserModel::TABLE, 'id', 'owner_id', TaskModel::TABLE)
            ->asc(TaskModel::TABLE.'.id');

        $filter = new TaskAllAssigneeFilter($value);
        $filter->setDatabase($db);
        $filter->withQuery($query);
        $filter->apply();

        return array_map('intval', $query->findAllByColumn('id'));
    }
}
