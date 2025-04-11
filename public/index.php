<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\DB\Connection;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

// Start session
session_start();
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = md5(uniqid(rand(), true));
    $_SESSION['token_time'] = time();
}


$container = new Container();

// Ajoute les variables globales
$container->set('menu', function () {
    return [['href' => './index.php', 'text' => 'Accueil']];
});
$container->set('chemin', function () {
    return dirname($_SERVER['SCRIPT_NAME']);
});
$container->set('view', function () {
    return Twig::create(__DIR__ . '/../views', ['cache' => false]);
});

// Passe le container à Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// Connexion à la BDD
Connection::createConn();

// Twig Middleware
$twig = $container->get('view');
$app->add(TwigMiddleware::create($app, $twig));

// Ajout des routes
(require __DIR__ . '/../app/routes/routes.php')($app);

$app->run();
