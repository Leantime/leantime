<?php

namespace Unit\app\Domain\Comments\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Domain\Comments\Repositories\Comments;
use Unit\TestCase;

/**
 * getAllAccountComments() (behind comments.pollComments) must tie each comment to the entity of
 * its own module.
 *
 * moduleId points at a different table per module (ticket → zp_tickets, article/idea/
 * *canvasitem → zp_canvas_items, project → zp_projects). The query joined tickets and canvas
 * items on the id alone, so a to-do comment also matched the canvas item that shared its id and
 * came back under that item's project, including a project the user cannot access.
 *
 * Runs against a real in-memory SQLite database.
 */
class AccountCommentsTest extends TestCase
{
    private const USER_ID = 4;

    private const MY_PROJECT = 10;

    private const FOREIGN_PROJECT = 20;

    private SQLiteConnection $connection;

    private Comments $comments;

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => self::USER_ID, 'clientId' => 0, 'role' => 'editor']]);

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->connection->statement('CREATE TABLE zp_comment (
            id INTEGER PRIMARY KEY, module TEXT, text TEXT, date TEXT, moduleId INTEGER,
            userId INTEGER, commentParent INTEGER, status TEXT
        )');
        $this->connection->statement('CREATE TABLE zp_tickets (id INTEGER PRIMARY KEY, projectId INTEGER)');
        $this->connection->statement('CREATE TABLE zp_canvas (id INTEGER PRIMARY KEY, projectId INTEGER)');
        $this->connection->statement('CREATE TABLE zp_canvas_items (
            id INTEGER PRIMARY KEY, canvasId INTEGER, status TEXT, author INTEGER
        )');
        $this->connection->statement('CREATE TABLE zp_projects (
            id INTEGER PRIMARY KEY, psettings TEXT, clientId INTEGER
        )');
        $this->connection->statement('CREATE TABLE zp_relationuserproject (userId INTEGER, projectId INTEGER)');

        $this->connection->table('zp_projects')->insert([
            ['id' => self::MY_PROJECT, 'psettings' => 'restricted', 'clientId' => 0],
            ['id' => self::FOREIGN_PROJECT, 'psettings' => 'restricted', 'clientId' => 0],
        ]);
        $this->connection->table('zp_relationuserproject')->insert(['userId' => self::USER_ID, 'projectId' => self::MY_PROJECT]);

        // To-do 5 lives in the foreign project; canvas item 5 (same id) lives in mine.
        $this->connection->table('zp_tickets')->insert(['id' => 5, 'projectId' => self::FOREIGN_PROJECT]);
        $this->connection->table('zp_canvas')->insert(['id' => 1, 'projectId' => self::MY_PROJECT]);
        $this->connection->table('zp_canvas_items')->insert(['id' => 5, 'canvasId' => 1, 'status' => 'published', 'author' => 1]);

        $this->comments = (new \ReflectionClass(Comments::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Comments::class, 'db'))->setValue($this->comments, $this->connection);
    }

    private function addComment(int $id, string $module, int $moduleId): void
    {
        $this->connection->table('zp_comment')->insert([
            'id' => $id, 'module' => $module, 'text' => "comment $id", 'date' => '2026-11-15 10:00:00',
            'moduleId' => $moduleId, 'userId' => 1, 'commentParent' => 0, 'status' => '',
        ]);
    }

    /**
     * @return array<int, int|null> comment id => project id
     */
    private function visibleComments(?int $projectId = null): array
    {
        $rows = $this->comments->getAllAccountComments($projectId, null);

        return array_column($rows, 'projectId', 'id');
    }

    public function test_a_todo_comment_in_a_foreign_project_is_not_returned_through_a_canvas_item_with_the_same_id(): void
    {
        $this->addComment(1, 'ticket', 5);

        $this->assertSame([], $this->visibleComments());
        $this->assertSame([], $this->visibleComments(self::MY_PROJECT));
    }

    public function test_a_canvas_item_comment_is_returned_once_under_its_own_project(): void
    {
        $this->addComment(2, 'leancanvasitem', 5);

        $this->assertEquals([2 => self::MY_PROJECT], $this->visibleComments());
    }

    public function test_a_todo_comment_is_returned_under_its_own_project(): void
    {
        $this->connection->table('zp_tickets')->insert(['id' => 7, 'projectId' => self::MY_PROJECT]);
        $this->addComment(3, 'ticket', 7);

        $this->assertEquals([3 => self::MY_PROJECT], $this->visibleComments());
    }

    public function test_project_comments_resolve_to_the_project_itself(): void
    {
        $this->addComment(4, 'project', self::MY_PROJECT);
        $this->addComment(5, 'project', self::FOREIGN_PROJECT);

        $this->assertEquals([4 => self::MY_PROJECT], $this->visibleComments());
    }
}
