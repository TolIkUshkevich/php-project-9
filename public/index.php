<?php

namespace App;

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use DI\Container;
use Slim\Flash\Messages;
use PostgreSQLTutorial\Connection as Connection;
use Valitron\Validator;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();
$container->set('flash', function () {
    return new Messages();
});

$app = AppFactory::createFromContainer($container);
$renderer = new PhpRenderer(__DIR__ . '/../templates');
$router = $app->getRouteCollector()->getRouteParser();

$container->set(\PDO::class, function () {
    $connecter = new PsqlConnection();
    $conn = $connecter->connect();
    return $conn;
});
$initFilePath = __DIR__ . '/../database.sql';
$initSql = file_get_contents($initFilePath);
$container->get(\PDO::class)->exec($initSql);
$conn = $container->get(\PDO::class);
$repo = new UrlRepository($conn);
$checkRepo = new CheckRepository($conn);

$app->get('/', function ($request, $response) use ($renderer) {
    $params = [];
    if ($request->getParam('error') !== null) {
        $error = $request->getParam('error');
        $url = $request->getParam('url');
        $params = [
            'error' => $error,
            'url' => $url
        ];
    }
    return $renderer->render($response, 'main.phtml', $params);
})->setName('main');

$app->get('/urls', function ($request, $response) use ($repo, $renderer, $checkRepo) {
    $urls = $repo->getUrls();
    $urlsWithChecks = $checkRepo->getChecks($urls);
    $params = [
        'urlsWithChecks' => $urlsWithChecks
    ];
    return $renderer->render($response, 'urls.phtml', $params);
})->setName('urls');

$app->post('/urls', function ($request, $response) use ($repo, $router, $renderer) {
    $flashMap = [
        'new' => 'Страница успешно добавлена',
        'exists' => 'Страница уже существует'
    ];
    $formData = $request->getParsedBody();
    $urlData = $formData['url'];
    $url = new Url($urlData['name']);
    $validator = new Validator(['name' => $url->getName()]);
    $validator->rules([
        'url' => 'name',
        'required' => 'name'
    ]);
    if ($validator->validate()) {
        $status = $repo->save($url);
        $message = $flashMap[$status];
        $this->get('flash')->addMessage('success', $message);
        $route = $router->urlFor('url', ['id' => (string)$url->getId()]);
        return $response->withRedirect($route, 302);
    } else {
        $params = [
            'error' => $url->getName() !== "" ? 'Некорректный URL' : 'URL не должен быть пустым',
            'url' => $url
        ];
        return $renderer->render($response->withStatus(422), 'main.phtml', $params);
    }
})->setName('post_url');

$app->post('/urls/{id}/checks', function ($request, $response, $args) use ($repo, $checkRepo, $router, $renderer) {
    $map = [
        'success' => 'Страница успешно проверена',
        'warning' => 'Проверка была выполнена успешно, но сервер ответил с ошибкой',
        'danger' => 'Произошла ошибка при проверке, не удалось подключиться'
    ];
    $check = new Check();
    $id = $args['id'];
    $url = $repo->find($id);
    if (!$url) {
        return $renderer->render($response->withStatus(402), 'fatal-error.phtml');
    }
    $checkStatus = $check->check($url);
    if ($checkStatus === 'success') {
        $checkRepo->create($check);
    } elseif ($checkStatus === 'fatal') {
        return $renderer->render($response->withStatus(505), 'fatal-error.phtml');
    }
    $this->get('flash')->addMessage($checkStatus, $map[$checkStatus]);
    $route = $router->urlFor('url', ['id' => $id]);
    return $response->withRedirect($route, 302);
})->setName('post_check');

$app->get('/urls/{id}', function ($request, $response, $args) use ($repo, $renderer, $checkRepo) {
    $id = (int)$args['id'];
    $url = $repo->find($id);
    if ($url === null) {
        return $renderer->render($response->withStatus(404), 'not-found.phtml');
    }
    $flashArray = $this->get('flash')->getMessages();
    if ($flashArray !== []) {
        $flashStatus = array_key_first($flashArray);
        $flash = [$flashStatus, $flashArray[$flashStatus][0]];
    } else {
        $flash = null;
    }
    $checks = $checkRepo->getChecksForUrl($url);
    $params = [
        'url' => $url,
        'flash' => $flash,
        'checks' => $checks
    ];
    return $renderer->render($response, 'url.phtml', $params);
})->setName('url');


$app->run();
