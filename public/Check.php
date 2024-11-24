<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Check {
    private $client;
    private $id;
    private $urlId;
    private $h1;
    private $body;
    private $statusCode;
    private $description;
    private $title;
    private $createdAt;

    public function __construct() {
        $this->client = new Client(['base_uri' => '']);
    }

    public static function fromArray(array $params) {
        $check = new Check();
        $check->setId($params['id']);
        $check->setUrlId($params['url_id']);
        $check->setH1($params['h1']);
        $check->setStatusCode($params['status_code']);
        $check->setDescription($params['description']);
        $check->setTitle($params['title']);
        $check->setCreatedAt($params['created_at']);
        return $check;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setUrlId($urlId) {
        $this->urlId = $urlId;
    }

    public function getUrlId() {
        return $this->urlId;
    }

    public function setH1($h1) {
        $this->h1 = $h1;
    }

    public function getH1() {
        return $this->h1;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getBody() {
        return $this->body;
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function check(Url $url) {
        $name = $url->getName();
        try {
            $response = $this->client->get($name);
            $urlId = $url->getId();
            $h1 = $response->getHeaders();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            preg_match('/(?<=<title>)(.|\n)*?(?=<\/title>)/', $body, $title);
            preg_match('/(?<=<body>)(.|\n)*?(?=<\/body>)/', $body, $description);
            $this->setUrlId($urlId);
            $this->setH1($h1);
            $this->setBody(htmlspecialchars($body));
            $this->setStatusCode($statusCode);
            $this->setDescription($description[0]);
            $this->setTitle($title[0]);
            return true;
        } catch (\GuzzleHttp\Exception\ConnectException) {
            return false;
        }
    }
}