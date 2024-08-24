<?php

namespace Test\Unit;

use GuzzleHttp\HandlerStack;
use Leantime\Core\Http\Client\ApiSession;
use PHPUnit\Framework\TestCase;

class ApiSessionTest extends TestCase
{
    public function testOAuth2(): void
    {
        $baseUri = 'http://test.com';
        $stack = HandlerStack::create();
        $requestDefaults = [];

        $client = ApiSession::oAuth2($baseUri, $stack, $requestDefaults);

        $this->assertEquals('http://test.com', $client->getConfig('base_uri'));
        $this->assertSame($stack, $client->getConfig('handler'));
        $this->assertEquals('oauth', $client->getConfig('auth'));
    }

    public function testOAuth2Grants(): void
    {
        $baseUri = 'http://test.com';
        $creds = [
            'client_id' => 'testclient',
            'client_secret' => 'testsecret',
        ];

        $stack = ApiSession::oAuth2Grants($baseUri, $creds);

        $this->assertInstanceOf(HandlerStack::class, $stack);
    }

    public function testOAuth1(): void
    {
        $baseUri = 'http://test.com';
        $creds = [
            'consumer_key' => 'testconsumer',
            'consumer_secret' => 'testsecret',
            'token' => 'testtoken',
            'token_secret' => 'testtokensecret',
        ];

        $client = ApiSession::oAuth1($baseUri, $creds);

        $this->assertEquals('http://test.com', $client->getConfig('base_uri'));
        $this->assertEquals('oauth', $client->getConfig('auth'));
    }

    public function testBasicAuth(): void
    {
        $baseUri = 'http://test.com';
        $creds = [
            'username' => 'testuser',
            'password' => 'testpass',
        ];

        $client = ApiSession::basicAuth($baseUri, $creds);

        $this->assertEquals('http://test.com', $client->getConfig('base_uri'));
        $this->assertEquals($creds, $client->getConfig('auth'));
    }

    public function testDigest(): void
    {
        $baseUri = 'http://test.com';
        $creds = [
            'username' => 'testuser',
            'password' => 'testpass',
            'digest' => 'testdigest',
        ];

        $client = ApiSession::digest($baseUri, $creds);

        $config = $client->getConfig();
        $this->assertEquals('http://test.com', $config[1]['base_uri']);
        $this->assertEquals($creds,  $config[1]['auth']);
    }

    public function testNtlm(): void
    {
        $baseUri = 'http://test.com';
        $creds = [
            'username' => 'testuser',
            'password' => 'testpass',
            'ntlm' => 'testntlm',
        ];

        $client = ApiSession::ntlm($baseUri, $creds);

        $this->assertEquals('http://test.com', $client->getConfig('base_uri'));
        $this->assertEquals($creds, $client->getConfig('auth'));
    }
}
