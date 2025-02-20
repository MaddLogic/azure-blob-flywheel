<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use League\Flysystem\Config;
use MaddLogic\FlysystemAzureBlob\Adapter\AzureBlobStorageAdapter;

#[CoversClass(\MaddLogic\FlysystemAzureBlob\Adapter\AzureBlobStorageAdapter::class)]
class AzureBlobStorageAdapterTest extends TestCase
{
    private $adapter;
    private $mockClient;

    public function testWriteFile()
    {
        // Ensure responses match expected Azure Blob Storage API behavior
        $mock = new MockHandler([
            new Response(201),            // Write (PUT)
            new Response(200, ['Content-Length' => 11], 'File Content'), // Read (GET)
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack,
            ]);

        // Pass the mock client into the adapter
        $this->adapter = new AzureBlobStorageAdapter(
            'testaccount',
            'testkey',
            'testcontainer',
            'https://testaccount.blob.core.windows.net',
            $mockClient
        );

        $result = $this->adapter->write('test-folder/test.txt', 'Hello, Azure!', new Config());

        // Assert that the file write operation was successful
        $this->assertTrue($result);

        // Verify that the file now exists
        $exists = $this->adapter->has('test-folder/test.txt');
        $this->assertTrue($exists, 'File should exist after writing');
    }

    public function testReadFile()
    {
        // Ensure responses match expected Azure Blob Storage API behavior
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 11], 'File Content'), // Read (GET)
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Pass the mock client into the adapter
        $this->adapter = new AzureBlobStorageAdapter(
            'testaccount',
            'testkey',
            'testcontainer',
            'https://testaccount.blob.core.windows.net',
            $mockClient
        );

        $file = $this->adapter->read('test-folder/test.txt');

        $this->assertArrayHasKey('contents', $file);
        $this->assertEquals('File Content', $file['contents']);
    }

    public function testFileExists()
    {
        // Ensure responses match expected Azure Blob Storage API behavior
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 11]) // File existence check (HEAD)
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Pass the mock client into the adapter
        $this->adapter = new AzureBlobStorageAdapter(
            'testaccount',
            'testkey',
            'testcontainer',
            'https://testaccount.blob.core.windows.net',
            $mockClient
        );

        $exists = $this->adapter->has('test-folder/test.txt');

        $this->assertTrue($exists);
    }

}
