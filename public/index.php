<?php

namespace App;

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\PhpRenderer;
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
    $storage = [];
    return new Messages($storage    );
});

$app = AppFactory::createFromContainer($container);
$renderer = new PhpRenderer(__DIR__ . '/../templates');
$router = $app->getRouteCollector()->getRouteParser();

$initFilePath = __DIR__ . '/../init.sql';
$initSql = file_get_contents($initFilePath);
$container->get(\PDO::class)->exec($initSql);
$conn = $container->get(\PDO::class);
$repo = new UrlRepository($conn);


$app->get('/', function ($request, $response) use ($renderer){
    return $renderer->render($response, 'main.phtml');
})->setName('main');

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
        $this->get('flash')->addMessage('new', 'aaaaaa');
        var_dump($this->get('flash')->getMessages());
        $route = $router->urlFor('url', ['id' => $url->getId()], ['status' => $status]);
        return $response->withRedirect($route);
    } else {
        var_dump($validator->errors());
    }
})->setName('post_url');

$app->get('/urls/{id}', function ($request, $response, $args) use ($repo, $renderer){
    $id = $args['id'];
    $status = $request->getParam('status');
    $url = $repo->find($id);
    $flash = $this->get('flash')->getMessage('new');
    var_dump($this->get('flash')->getMessages());
    $params = [
        'url' => $url->toArray(),
        'flash' => $flash,
        'status' => $status
    ];
    return $renderer->render($response, 'urls.phtml', $params);
})->setName('url');

$app->run();