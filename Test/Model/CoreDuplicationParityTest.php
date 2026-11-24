<?php

require_once 'tests/units/Base.php';
use KanboardTests\units\Base;

use Kanboard\Core\Plugin\Loader;
use Kanboard\Core\Security\Role;
use Kanboard\Model\GroupModel;
use Kanboard\Model\ProjectGroupRoleModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\TaskExternalLinkModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Group_assign\Model\MultiselectMemberModel;

/**
 * The plugin replaces Kanboard's taskDuplicationModel and taskRecurrenceModel with its own copies.
 * These tests pin the behaviour of Kanboard core (1.2.54 and later: external links copied, duplication hook
 * fired) together with the plugin's own (Assigned Group and Other Assignees carried over).
 */
class CoreDuplicationParityTest extends Base
{
    private $projectId;
    private $groupId;
    private $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $plugin = new Loader($this->container);
        $plugin->scan();

        $this->projectId = (new ProjectModel($this->container))->create(array('name' => 'Project'));
        $this->groupId = (new GroupModel($this->container))->create('Team');
        $this->assertTrue((new ProjectGroupRoleModel($this->container))->addGroup($this->projectId, $this->groupId, Role::PROJECT_MEMBER));
        $this->userId = (new UserModel($this->container))->create(array('username' => 'other-assignee'));
        $this->assertTrue((new ProjectUserRoleModel($this->container))->addUser($this->projectId, $this->userId, Role::PROJECT_MEMBER));
    }

    private function createTaskWithAssignmentsAndLink(array $extra = array())
    {
        $msId = $this->container['groupAssignmentModel']->createMultiselect(array($this->userId));
        $taskId = (new TaskCreationModel($this->container))->create(array_merge(array(
            'project_id' => $this->projectId,
            'title' => 'Source',
            'owner_id' => 1,
            'owner_gp' => $this->groupId,
            'owner_ms' => $msId,
        ), $extra));
        $this->assertGreaterThan(0, $taskId);

        $this->assertNotFalse((new TaskExternalLinkModel($this->container))->create(array(
            'task_id' => $taskId,
            'creator_id' => 1,
            'dependency' => 'related',
            'title' => 'Spec',
            'link_type' => 'weblink',
            'url' => 'https://example.org/spec',
        )));

        return array($taskId, $msId);
    }

    private function assertCopiedAssignmentsAndLink($sourceMsId, $copyId)
    {
        $copy = $this->container['taskFinderModel']->getById($copyId);
        $this->assertSame($this->groupId, (int) $copy['owner_gp']);
        $this->assertGreaterThan(0, (int) $copy['owner_ms']);
        $this->assertNotSame($sourceMsId, (int) $copy['owner_ms'], 'the copy must get its own Other Assignees container');
        $this->assertSame(array($this->userId), $this->container['groupAssignmentModel']->getMemberIds($copy['owner_ms']));

        $links = (new TaskExternalLinkModel($this->container))->getAll($copyId);
        $this->assertCount(1, $links);
        $this->assertSame('https://example.org/spec', $links[0]['url']);
        $this->assertSame('Spec', $links[0]['title']);
        $this->assertSame('related', $links[0]['dependency']);
        $this->assertSame('weblink', $links[0]['link_type']);
    }

    public function testDuplicateCopiesExternalLinksAndAssignmentsAndFiresHook()
    {
        list($taskId, $msId) = $this->createTaskWithAssignmentsAndLink();

        $seen = array();
        $this->container['hook']->on('model:task:duplication:aftersave', function ($values) use (&$seen) {
            $seen[] = $values;
        });

        $copyId = $this->container['taskDuplicationModel']->duplicate($taskId);
        $this->assertGreaterThan($taskId, $copyId);
        $this->assertCopiedAssignmentsAndLink($msId, $copyId);
        $this->assertSame(array(array('source_task_id' => $taskId, 'destination_task_id' => $copyId)), $seen);
    }

    public function testRecurringTaskCopiesExternalLinksAndAssignments()
    {
        list($taskId, $msId) = $this->createTaskWithAssignmentsAndLink(array(
            'date_due' => time(),
            'recurrence_status' => TaskModel::RECURRING_STATUS_PENDING,
            'recurrence_trigger' => TaskModel::RECURRING_TRIGGER_CLOSE,
            'recurrence_factor' => 1,
            'recurrence_timeframe' => TaskModel::RECURRING_TIMEFRAME_DAYS,
            'recurrence_basedate' => TaskModel::RECURRING_BASEDATE_TRIGGERDATE,
        ));

        $this->assertInstanceOf('Kanboard\Plugin\Group_assign\Model\TaskRecurrenceModel', $this->container['taskRecurrenceModel']);
        $childId = $this->container['taskRecurrenceModel']->duplicateRecurringTask($taskId);
        $this->assertGreaterThan($taskId, $childId);
        $this->assertCopiedAssignmentsAndLink($msId, $childId);

        $parent = $this->container['taskFinderModel']->getById($taskId);
        $this->assertEquals(TaskModel::RECURRING_STATUS_PROCESSED, $parent['recurrence_status']);
        $this->assertEquals($childId, $parent['recurrence_child']);
    }

    public function testMoveModelOverrideIsActive()
    {
        $this->assertInstanceOf('Kanboard\Plugin\Group_assign\Model\TaskProjectMoveModel', $this->container['taskProjectMoveModel']);
    }
}
