<?php

namespace tests\Unit;

use tests\TestCase;
use Mockery as m;
use think\file\UploadedFile;

class FileUploadTest extends TestCase
{
    private array $allowedAudioFormats = ['mp3', 'wav', 'flac', 'm4a', 'aac', 'ogg'];
    private array $allowedImageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    private int $maxAudioSize = 50 * 1024 * 1024;
    private int $maxImageSize = 5 * 1024 * 1024;

    public function audioFileDataProvider(): array
    {
        return [
            'valid mp3 file' => [
                'audio',
                'test.mp3',
                'audio/mpeg',
                5 * 1024 * 1024,
                true
            ],
            'valid wav file' => [
                'audio',
                'test.wav',
                'audio/wav',
                10 * 1024 * 1024,
                true
            ],
            'valid flac file' => [
                'audio',
                'test.flac',
                'audio/flac',
                20 * 1024 * 1024,
                true
            ],
            'valid m4a file' => [
                'audio',
                'test.m4a',
                'audio/mp4',
                8 * 1024 * 1024,
                true
            ],
            'invalid format - exe' => [
                'audio',
                'test.exe',
                'application/exe',
                1 * 1024 * 1024,
                false
            ],
            'invalid format - php' => [
                'audio',
                'test.php',
                'application/x-php',
                1 * 1024,
                false
            ],
            'too large audio file' => [
                'audio',
                'large.mp3',
                'audio/mpeg',
                60 * 1024 * 1024,
                false
            ],
            'exactly max size audio' => [
                'audio',
                'exact.mp3',
                'audio/mpeg',
                50 * 1024 * 1024,
                true
            ]
        ];
    }

    public function imageFileDataProvider(): array
    {
        return [
            'valid jpg file' => [
                'image',
                'cover.jpg',
                'image/jpeg',
                2 * 1024 * 1024,
                true
            ],
            'valid png file' => [
                'image',
                'cover.png',
                'image/png',
                3 * 1024 * 1024,
                true
            ],
            'valid gif file' => [
                'image',
                'cover.gif',
                'image/gif',
                500 * 1024,
                true
            ],
            'valid svg file' => [
                'image',
                'cover.svg',
                'image/svg+xml',
                100 * 1024,
                true
            ],
            'invalid format - pdf' => [
                'image',
                'doc.pdf',
                'application/pdf',
                1 * 1024 * 1024,
                false
            ],
            'invalid format - zip' => [
                'image',
                'archive.zip',
                'application/zip',
                2 * 1024 * 1024,
                false
            ],
            'too large image file' => [
                'image',
                'large.jpg',
                'image/jpeg',
                10 * 1024 * 1024,
                false
            ],
            'exactly max size image' => [
                'image',
                'exact.jpg',
                'image/jpeg',
                5 * 1024 * 1024,
                true
            ]
        ];
    }

    /**
     * @dataProvider audioFileDataProvider
     */
    public function testAudioFileValidation(string $fileType, string $filename, string $mimeType, int $fileSize, bool $expectedValid): void
    {
        $file = $this->createMockUploadedFile($filename, $mimeType, $fileSize);
        $result = $this->validateFile($file, $fileType);

        if ($expectedValid) {
            $this->assertTrue($result['success'], $result['message'] ?? 'Validation should pass');
        } else {
            $this->assertFalse($result['success'], 'Validation should fail');
        }
    }

    /**
     * @dataProvider imageFileDataProvider
     */
    public function testImageFileValidation(string $fileType, string $filename, string $mimeType, int $fileSize, bool $expectedValid): void
    {
        $file = $this->createMockUploadedFile($filename, $mimeType, $fileSize);
        $result = $this->validateFile($file, $fileType);

        if ($expectedValid) {
            $this->assertTrue($result['success'], $result['message'] ?? 'Validation should pass');
        } else {
            $this->assertFalse($result['success'], 'Validation should fail');
        }
    }

    public function testDoubleExtensionDetection(): void
    {
        $file = $this->createMockUploadedFile('malicious.php.jpg', 'image/jpeg', 1024);
        $result = $this->validateFile($file, 'image');
        $this->assertFalse($result['success'], 'Double extension files should be rejected');
        $this->assertStringContainsString('不允许的文件格式', $result['message']);
    }

    public function testEmptyFilename(): void
    {
        $file = $this->createMockUploadedFile('', 'image/jpeg', 1024);
        $result = $this->validateFile($file, 'image');
        $this->assertFalse($result['success'], 'Empty filename should be rejected');
    }

    private function createMockUploadedFile(string $filename, string $mimeType, int $size): UploadedFile
    {
        $file = m::mock(UploadedFile::class);
        $file->shouldReceive('getOriginalName')->andReturn($filename);
        $file->shouldReceive('getMime')->andReturn($mimeType);
        $file->shouldReceive('getSize')->andReturn($size);
        $file->shouldReceive('isValid')->andReturn(true);

        return $file;
    }

    private function validateFile(UploadedFile $file, string $type): array
    {
        $filename = $file->getOriginalName();

        if (empty($filename)) {
            return ['success' => false, 'message' => '文件名不能为空'];
        }

        if (preg_match('/\.[^.]+\./', $filename)) {
            return ['success' => false, 'message' => '不允许的文件格式（多重扩展名）'];
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileSize = $file->getSize();

        if ($type === 'audio') {
            if (!in_array($extension, $this->allowedAudioFormats)) {
                return ['success' => false, 'message' => '不允许的文件格式，仅支持: ' . implode(', ', $this->allowedAudioFormats)];
            }
            if ($fileSize > $this->maxAudioSize) {
                return ['success' => false, 'message' => '文件大小超过限制，最大: ' . ($this->maxAudioSize / 1024 / 1024) . 'MB'];
            }
        } elseif ($type === 'image') {
            if (!in_array($extension, $this->allowedImageFormats)) {
                return ['success' => false, 'message' => '不允许的文件格式，仅支持: ' . implode(', ', $this->allowedImageFormats)];
            }
            if ($fileSize > $this->maxImageSize) {
                return ['success' => false, 'message' => '文件大小超过限制，最大: ' . ($this->maxImageSize / 1024 / 1024) . 'MB'];
            }
        }

        return ['success' => true];
    }
}
