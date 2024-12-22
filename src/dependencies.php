<?php
use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
require_once __DIR__ . '/../src/DB/queries.php';

$container = new Container();

// Database connection setup
$container->set('db', function(ContainerInterface $c) {
    $pdo = new PDO('sqlite:/var/lib/chat-db/messagingDB.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

$container->set('dbQueries', function(ContainerInterface $c) {
    return new DBQueries($c->get('db'));
});

// use this container in Slim
AppFactory::setContainer($container);