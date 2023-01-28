<?php

require __DIR__ . '/../vendor/autoload.php';

use Database\Connection\Connection;
use DI\Container;
use DiDom\Document;
use Documents\Parser;
use GuzzleHttp\Client;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Urls\UrlChecksRepository;
use Urls\UrlsRepository;
use Urls\Validator;

session_start();

$container = new Container();
AppFactory::setContainer($container);

$container->set('view', fn() => Twig::create(__DIR__ . '/../templates/'));
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

    $checksRepository = new UrlChecksRepository($this->get('db'));
    $urls = array_map(static fn($url) => array_merge($url, $checksRepository->getLastCheck($url['id'])), $urls);

    $params = ['urls' => $urls];

    return $this->get('view')->render($response, 'urls.twig', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($request, $response, array $args) use ($router) {
    $id = $args['id'];
    $urlsRepository = new UrlsRepository($this->get('db'));

    $url = $urlsRepository->getById($id);
    $messages = $this->get('flash')->getMessages();

    $checksRepository = new UrlChecksRepository($this->get('db'));
    $checks = $checksRepository->getAllByUrlId($url['id']);

    $params = ['flash' => $messages, 'url' => $url, 'checks' => $checks];

    return $this->get('view')->render($response, 'url.twig', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router) {
    $urlData = $request->getParsedBodyParam('url');
    $validator = new Validator();

    $result = $validator->validate($urlData);

    if (!$result->isSuccessful()) {
        [$error] = $result->getErrors();

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

$app->post('/urls/{url_id}/checks', function ($request, $response, array $args) use ($router) {
    $urlId = $args['url_id'];
    $db = $this->get('db');
    $client = new Client();

    try {
        $urlsRepository = new UrlsRepository($db);
        $urlData = $urlsRepository->getById($urlId);

        $result = $client->get($urlData['name'], ['body' => true, 'timeout' => 5]);

        $params = ['status_code' => $result->getStatusCode()];

        $document = new Document((string) $result->getBody());

        $parser = new Parser($document);
        $parsedParams = $parser->parse();
        $params = array_merge($params, $parsedParams);

        $repository = new UrlChecksRepository($db);
        $repository->save($urlId, $params);

        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (\Exception $exception) {
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
    }

    $redirectUrl = $router->urlFor('url', ['id' => $urlId]);

    return $response->withRedirect($redirectUrl);
})->setName('check');

$app->run();
