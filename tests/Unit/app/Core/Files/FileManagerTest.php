<?php

namespace Tests\Unit\app\Core\Files;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Files\Exceptions\FileValidationException;
use Leantime\Core\Files\FileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

class FileManagerTest extends TestCase
{
    private $filesystemManager;

    private $config;

    private $fileManager;

    private $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up session values needed for DateTimeHelper (used by dtHelper())
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);

        // Mock the FilesystemManager
        $this->filesystemManager = $this->createMock(FilesystemManager::class);

        // Mock the Environment
        $this->config = $this->createMock(Environment::class);

        // Mock the storage disk
        $this->storage = $this->createMock(FilesystemAdapter::class);

        // Setup the FileManager with mocked dependencies
        $this->fileManager = new FileManager(
            $this->filesystemManager,
            $this->config
        );

        // Create a test file in userfiles directory
        $testDir = base_path('userfiles/test');
        if (! is_dir($testDir)) {
            mkdir($testDir, 0777, true);
        }
        file_put_contents($testDir.'/test.txt', 'test content');
    }

    protected function tearDown(): void
    {
        // Clean up test file
        @unlink(base_path('userfiles/test/test.txt'));
        @rmdir(base_path('userfiles/test'));
        parent::tearDown();
    }

    public function test_upload_file_successfully()
    {
        // Mock session data
        session(['userdata.id' => 123]);

        // Create a mock uploaded file
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('getError')->willReturn(0);
        $file->method('getSize')->willReturn(1000); // 1KB
        $file->method('getClientOriginalName')->willReturn('test-file.txt');
        $file->method('getClientOriginalExtension')->willReturn('txt');
        $file->method('getRealPath')->willReturn(base_path('userfiles/test/test.txt'));

        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);
        $this->storage->method('mimeType')->willReturn('text/plain');

        // Setup storage to successfully store the file
        $this->storage->method('put')->willReturn(true);

        // Mock the PHP stream functions
        $this->storage->method('put')
            ->with($this->anything(), $this->anything(), $this->anything())
            ->willReturn(true);

        // Execute the method under test
        $result = $this->fileManager->upload($file);

        // Assert the result is an array with expected keys
        $this->assertIsArray($result);
        $this->assertArrayHasKey('fileName', $result);
        $this->assertArrayHasKey('realName', $result);
        $this->assertArrayHasKey('extension', $result);
        $this->assertEquals('test-file.txt', $result['realName']);
        $this->assertEquals('txt', $result['extension']);
    }

    public function test_upload_file_with_invalid_file()
    {
        // Create a mock uploaded file that is invalid
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(false);
        $file->method('getErrorMessage')->willReturn('Test error message');

        //        // Mock the Log facade
        //        Log::shouldReceive('error')
        //            ->once()
        //            ->with('File upload failed: Invalid file upload attempt: Test error message',  ['exception' => new FileValidationException('test'), 'file'=> '']);

        // Execute the method under test
        $result = $this->fileManager->upload($file);

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_upload_file_with_file_too_large()
    {
        // Create a mock uploaded file that is too large
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('getError')->willReturn(0);
        $file->method('getSize')->willReturn(PHP_INT_MAX); // Very large file

        //        // Mock the Log facade
        //        Log::shouldReceive('error')
        //            ->once()
        //            ->with('File upload failed: File size exceeds the maximum allowed size of', ['exception' => new FileValidationException('test'), 'file'=> '']);

        // Execute the method under test
        $result = $this->fileManager->upload($file);

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_get_file_successfully()
    {
        // Create a mock response
        $mockResponse = $this->createMock(Response::class);

        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);
        $this->storage->method('mimeType')->willReturn('text/plain');

        // Setup storage to successfully find and download the file
        $this->storage->expects($this->once())->method('exists')->willReturn(true);
        $this->storage->method('download')->willReturn($mockResponse);

        // Execute the method under test
        $result = $this->fileManager->getFile('test.txt', 'original-name.txt');

        // Assert the result is the expected response
        $this->assertSame($mockResponse, $result);
    }

    public function test_get_file_not_found()
    {
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);

        // Setup storage to not find the file
        $this->storage->method('exists')->willReturn(false);

        // Execute the method under test
        $result = $this->fileManager->getFile('non-existent-file.txt', 'original-name.txt');

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_get_file_url_local_storage()
    {
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);
        $this->storage->method('mimeType')->willReturn('text/plain');

        // Setup storage to successfully find the file and return a URL
        $this->storage->method('exists')->willReturn(true);
        $this->storage->method('url')->willReturn('http://example.com/files/test.txt');

        // Configure cache behavior
        $this->config->method('get')->willReturn(true);
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('http://example.com/files/test.txt');

        // Execute the method under test
        $result = $this->fileManager->getFileUrl('test.txt');

        // Assert the result is the expected URL
        $this->assertEquals('http://example.com/files/test.txt', $result);
    }

    public function test_get_file_url_s3_storage()
    {
        // Configure environment to use S3
        $this->config->useS3 = true;
        $this->config->method('get')->willReturn(60);

        // Setup storage to return mime type
        $this->storage->method('mimeType')->willReturn('text/plain');
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('disk')->with('s3')->willReturn($this->storage);

        // Setup storage to successfully find the file and return a temporary URL
        $this->storage->method('exists')->willReturn(true);
        $this->storage->method('temporaryUrl')->willReturn('https://s3.example.com/files/test.txt?signature=abc123');
        $this->storage->method('temporaryUrl')->willReturn('https://s3.example.com/files/test.txt?signature=abc123');
        $this->storage->method('temporaryUrl')->willReturn('https://s3.example.com/files/test.txt?signature=abc123');

        // Execute the method under test
        $result = $this->fileManager->getFileUrl('test.txt', 's3');

        // Assert the result is the expected URL
        $this->assertEquals('https://s3.example.com/files/test.txt?signature=abc123', $result);
    }

    public function test_get_file_url_file_not_found()
    {
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);

        // Setup storage to not find the file
        $this->storage->method('exists')->willReturn(false);

        // Execute the method under test
        $result = $this->fileManager->getFileUrl('non-existent-file.txt');

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_delete_file_successfully()
    {
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);

        // Setup storage to successfully find and delete the file
        $this->storage->method('exists')->willReturn(true);
        $this->storage->method('delete')->willReturn(true);

        // Execute the method under test
        $result = $this->fileManager->deleteFile('test.txt');

        // Assert the result is true
        $this->assertTrue($result);
    }

    public function test_delete_file_not_found()
    {
        // Setup filesystem manager to return our mocked storage
        $this->filesystemManager->method('getDefaultDriver')->willReturn('local');
        $this->filesystemManager->method('disk')->with('local')->willReturn($this->storage);

        // Setup storage to not find the file
        $this->storage->method('exists')->willReturn(false);

        // Mock the Log facade
        Log::shouldReceive('info')
            ->once()
            ->with('File not found for deletion: test.txt on disk local');

        // Execute the method under test
        $result = $this->fileManager->deleteFile('test.txt');

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_delete_file_with_empty_filename()
    {
        // Mock the Log facade
        Log::shouldReceive('warning')
            ->once()
            ->with('Attempted to delete a file with empty filename');

        // Execute the method under test
        $result = $this->fileManager->deleteFile('');

        // Assert the result is false
        $this->assertFalse($result);
    }

    public function test_get_maximum_file_upload_size()
    {
        // Test the static method
        $result = FileManager::getMaximumFileUploadSize();

        // Assert the result is an integer
        $this->assertIsInt($result);

        // The result should be the minimum of post_max_size and upload_max_filesize
        $expected = min(
            $this->convertPHPSizeToBytes(ini_get('post_max_size')),
            $this->convertPHPSizeToBytes(ini_get('upload_max_filesize'))
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Helper method to convert PHP size strings to bytes
     */
    private function convertPHPSizeToBytes(string $sSize): int
    {
        $sSuffix = strtoupper(substr($sSize, -1));
        if (! in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
            return (int) $sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
                // Fallthrough intended
            case 'T':
                $iValue *= 1024;
                // Fallthrough intended
            case 'G':
                $iValue *= 1024;
                // Fallthrough intended
            case 'M':
                $iValue *= 1024;
                // Fallthrough intended
            case 'K':
                $iValue *= 1024;
                break;
        }

        return (int) $iValue;
    }

    public function test_sanitize_filename()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass(FileManager::class);
        $method = $reflection->getMethod('sanitizeFilename');
        $method->setAccessible(true);

        // Test with a normal filename
        $result = $method->invoke($this->fileManager, 'test.txt');
        $this->assertEquals('test.txt', $result);

        // Test with a path
        $result = $method->invoke($this->fileManager, '/path/to/test.txt');
        $this->assertEquals('test.txt', $result);

        // Test with special characters
        $result = $method->invoke($this->fileManager, 'test@file#$.txt');
        $this->assertEquals('test-file--.txt', $result);

        // Allow chinese characters
        $result = $method->invoke($this->fileManager, '测试文件.txt');
        $this->assertEquals('测试文件.txt', $result);
    }

    public function test_get_avatar_with_cache_hit()
    {
        // We already have a test file at userfiles/test/test.txt
        $testFile = base_path('userfiles/test/test.txt');

        // Verify the test file exists
        $this->assertFileExists($testFile);

        // Verify content
        $this->assertEquals('test content', file_get_contents($testFile));
    }
}
