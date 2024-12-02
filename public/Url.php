<?php

namespace App;

class Url {
    private $name;
    private $id;
    private $createdAt;

    public static function fromArray(array $params) {
        $url = new Url($params['name']);
        $url->setId($params['id']);
        $url->setCreatedAt($params['created_at']);
        return $url;
    }

    public function toArray() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'created_at' => $this->getCreatedAt()
        ];
    }

    public function __construct($name) {
        $this->name = $name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCreatedAt($time) {
        $this->createdAt = $time;
    }

    public function getName() {
        return $this->name;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getId() {
        return $this->id;
    }

}