<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require '../vendor/autoload.php';
$config = require_once '../configs/web.php';

$container = new \DI\Container();
$container->set('settings', $config);
$specialistsConfig = require_once '../configs/specialists.php';
$container->set('specialistsConfig', $specialistsConfig);
$molulesConfig = require_once '../configs/modules.php';
$container->set('molulesConfig', $molulesConfig);
// Register component on container
$container->set('db', function ($container) {
    $db = $container->get('settings')['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->query('set names utf8');
    return $pdo;
});
$container->set('view', function() {
    return new \Slim\Views\PhpRenderer('../app/templates/');
});
$container->set('session', function($container) {
    return new \App\Components\Session($container);
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(new \Slim\Middleware\Session($config['session']));

/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->addBodyParsingMiddleware();

$app->get('/', App\Controllers\MainController::class . ':index');
$app->map(['GET', 'POST'],'/login', App\Controllers\LoginController::class . ':login');
$app->get('/logout', App\Controllers\LoginController::class . ':logout');
$app->post('/api', App\Controllers\ApiController::class . ':index');

$app->group('/inkcom', function (RouteCollectorProxy $group) {
    $group->get('', App\Controllers\InkcomController::class . ':index');
    $group->get('/commissions', App\Controllers\InkcomController::class . ':commissions');
    $group->get('/classes', App\Controllers\InkcomController::class . ':classes');
})->add(new App\Components\PermissionMiddleware($container, 'ink'));

$app->group('/config', function (RouteCollectorProxy $group) {
    $group->get('/personal', App\Controllers\ConfigController::class . ':personal')->setName('config-personal');
    $group->get('/personal-{id}', App\Controllers\ConfigController::class . ':personalEdit')->setName('config-personal');
    $group->get('/positions', App\Controllers\ConfigController::class . ':positions')->setName('config-positions');
    $group->get('/years', App\Controllers\ConfigController::class . ':years')->setName('config-years');
    $group->get('/classes', App\Controllers\ConfigController::class . ':classes')->setName('config-classes');
})->add(new App\Components\PermissionMiddleware($container, 'cfg'));

$app->group('/vcomis', function (RouteCollectorProxy $group) {
    $group->get('', App\Controllers\VcomisController::class . ':index');
    $group->get('/report-{id}', App\Controllers\VcomisController::class . ':report');
})->add(new App\Components\PermissionMiddleware($container, 'vcm'));

$app->group('/logoped', function (RouteCollectorProxy $group) {
    $group->get('/register', App\Controllers\LogopedController::class . ':register')->setName('logoped-list');
    $group->get('/exams', App\Controllers\LogopedController::class . ':exams')->setName('logoped-exams');
})->add(new App\Components\PermissionMiddleware($container, 'lgp'));

$app->group('/specialist-{id}', function (RouteCollectorProxy $group) {
    $group->get('/register', App\Controllers\SpecialistController::class . ':register')->setName('specialist-register');
    $group->get('/reports', App\Controllers\SpecialistController::class . ':reports')->setName('specialist-reports');
})->add(new App\Components\PermissionGroupMiddleware($container));

$app->group('/rablist', function (RouteCollectorProxy $group) {
    $group->get('', App\Controllers\RablistController::class . ':index');
})->add(new App\Components\PermissionMiddleware($container, 'rbl'));

$app->group('/plan', function (RouteCollectorProxy $group) {
    $group->get('', App\Controllers\PlanController::class . ':index')->setName('plan-index');
    $group->get('/add-user', App\Controllers\PlanController::class . ':addUser')->setName('plan-adduser');
    $group->get('/add-class', App\Controllers\PlanController::class . ':addClass')->setName('plan-addclass');
})->add(new App\Components\PermissionMiddleware($container, 'pln'));

$app->get('/password/{pass}', function (Request $request, Response $response, array $args) {
    $pass = $args['pass'];
    $response->getBody()->write("Hesh: " . password_hash($pass, PASSWORD_DEFAULT));

    return $response;
});
$app->run();