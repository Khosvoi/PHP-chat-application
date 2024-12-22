<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, DBQueries $dbQueries) {
    $app->post('/messages', function (Request $request, Response $response) use ($dbQueries) {
        $params = (array)$request->getParsedBody();
        $userId = $params['user_id'] ?? null;
        $messageContent = $params['message_content'] ?? null;
        $groupId = $params['group_id'] ?? null;

        if (!$userId || !$messageContent || !$groupId) {
            $error = ['error' => 'User ID, message content, and group ID are required'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $success = $dbQueries->insertMessageQuery($userId, $messageContent, $groupId);
            if ($success) {
                $response->getBody()->write(json_encode(['success' => true]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            }
            throw new Exception('Failed to insert message');
        } catch (Exception $e) {
            error_log('Error creating message: ' . $e->getMessage());
            $error = ['error' => 'Failed to create message'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};