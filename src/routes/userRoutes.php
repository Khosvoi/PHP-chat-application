<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app, DBQueries $dbQueries) {
    $app->post('/users', function (Request $request, Response $response) use ($dbQueries) {
        $params = (array)$request->getParsedBody();
        error_log('Incoming Request: ' . json_encode($params));
        $userId = $params['user_id'] ?? null;

        if (!$userId) {
            $error = ['error' => 'User ID is required'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Check if user exists
            $existingUser = $dbQueries->getUserByIdQuery($userId);
            
            if ($existingUser) {
                // User exists, return success with existing user data
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'user_id' => $userId,
                    'message' => 'User logged in successfully'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            // Create new user if doesn't exist
            $newUserId = $dbQueries->createUserQuery($userId);
            $response->getBody()->write(json_encode([
                'success' => true,
                'user_id' => $userId,
                'message' => 'New user created successfully'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $error = ['error' => 'Internal server error'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};