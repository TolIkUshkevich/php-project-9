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
$container->set(\PDO::class, function () {
    $connecter = new PsqlConnection;
    $conn = $connecter->connect();
    return $conn;
});
$container->set('flash', function () {
    return new Messages();
});

$app = AppFactory::createFromContainer($container);
$renderer = new PhpRenderer(__DIR__ . '/../templates');
$router = $app->getRouteCollector()->getRouteParser();

$initFilePath = __DIR__ . '/../init.sql';
$initSql = file_get_contents($initFilePath);
$container->get(\PDO::class)->exec($initSql);
$conn = $container->get(\PDO::class);
$repo = new UrlRepository($conn);
$checkRepo = new CheckRepository($conn);

$app->get('/', function ($request, $response) use ($renderer){
    if ($request->getParam('error') !== null) {
        $error = $request->getParam('error');
        $url = $request->getParam('url');
        $params = [
            'error' => $error,
            'url' => $url
        ];
    } else {
        $params = [];
    }
    return $renderer->render($response, 'main.phtml', $params);
})->setName('main');

$app->get('/urls', function ($request, $response) use ($repo, $renderer ) {
    $urls = $repo->getUrls();
    $params = [
        'urls' => $urls
    ];
    return $renderer->render($response, 'urls.phtml', $params);
})->setName('urls');

$app->post('/urls', function ($request, $response) use ($repo, $router) {
    $flashMap = [
        'new' => 'Страница успешно добавлена',
        'exists' => 'Страница уже существует'
    ];
    $formData = $request->getParsedBody();
    $urlData = $formData['url'];
    $url = new Url($urlData['name']);
    $validator = new Validator(['name' => $url->getName()]);
    $validator->rule('url', 'name');
    if ($validator->validate()) {
        $status = $repo->save($url);
        $message = $flashMap['new'];
        $this->get('flash')->addMessage('success', $message);
        $route = $router->urlFor('url', ['id' => $url->getId()]);
        return $response->withRedirect($route);
    } else {
        $route = $router->urlFor('main', [], ['error' => 'error', 'url' => $url->getName()]);
        return $response->withRedirect($route);
    }
})->setName('post_url');

$app->post('/urls/{id}/checks', function ($request, $response, $args) use ($repo, $checkRepo, $router) {
    $check = new Check();
    $id = $args['id'];
    $url = $repo->find($id);
    $checkStatus = $check->check($url);
    if ($checkStatus) {
        $checkRepo->create($check);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } else {
        $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
    }
    $route = $router->urlFor('url', ['id' => $id]);
    return $response->withRedirect($route);
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($repo, $renderer, $checkRepo){
    $id = (int)$args['id'];
    $url = $repo->find($id);
    if ($this->get('flash')->getMessage('success') !== null) {
        $flash = $this->get('flash')->getMessage('success');
        $status = 'success';
    } else {
        $flash = $this->get('flash')->getMessage('error');
        $status = 'error';
    }
    $checks = $checkRepo->getChecksForUrl($url);    
    $params = [
        'url' => $url,
        'flash' => $flash,
        'status' => $status,
        'checks' => $checks
    ];
    return $renderer->render($response, 'url.phtml', $params);
})->setName('url');


$app->run();