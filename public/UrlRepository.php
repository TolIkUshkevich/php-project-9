<?php

namespace App;

class UrlRepository
{
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    // private function getLastCreatedAt(Url $url) {
    //     $lastUrl = $this->find((int) $this->conn->lastInsertId());
    //     return $lastUrl->setCreatedAt();
    // }

    public function getEntities(): array
    {
        $urls = [];
        $sql = "SELECT * FROM urls";
        $stmt = $this->conn->query($sql);

        while ($row = $stmt->fetch()) {
            $url = new Url([$row['id'], $row['url']]);
            $urls[] = $url;
        }

        return $urls;
    }

    public function find(int $id)
    {
        $sql = "SELECT * FROM urls WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        if ($row = $stmt->fetch())  {
            $url = Url::fromArray($row);
            return $url;
        }

        return null;
    }

    public function findByUrl(Url $url)
    {
        $urlName = $url->getName();
        $sql = "SELECT * FROM urls WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$urlName]);
        if ($row = $stmt->fetch())  {
            $url = Url::fromArray($row);
            return true;
        }

        return false;
    }

    public function exists(Url $url) {
        return $this->findByUrl($url) != null;
    }

    public function save(Url $url) {
        if ($this->exists($url)) {
            $this->setExistsUrlData($url);
            return 'exists';
        } else {
            $this->create($url);
            return 'new';
        }
    }

    private function setExistsUrlData(Url $url): void
    {
        $urlData = $this->findByUrl($url);
        $id = (int)[$urlData['id']];
        $url->setId($id);
        $url->setCreatedAt([$urlData['created_at']]);
    }

    private function create(Url $url): void
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, NOW())";
        $stmt = $this->conn->prepare($sql);
        $name = $url->getName();
        $stmt->bindParam(':name', $name);
        $stmt->execute([$name]);
        $id = (int) $this->conn->lastInsertId();
        $url->setId($id);
    }
}
