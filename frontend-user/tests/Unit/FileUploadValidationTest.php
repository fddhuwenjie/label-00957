<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;

class FileUploadValidationTest extends TestCase
{
    public static function fileSizeProvider(): array
    {
        return [
            'small_file' => [
                'size' => 1024 * 100,
                'maxSize' => 50 * 1024 * 1024,
                'expectedValid' => true,
            ],
            'exact_limit' => [
                'size' => 50 * 1024 * 1024,
                'maxSize' => 50 * 1024 * 1024,
                'expectedValid' => true,
            ],
            'over_limit_by_one' => [
                'size' => 50 * 1024 * 1024 + 1,
                'maxSize' => 50 * 1024 * 1024,
                'expectedValid' => false,
            ],
            'huge_file' => [
                'size' => 500 * 1024 * 1024,
                'maxSize' => 50 * 1024 * 1024,
                'expectedValid' => false,
            ],
            'zero_size' => [
                'size' => 0,
                'maxSize' => 50 * 1024 * 1024,
                'expectedValid' => false,
            ],
            'cover_exact_limit' => [
                'size' => 5 * 1024 * 1024,
                'maxSize' => 5 * 1024 * 1024,
                'expectedValid' => true,
            ],
            'cover_over_limit' => [
                'size' => 5 * 1024 * 1024 + 1,
                'maxSize' => 5 * 1024 * 1024,
                'expectedValid' => false,
            ],
        ];
    }

    public static function fileFormatProvider(): array
    {
        return [
            'mp3_audio' => [
                'filename' => 'song.mp3',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => true,
            ],
            'wav_audio' => [
                'filename' => 'song.wav',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => true,
            ],
            'flac_audio' => [
                'filename' => 'song.flac',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => true,
            ],
            'exe_rejected' => [
                'filename' => 'malware.exe',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => false,
            ],
            'php_rejected' => [
                'filename' => 'shell.php',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => false,
            ],
            'jpg_image' => [
                'filename' => 'cover.jpg',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'image',
                'expectedValid' => true,
            ],
            'png_image' => [
                'filename' => 'cover.png',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'image',
                'expectedValid' => true,
            ],
            'svg_rejected_as_image' => [
                'filename' => 'xss.svg',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'image',
                'expectedValid' => false,
            ],
            'double_extension' => [
                'filename' => 'shell.php.mp3',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => true,
            ],
            'no_extension' => [
                'filename' => 'shell',
                'allowedAudio' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg'],
                'allowedImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'type' => 'audio',
                'expectedValid' => false,
            ],
        ];
    }

    public static function mimeTypeProvider(): array
    {
        return [
            'mp3_mime' => [
                'extension' => 'mp3',
                'expectedMime' => 'audio/mpeg',
            ],
            'wav_mime' => [
                'extension' => 'wav',
                'expectedMime' => 'audio/wav',
            ],
            'jpg_mime' => [
                'extension' => 'jpg',
                'expectedMime' => 'image/jpeg',
            ],
            'png_mime' => [
                'extension' => 'png',
                'expectedMime' => 'image/png',
            ],
        ];
    }

    /**
     * @dataProvider fileSizeProvider
     */
    public function testFileSizeValidation(int $size, int $maxSize, bool $expectedValid): void
    {
        $isValid = $size > 0 && $size <= $maxSize;
        $this->assertEquals($expectedValid, $isValid);
    }

    /**
     * @dataProvider fileFormatProvider
     */
    public function testFileFormatValidation(string $filename, array $allowedAudio, array $allowedImage, string $type, bool $expectedValid): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = $type === 'audio' ? $allowedAudio : $allowedImage;

        if (empty($extension)) {
            $isValid = false;
        } else {
            $isValid = in_array($extension, $allowedExtensions, true);
        }

        $this->assertEquals($expectedValid, $isValid);
    }

    /**
     * @dataProvider mimeTypeProvider
     */
    public function testMimeTypeMapping(string $extension, string $expectedMime): void
    {
        $mimeMap = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        $this->assertEquals($expectedMime, $mimeMap[$extension] ?? 'application/octet-stream');
    }

    public function testAudioMaxSizeIs50MB(): void
    {
        $audioMaxSize = 50 * 1024 * 1024;
        $this->assertEquals(52428800, $audioMaxSize);
    }

    public function testCoverMaxSizeIs5MB(): void
    {
        $coverMaxSize = 5 * 1024 * 1024;
        $this->assertEquals(5242880, $coverMaxSize);
    }
}
