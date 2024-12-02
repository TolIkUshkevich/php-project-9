<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Url;

class UrlTest extends TestCase {

    private $params;
    private $url;

    public function setUp(): void
    {
        $this->params = [
            'id' => 1,
            'name' => 'https://example@url.com',
            'created_at' => '2024-12-01 00:00:00'
        ];
    }

    public function testFromArray(): void
    {
        $url = Url::fromArray($this->params);
        $this->assertSame($this->params['id'], $url->getId());
        $this->assertSame($this->params['name'], $url->getName());
        $this->assertSame($this->params['created_at'], $url->getCreatedAt());
    }

    public function testToArray(): void
    {
        $url = Url::fromArray($this->params);
        $urlArray = $url->toArray();
        $this->assertSame($urlArray, $this->params);
    }

    public function testSetAndSetFunction(): void
    {
        $url = new Url($this->params['name']);
        $url->setId($this->params['id']);
        $url->setCreatedAt($this->params['created_at']);
        $this->assertSame($this->params['id'], $url->getId());
        $this->assertSame($this->params['created_at'], $url->getCreatedAt());
    }
}