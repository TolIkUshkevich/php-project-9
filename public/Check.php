<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use DiDom\Document;

class Check
{
    private $client;
    private $id;
    private $urlId;
    private $h1;
    private $body;
    private $statusCode;
    private $description;
    private $title;
    private $createdAt;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => '']);
    }

    public static function fromArray(array|false $params): check
    {
        $map = [
            'id' => fn($check, $id) => $check->setId($id),
            'url_id' => fn($check, $urlId) => $check->setUrlId($urlId),
            'h1' => fn($check, $h1) => $check->setH1($h1),
            'status_code' => fn($check, $statusCode) => $check->setStatusCode($statusCode),
            'description' => fn($check, $description) => $check->setDescription($description),
            'title' => fn($check, $title) => $check->setTitle($title),
            'created_at' => fn($check, $createdAt) => $check->setCreatedAt($createdAt)
        ];

        $check = new Check();

        if (!$params) {
            return $check;
        }

        foreach ($params as $key => $value) {
            if (array_key_exists($key, $map)) {
                $map[$key]($check, $value);
            }
        }

        return $check;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUrlId($urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setH1($h1): void
    {
        $this->h1 = $h1;
    }

    public function getH1(): string | null
    {
        return $this->h1;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getBody(): string | null
    {
        return $this->body;
    }

    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int | null
    {
        return $this->statusCode;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {

        return $this->description;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): string | null
    {
        return $this->createdAt;
    }

    public function check(Url $url): string
    {
        $name = $url->getName();
        try {
            $response = $this->client->get($name);
            $body = $response->getBody();
            $document = new Document((string)$body);
            $urlId = $url->getId();
            $statusCode = $response->getStatusCode();
            var_dump($statusCode);
            die;
            $h1 = optional($document->first('h1'))->text();
            $title = optional($document->first('title'))->text();
            $description = optional($document->xpath("//meta[@name='description']/@content"))[0];
            $this->setUrlId($urlId);
            $this->setH1($h1);
            $this->setBody(htmlspecialchars($body));
            $this->setStatusCode((int)$statusCode);
            $this->setDescription($description);
            $this->setTitle($title);
            return 'success';
        } catch (\GuzzleHttp\Exception\ClientException) {
            return 'warning';
        } catch (\GuzzleHttp\Exception\ConnectException) {
            return 'danger';
        } catch (\GuzzleHttp\Exception\RequestException) {
            return 'fatal';
        }
    }
}
