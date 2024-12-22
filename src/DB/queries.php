<?php

class DBQueries {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createUserQuery($userId) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO users (user_id) VALUES (?)');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log('Error creating user: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserByIdQuery($userId) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createGroupQuery($groupName) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO groups (group_name) VALUES (?)');
            $stmt->execute([$groupName]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                error_log('Group name already exists: ' . $e->getMessage());
                return -1; // Indicate duplicate group name
            }
            error_log('Error creating group: ' . $e->getMessage());
            return false;
        }
    }

    public function fetchAllGroupsQuery() {
        $stmt = $this->pdo->query('SELECT group_id, group_name FROM groups');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function joinGroupQuery($userId, $groupId) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO user_groups (user_id, group_id) VALUES (?, ?)');
            return $stmt->execute([$userId, $groupId]);
        } catch (PDOException $e) {
            error_log('Error joining group: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserGroupsQuery($userId) {
        $stmt = $this->pdo->prepare('
            SELECT g.group_id, g.group_name 
            FROM groups g
            JOIN user_groups ug ON g.group_id = ug.group_id
            WHERE ug.user_id = ?
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupMessagesQuery($groupId) {
        $stmt = $this->pdo->prepare('
            SELECT m.message_id, m.user_id, m.message_content, m.created_at 
            FROM messages m
            WHERE m.group_id = ?
            ORDER BY m.created_at ASC
        ');
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertMessageQuery($userId, $messageContent, $groupId) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO messages (user_id, message_content, group_id) 
                VALUES (?, ?, ?)
            ');
            return $stmt->execute([$userId, $messageContent, $groupId]);
        } catch (PDOException $e) {
            error_log('Error inserting message: ' . $e->getMessage());
            return false;
        }
    }
}
