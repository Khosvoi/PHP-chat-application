<?php
require_once  __DIR__ . '/DB_connector.php';

class DBQueries{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?? DBConnector::getConnection();
        $this->createTables();
    }

    private function createTables()
    {
        // Create user_ids table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_ids (
                user_id TEXT PRIMARY KEY,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create groups table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_name TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create group_members table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS group_members (
                user_id TEXT,
                group_id INTEGER,
                joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, group_id),
                FOREIGN KEY (user_id) REFERENCES user_ids(user_id),
                FOREIGN KEY (group_id) REFERENCES groups(id)
            )
        ");
        
        // Create messages table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                message_content TEXT NOT NULL,
                group_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user_ids(user_id),
                FOREIGN KEY (group_id) REFERENCES groups(id)
            )
        ");
    }

    public function createGroupQuery($groupName)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO groups (group_name) 
            VALUES (:group_name)
        ");
        $stmt->execute([':group_name' => $groupName]);
        return $this->pdo->lastInsertId();
    }

    public function joinGroupQuery($userId, $groupId): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO group_members (user_id, group_id) 
            VALUES (:user_id, :group_id)
            ON CONFLICT (user_id, group_id) DO NOTHING
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':group_id' => $groupId
        ]);
    }

    public function getUserGroupsQuery($userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT g.id as group_id, g.group_name, g.created_at
            FROM groups g
            INNER JOIN group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = :user_id
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllGroupsQuery(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id as group_id, group_name, created_at
            FROM groups
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupMessagesQuery($groupId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.id, m.user_id, m.message_content, m.created_at
            FROM messages m
            WHERE m.group_id = :group_id
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUserQuery($userId)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_ids (user_id) 
            VALUES (:user_id)
        ");
        $stmt->execute([':user_id' => $userId]);
        return $userId;
    }

    public function getUserByIdQuery($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT user_id, created_at
            FROM user_ids
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertMessageQuery($userId, $messageContent, $groupId): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (user_id, message_content, group_id)
            VALUES (:user_id, :message_content, :group_id)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':message_content' => $messageContent,
            ':group_id' => $groupId
        ]);
    }
}
