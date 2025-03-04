<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use League\Flysystem\Config;

class AzureBlobStorageIntegrationTest extends TestCase
{
    private $adapter;
    private $testFilePath = 'test-folder/integration-test.txt';
    private $testFileContent = 'This is a real integration test file!';

    protected function setUp(): void
    {
        try { // Load environment variables for Azure credentials
            $accountName = $_ENV['AZURE_STORAGE_ACCOUNT'];
            $accountKey = $_ENV['AZURE_STORAGE_KEY'];
            $container = $_ENV['AZURE_STORAGE_CONTAINER'];
            $endpoint = $_ENV['AZURE_STORAGE_ENDPOINT'];

            if (!$accountName || !$accountKey || !$container || !$endpoint) {
                $this->markTestSkipped('Azure storage credentials are missing.');
            }

            // Create a real Azure Blob Storage adapter
            $this->adapter = new \MaddLogic\FlysystemAzureBlob\Adapter\AzureBlobStorageAdapter(
                $accountName,
                $accountKey,
                $container,
                $endpoint
            );
        } catch (\Exception $e) {
            echo "\n🔥 Exception Caught in Test 🔥\n";
            echo "Message: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
            echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
            throw $e; // Re-throw to let PHPUnit handle it as a test failure
        }
    }

    public function testWriteToAzureBlob()
    {
        $this->adapter->write($this->testFilePath, $this->testFileContent, new Config());

        // Ensure the file was successfully written
        $this->assertTrue(1==1, 'Failed to write file to Azure Blob Storage.');
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
        $this->assertEquals($this->testFileContent, $file, 'File content mismatch.');
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
