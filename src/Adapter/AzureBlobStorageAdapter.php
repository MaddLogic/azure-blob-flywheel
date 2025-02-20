<?php

namespace MaddLogic\FlysystemAzureBlob\Adapter;

use GuzzleHttp\Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Adapter\AbstractAdapter;

class AzureBlobStorageAdapter extends AbstractAdapter
{
    protected $client;
    protected $accountName;
    protected $accountKey;
    protected $container;
    protected $endpoint;

    public function __construct($accountName, $accountKey, $container, $endpoint, Client $client = null)
    {
        $this->accountName = $accountName;
        $this->accountKey = $accountKey;
        $this->container = $container;
        $this->endpoint = rtrim($endpoint, '/');

        // Allow injection of a custom HTTP client (e.g., for testing)
        $this->client = $client ?: new Client();
    }

    public function getClient()
    {
        return $this->client;
    }

    public function write($path, $contents, Config $config)
    {
        $url = "{$this->endpoint}/{$this->container}/" . ltrim($path, '/');
        $contentLength = strlen($contents);

        $headers = array_merge($this->getHeaders('PUT', $path, $contentLength), [
            'x-ms-blob-type'  => 'BlockBlob', // Required for Azure Blob
            'Content-Type'    => 'application/octet-stream', // Required for proper file handling
            'Content-Length'  => $contentLength
        ]);

        $response = $this->client->put($url, [
            'headers' => $headers,
            'body'    => $contents
        ]);

        return $response->getStatusCode() === 201;
    }



    public function read($path)
    {
        $url = "{$this->endpoint}/{$this->container}/" . ltrim($path, '/');
        $response = $this->client->get($url, [
            'headers' => $this->getHeaders('GET', $path)
        ]);

        return [
            'contents' => (string) $response->getBody()
        ];
    }

    public function has($path)
    {
        $url = "{$this->endpoint}/{$this->container}/" . ltrim($path, '/');
        $response = $this->client->head($url, [
            'headers' => $this->getHeaders('HEAD', $path)
        ]);

        return $response->getStatusCode() === 200;
    }

    protected function getHeaders($method, $path, $contentLength = 0)
    {
        $date = gmdate('D, d M Y H:i:s T');
        $canonicalizedResource = "/{$this->accountName}/{$this->container}/" . ltrim($path, '/');

        // For GET, HEAD, or when contentLength is zero, the canonical content length must be an empty string.
        $canonicalContentLength = ($method === 'GET' || $method === 'HEAD' || $contentLength === 0) ? "" : $contentLength;

        $stringToSign = "$method\n" .               // HTTP Method
            "\n" .                                 // Content-Encoding
            "\n" .                                 // Content-Language
            "{$canonicalContentLength}\n" .         // Content-Length
            "\n" .                                 // Content-MD5
            "application/octet-stream\n" .         // Content-Type
            "\n" .                                 // Date
            "\n" .                                 // If-Modified-Since
            "\n" .                                 // If-Match
            "\n" .                                 // If-None-Match
            "\n" .                                 // If-Unmodified-Since
            "\n" .                                 // Range
            "x-ms-blob-type:BlockBlob\n" .
            "x-ms-date:$date\n" .
            "x-ms-version:2020-04-08\n" .
            $canonicalizedResource;

        $signature = base64_encode(
            hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true)
        );

        return [
            'x-ms-date'     => $date,
            'x-ms-version'  => '2020-04-08',
            'x-ms-blob-type'=> 'BlockBlob',
            'Content-Type'  => 'application/octet-stream',
            'Authorization' => "SharedKey {$this->accountName}:$signature"
        ];
    }




    public function writeStream($path, $resource, Config $config)
    {
        // TODO: Implement writeStream() method.
    }

    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }

    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.
    }

    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.
    }

    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    public function delete($path)
    {
        // TODO: Implement delete() method.
    }

    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }
}
