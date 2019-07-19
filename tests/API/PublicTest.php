<?php

class PublicTest extends ApiTestCase
{
    public function testHealth()
    {
        $this->get('/health');
        $content = json_decode($this->response->getContent(), true);
        $this->assertEquals('ok', array_get($content, 'status'));
    }
}