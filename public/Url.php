<?php

namespace App;

class Url
{
    private $name;
    private $id;
    private $createdAt;

    public static function fromArray(array $params): Url
    {
        $url = new Url($params['name']);
        $url->setId($params['id']);
        $url->setCreatedAt($params['created_at']);
        return $url;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'created_at' => $this->getCreatedAt()
        ];
    }

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCreatedAt($time): void
    {
        $this->createdAt = $time;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
