<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$container = new Container();
AppFactory::setContainer($container);

$container->set('view', fn() => Twig::create(__DIR__ . '/../templates/', ['cache' => __DIR__ . '/../var/cache']));

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(TwigMiddleware::createFromContainer($app));

$app->get('/', function ($request, $response) {
    return $this->get('view')->render($response, 'index.twig');
});

$app->run();
