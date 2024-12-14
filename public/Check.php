<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use DiDom\Document;

class Check
{
    private Client $client;
    private int $id;
    private string $urlId;
    private string $h1;
    private int $statusCode;
    private string $description;
    private string $title;
    private string $createdAt;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => '']);
    }

    public static function fromArray(array|false $params): Check
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

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUrlId(int $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setH1(string $h1): void
    {
        $this->h1 = $h1;
    }

    public function getH1(): string | null
    {
        return $this->h1;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int | null
    {
        return $this->statusCode;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {

        return $this->description;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setCreatedAt(string $createdAt): void
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
