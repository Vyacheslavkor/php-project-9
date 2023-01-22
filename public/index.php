<?php

require __DIR__ . '/../vendor/autoload.php';

use Database\Connection\Connection;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\EscaperExtension;
use Urls\UrlsRepository;
use Urls\Validator;

session_start();

$container = new Container();
AppFactory::setContainer($container);

$escaper = new EscaperExtension('html');
$container->set('view', fn() => Twig::create(__DIR__ . '/../templates/', ['cache' => __DIR__ . '/../var/cache']));
$container->set('flash', fn() => new Messages());

$container->set('db', fn() => Connection::get()->connect($_ENV));

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(TwigMiddleware::createFromContainer($app));

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    return $this->get('view')->render($response, 'index.twig');
})->setName('index');

$app->get('/urls', function ($request, $response) use ($router) {
    $repository = new UrlsRepository($this->get('db'));
    $urls = $repository->getAll();

    $params = ['urls' => $urls];

    return $this->get('view')->render($response, 'urls.twig', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($request, $response, array $args) use ($router) {
    $id = $args['id'];
    $urlsRepository = new UrlsRepository($this->get('db'));

    $url = $urlsRepository->getById($id);

    $messages = $this->get('flash')->getMessages();
    $params = ['flash' => $messages, 'url' => $url];

    return $this->get('view')->render($response, 'url.twig', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router) {
    $urlData = $request->getParsedBodyParam('url');
    $validator = new Validator();

    $result = $validator->validate($urlData);

    if (!$result->isSuccessful()) {
        $errors = $result->getErrors();
        $error = reset($errors);

        $params = [
            'url'   => $urlData,
            'error' => $error,
        ];

        $response = $response->withStatus(422);

        return $this->get('view')->render($response, 'index.twig', $params);
    }

    $urlName = $result->getData('url');
    $urlsRepository = new UrlsRepository($this->get('db'));
    $existingUrl = $urlsRepository->getByName($urlName);

    if (empty($existingUrl)) {
        $urlId = $urlsRepository->add($urlName);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    } else {
        $urlId = $existingUrl['id'];
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    }

    $redirectUrl = $router->urlFor('url', ['id' => $urlId]);

    return $response->withRedirect($redirectUrl);
})->setName('addUrl');

$app->run();
