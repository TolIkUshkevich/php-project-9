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

$app->get('/urls', function ($request, $response) use ($repo, $renderer, $checkRepo) {
    $urls = $repo->getUrls();
    $urlsWithChecks = $checkRepo->getLastChecks($urls);
    $params = [
        'urlsWithChecks' => $urlsWithChecks
    ];
    return $renderer->render($response, 'urls.phtml', $params);
})->setName('urls');

$app->post('/urls', function ($request, $response) use ($repo, $router, $renderer) {
    $flashMap = [
        'new' => '<div class="alert alert-success" role="alert">Страница успешно добавлена</div>',
        'exists' => '<div class="alert alert-success" role="alert">Страница уже существует</div>'
    ];
    $formData = $request->getParsedBody();
    $urlData = $formData['url'];
    $url = new Url($urlData['name']);
    $validator = new Validator(['name' => $url->getName()]);
    $validator->rule('url', 'name');
    if ($validator->validate()) {
        $status = $repo->save($url);
        $message = $flashMap[$status];
        $this->get('flash')->addMessage('processing_success', $message);
        $route = $router->urlFor('url', ['id' => $url->getId()]);
        return $response->withRedirect($route);
    } else {
        $params = [
            'error' => 'wrong url',
            'url' => $url
        ];
        // $route = $router->urlFor('main', [], ['error' => 'error', 'url' => $url->getName()]);
        return $renderer->render($response, 'main.phtml', $params);
    }
})->setName('post_url');

$app->post('/urls/{id}/checks', function ($request, $response, $args) use ($repo, $checkRepo, $router) {
    $map = [
        'check_success' => '<div class="alert alert-success" role="alert">Страница успешно проверена</div>',
        'check_error' => '<div class="alert alert-warning" role="alert">Проверка была выполнена успешно, но сервер ответил с ошибкой</div>',
        'url_error' => '<div class="alert alert-danger" role="alert">Произошла ошибка при проверке, не удалось подключиться</div>'
    ];
    $check = new Check();
    $id = $args['id'];
    $url = $repo->find($id);
    $checkStatus = $check->check($url);
    if ($checkStatus === 'check_success') {
        $checkRepo->create($check);
    }
    $this->get('flash')->addMessage($checkStatus, $map[$checkStatus]);
    $route = $router->urlFor('url', ['id' => $id]);
    return $response->withRedirect($route);
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($repo, $renderer, $checkRepo){
    $flashKeys = [
        'check_success',
        'check_error',
        'url_error',
        'processing_success'
    ];
    $id = (int)$args['id'];
    $url = $repo->find($id);
    $flash = null;
    foreach ($flashKeys as $key) {
        if ($this->get('flash')->getMessage($key)) {
            $flash = $this->get('flash')->getMessage($key);
        }
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