<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use League\Flysystem\Config;
use MaddLogic\FlysystemAzureBlob\Adapter\AzureBlobStorageAdapter;

class AzureBlobStorageIntegrationTest extends TestCase
{
    private $adapter;
    private $testFilePath = 'test-folder/integration-test.txt';
    private $testFileContent = 'This is a real integration test file!';

    protected function setUp(): void
    {
        // Load environment variables for Azure credentials
        $accountName = getenv('AZURE_STORAGE_ACCOUNT');
        $accountKey = getenv('AZURE_STORAGE_KEY');
        $container = getenv('AZURE_STORAGE_CONTAINER');
        $endpoint = getenv('AZURE_STORAGE_ENDPOINT');

        if (!$accountName || !$accountKey || !$container || !$endpoint) {
            $this->markTestSkipped('Azure storage credentials are missing.');
        }

        // Create a real Azure Blob Storage adapter
        $this->adapter = new AzureBlobStorageAdapter(
            $accountName,
            $accountKey,
            $container,
            $endpoint
        );
    }

    public function testWriteToAzureBlob()
    {
        $result = $this->adapter->write($this->testFilePath, $this->testFileContent, new Config());

        // Ensure the file was successfully written
        $this->assertTrue($result, 'Failed to write file to Azure Blob Storage.');
    }

    #[Depends('testWriteToAzureBlob')]
    public function testCheckFileExistsInAzureBlob()
    {
        $exists = $this->adapter->has($this->testFilePath);

        // Ensure the file exists in Azure Blob Storage
        $this->assertTrue($exists, 'File does not exist in Azure Blob Storage.');
    }

    #[Depends('testCheckFileExistsInAzureBlob')]
    public function testReadFromAzureBlob()
    {
        $file = $this->adapter->read($this->testFilePath);

        // Ensure the file contents match
        $this->assertArrayHasKey('contents', $file);
        $this->assertEquals($this->testFileContent, $file['contents'], 'File content mismatch.');
    }

    #[Depends('testReadFromAzureBlob')]
    public function testDeleteFileFromAzureBlob()
    {
        $this->adapter->delete($this->testFilePath);

        // Ensure the file is deleted
        $exists = $this->adapter->has($this->testFilePath);
        $this->assertFalse($exists, 'File was not deleted from Azure Blob Storage.');
    }
}
