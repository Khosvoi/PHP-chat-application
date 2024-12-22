<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, DBQueries $dbQueries) {
    // Create a group
    $app->post('/groups', function (Request $request, Response $response) use ($dbQueries) {
        $params = (array)$request->getParsedBody();
        $groupName = $params['group_name'] ?? null;
        $userId = $params['user_id'] ?? null;

        if (!$groupName || !$userId) {
            $error = ['error' => 'Group name and user ID are required'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $groupId = $dbQueries->createGroupQuery($groupName);
            // Auto-join the creator to the group
            $dbQueries->joinGroupQuery($userId, $groupId);
            
            $payload = ['id' => $groupId, 'name' => $groupName];
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $error = ['error' => 'Failed to create group'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Get all groups
    $app->get('/groups', function (Request $request, Response $response) use ($dbQueries) {
        try {
            $groups = $dbQueries->fetchAllGroupsQuery();
            $response->getBody()->write(json_encode($groups));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            error_log('Error fetching groups: ' . $e->getMessage());
            $error = ['error' => 'Failed to fetch groups'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Get user's groups
    $app->get('/users/{userId}/groups', function (Request $request, Response $response, array $args) use ($dbQueries) {
        $userId = $args['userId'];
        
        try {
            $groups = $dbQueries->getUserGroupsQuery($userId);
            $response->getBody()->write(json_encode($groups));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $error = ['error' => 'Failed to fetch user groups'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Join a group
    $app->post('/groups/{id}/join', function (Request $request, Response $response, array $args) use ($dbQueries) {
        $groupId = $args['id'];
        $params = (array)$request->getParsedBody();
        $userId = $params['user_id'] ?? null;

        if (!$userId) {
            $error = ['error' => 'User ID is required'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $success = $dbQueries->joinGroupQuery($userId, $groupId);
            if ($success) {
                $payload = ['status' => 'Joined group successfully'];
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
            $error = ['error' => 'Failed to join group'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        } catch (Exception $e) {
            $error = ['error' => 'Failed to join group'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Get group messages
    $app->get('/groups/{id}/messages', function (Request $request, Response $response, array $args) use ($dbQueries) {
        $groupId = $args['id'];
        
        try {
            $messages = $dbQueries->getGroupMessagesQuery($groupId);
            $response->getBody()->write(json_encode($messages));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $error = ['error' => 'Failed to fetch messages'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};