<?php
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use DI\Container;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../../src/DB/queries.php';
require_once __DIR__ . '/../../src/routes/groupRoutes.php';
require_once __DIR__ . '/../../src/routes/userRoutes.php';

class RoutesTest extends TestCase
{
    private $app;
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->set('db', function() {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables with correct schema
            $pdo->exec('
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
            return $pdo;
        });
        
        $this->container->set('dbQueries', function($c) {
            return new DBQueries($c->get('db'));
        });

        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        
        // Add middleware
        $this->app->addBodyParsingMiddleware();
        
        // Load routes
        $dbQueries = $this->container->get('dbQueries');
        (require __DIR__ . '/../../src/routes/userRoutes.php')($this->app, $dbQueries);
        (require __DIR__ . '/../../src/routes/groupRoutes.php')($this->app, $dbQueries);
    }

    public function testCreateUser()
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/users')
            ->withParsedBody(['user_id' => 'testUser123']);
            
        $response = $this->app->handle($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('testUser123', $responseData['user_id']);
    }

    public function testCreateGroup()
    {
        // First create a user
        $userId = 'testUser123';
        $this->container->get('dbQueries')->createUserQuery($userId);
        
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/groups')
            ->withParsedBody([
                'group_name' => 'Test Group',
                'user_id' => $userId
            ]);
            
        $response = $this->app->handle($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Test Group', $responseData['name']);
    }

    public function testGetGroups()
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/groups');
        $response = $this->app->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($responseData);
    }
}
