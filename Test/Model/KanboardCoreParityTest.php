<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;

use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\Role;
use Kanboard\Model\ColumnModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\TaskDuplicationModel;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Group_assign\Model\GroupAssignTaskDuplicationModel;
use Kanboard\Plugin\Group_assign\Model\NewTaskFinderModel;

/**
 * Kanboard 1.2.58 calls TaskFinderModel::getUserICalQuery() and TaskDuplicationModel::getDestinationColumnId()
 * on the services this plugin replaces, and Kanboard 1.2.55 restricted the dashboard and overdue lists to the
 * active projects the user can access. The plugin's models must behave like core here.
 */
class KanboardCoreParityTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();
    }

    /**
     * Project 1: user is a member, task assigned to the user.
     * Project 2: user is NOT a member (removed), task still assigned to the user.
     * Project 3: user is a member, project disabled, task assigned to the user.
     */
    private function createProjectsAndTasks()
    {
        $projectModel = new ProjectModel($this->container);
        $userModel = new UserModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);

        $this->assertEquals(2, $userModel->create(array('username' => 'user1')));

        $this->assertEquals(1, $projectModel->create(array('name' => 'P1')));
        $this->assertEquals(2, $projectModel->create(array('name' => 'P2')));
        $this->assertEquals(3, $projectModel->create(array('name' => 'P3')));

        $this->assertTrue($projectUserRoleModel->addUser(1, 2, Role::PROJECT_MEMBER));
        $this->assertTrue($projectUserRoleModel->addUser(2, 2, Role::PROJECT_MEMBER));
        $this->assertTrue($projectUserRoleModel->addUser(3, 2, Role::PROJECT_MEMBER));

        $yesterday = time() - 86400;
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'T1', 'owner_id' => 2, 'date_due' => $yesterday)));
        $this->assertEquals(2, $taskCreationModel->create(array('project_id' => 2, 'title' => 'T2', 'owner_id' => 2, 'date_due' => $yesterday)));
        $this->assertEquals(3, $taskCreationModel->create(array('project_id' => 3, 'title' => 'T3', 'owner_id' => 2, 'date_due' => $yesterday)));

        // The user leaves project 2; project 3 is disabled
        $this->assertTrue($projectUserRoleModel->removeUser(2, 2));
        $this->assertTrue($projectModel->disable(3));
    }

    private function taskIds(array $tasks)
    {
        $ids = array_map('intval', array_column($tasks, 'id'));
        sort($ids);
        return $ids;
    }

    public function testPluginReplacesCoreServices()
    {
        $this->assertInstanceOf(NewTaskFinderModel::class, $this->container['taskFinderModel']);
        $this->assertInstanceOf(GroupAssignTaskDuplicationModel::class, $this->container['taskDuplicationModel']);
    }

    public function testGetUserICalQueryMatchesCore()
    {
        $this->createProjectsAndTasks();
        $core = new TaskFinderModel($this->container);
        $plugin = $this->container['taskFinderModel'];

        // Kanboard 1.2.58+: same result as core
        foreach (method_exists($core, 'getUserICalQuery') ? array(1, 2) : array() as $user_id) {
            $expected = $this->taskIds($core->getUserICalQuery($user_id)->findAll());
            $this->assertEquals($expected, $this->taskIds($plugin->getUserICalQuery($user_id)->findAll()), 'user '.$user_id);
        }

        // admin (user 1): all active projects; user 2: only project 1
        $this->assertEquals(array(1, 2), $this->taskIds($plugin->getUserICalQuery(1)->findAll()));
        $this->assertEquals(array(1), $this->taskIds($plugin->getUserICalQuery(2)->findAll()));
    }

    public function testDashboardAndOverdueListsOnlyShowAccessibleProjects()
    {
        $this->createProjectsAndTasks();
        $core = new TaskFinderModel($this->container);
        $plugin = $this->container['taskFinderModel'];

        $this->assertEquals($this->taskIds($core->getUserQuery(2)->findAll()), $this->taskIds($plugin->getUserQuery(2)->findAll()));
        $this->assertEquals(array(1), $this->taskIds($plugin->getUserQuery(2)->findAll()));

        $this->assertEquals($this->taskIds($core->getOverdueTasksByUser(2)), $this->taskIds($plugin->getOverdueTasksByUser(2)));
        $this->assertEquals(array(1), $this->taskIds($plugin->getOverdueTasksByUser(2)));
    }

    public function testGetDestinationColumnIdMatchesCore()
    {
        $projectModel = new ProjectModel($this->container);
        $columnModel = new ColumnModel($this->container);
        $core = new TaskDuplicationModel($this->container);
        $plugin = $this->container['taskDuplicationModel'];

        $this->assertEquals(1, $projectModel->create(array('name' => 'P1')));
        $this->assertEquals(2, $projectModel->create(array('name' => 'P2')));
        $this->assertNotFalse($columnModel->create(1, 'Only in P1'));

        $p1Ready = $columnModel->getColumnIdByTitle(1, 'Ready');
        $p2Ready = $columnModel->getColumnIdByTitle(2, 'Ready');
        $p1Only = $columnModel->getColumnIdByTitle(1, 'Only in P1');

        $this->assertEquals($p2Ready, $plugin->getDestinationColumnId(2, $p1Ready));
        $this->assertEquals($columnModel->getFirstColumnId(2), $plugin->getDestinationColumnId(2, $p1Only));

        // Kanboard 1.2.58+: same result as core
        foreach (method_exists($core, 'getDestinationColumnId') ? array($p1Ready, $p1Only) : array() as $column_id) {
            $this->assertEquals($core->getDestinationColumnId(2, $column_id), $plugin->getDestinationColumnId(2, $column_id));
        }
    }
}
