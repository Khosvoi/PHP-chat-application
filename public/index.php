<?php
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\MiddleWare\BodyParsingMiddleware;
require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);

require_once __DIR__ . '/../src/dependencies.php';
require_once __DIR__ . '/../src/routes.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Load and apply route definitions
try {
    $dbQueries = $container->get('dbQueries');

    (require __DIR__ . '/../src/routes/groupRoutes.php')($app, $dbQueries);
    (require __DIR__ . '/../src/routes/messageRoutes.php')($app, $dbQueries);
    (require __DIR__ . '/../src/routes/userRoutes.php')($app, $dbQueries);
}
catch(Exception $e){
    error_log($e->getMessage());
    $app->get('/', function ($request, $response, $args) use ($e) {
        $response->getBody()->write('An error with loading routes: ' . $e->getMessage());
        return $response->withStatus(500);
});}
$app->run();

//Create a named volume
// docker volume create chat-db-volume

// # Run your container with the volume
// docker run -p 8000:8000 -v chat-db-volume:/var/lib/chat-db your-image-name