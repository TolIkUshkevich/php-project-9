<?php

namespace App;

use Carbon\Carbon;

class CheckRepository {
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getChecksForUrl(Url $url) {
        $checks = [];
        $sql = "SELECT * FROM url_checks WHERE url_id = :url_id";
        $stmt = $this->conn->prepare($sql);
        $urlId = $url->getId();
        $stmt->bindParam(':url_id', $urlId);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            // var_dump($row);
            // die;
            $checks[] = Check::fromarray($row);
        }

        return $checks;
    }

    public function create(Check $check) {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) VALUES (:url_id, :status_code, :h1, :title, :description, NOW())";
        $stmt = $this->conn->prepare($sql);
        $urlId = $check->getUrlId();
        $statusCode = $check->getStatusCode();
        $h1 = $check->getH1();
        $title = $check->getTitle();
        $description = $check->getBody();
        $stmt->bindParam(':url_id', $urlId);
        $stmt->bindParam(':status_code', $statusCode);
        $stmt->bindParam(':h1', $h1);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $id = (int) $this->conn->lastInsertId();
        $check->setId($id);
        $check->setCreatedAt(Carbon::now());
    }

    public function find(int $id) {
        $sql = "SELECT * FROM url_checks WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        if ($row = $stmt->fetch())  {
            $url = Check::fromArray($row);
            return $url;
        }

        return null;
    }

}