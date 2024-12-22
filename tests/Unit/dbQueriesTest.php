<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../src/DB/queries.php';

class DBQueriesTest extends TestCase
{
    private $dbQueries;
    private $pdo;

    protected function setUp(): void
    {
        // Use SQLite in-memory database for testing
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables with correct schema
        $this->pdo->exec('
            CREATE TABLE users (
                user_id TEXT PRIMARY KEY
            );
            CREATE TABLE groups (
                group_id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_name TEXT NOT NULL
            );
            CREATE TABLE user_groups (
                user_id TEXT,
                group_id INTEGER,
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (group_id) REFERENCES groups(group_id)
            );
            CREATE TABLE messages (
                message_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT,
                group_id INTEGER,
                message_content TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (group_id) REFERENCES groups(group_id)
            );
        ');
        
        $this->dbQueries = new DBQueries($this->pdo);
    }

    public function testCreateAndGetUser()
    {
        $userId = 'testUser123';
        
        // Test creating user
        $result = $this->dbQueries->createUserQuery($userId);
        $this->assertTrue($result);
        
        // Test getting user
        $user = $this->dbQueries->getUserByIdQuery($userId);
        $this->assertEquals($userId, $user['user_id']);
    }

    public function testCreateAndFetchGroup()
    {
        $groupName = 'Test Group';
        
        // Test creating group
        $groupId = $this->dbQueries->createGroupQuery($groupName);
        $this->assertIsInt($groupId);
        
        // Test fetching all groups
        $groups = $this->dbQueries->fetchAllGroupsQuery();
        $this->assertCount(1, $groups);
        $this->assertEquals($groupName, $groups[0]['group_name']);
    }

    public function testJoinGroup()
    {
        $userId = 'testUser123';
        $groupName = 'Test Group';
        
        // Setup
        $this->dbQueries->createUserQuery($userId);
        $groupId = $this->dbQueries->createGroupQuery($groupName);
        
        // Test joining group
        $result = $this->dbQueries->joinGroupQuery($userId, $groupId);
        $this->assertTrue($result);
        
        // Test getting user's groups
        $userGroups = $this->dbQueries->getUserGroupsQuery($userId);
        $this->assertCount(1, $userGroups);
        $this->assertEquals($groupName, $userGroups[0]['group_name']);
    }
}
