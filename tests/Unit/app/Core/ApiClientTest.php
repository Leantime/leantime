<?php

namespace Test\Unit;

use GuzzleHttp\HandlerStack;
use Leantime\Core\Http\Client\ApiClient;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    public function testOAuth2(): void
    {
        $baseUri = 'http://test.com';
        $stack = HandlerStack::create();
        $requestDefaults = [];

        $client = ApiClient::oAuth2($baseUri, $stack, $requestDefaults);

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

        $stack = ApiClient::oAuth2Grants($baseUri, $creds);

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

        $client = ApiClient::oAuth1($baseUri, $creds);

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

        $client = ApiClient::basicAuth($baseUri, $creds);

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

        $client = ApiClient::digest($baseUri, $creds);

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

        $client = ApiClient::ntlm($baseUri, $creds);

        $this->assertEquals('http://test.com', $client->getConfig('base_uri'));
        $this->assertEquals($creds, $client->getConfig('auth'));
    }
}
