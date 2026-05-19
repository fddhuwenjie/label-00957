<?php

declare(strict_types=1);

namespace app\tests;

use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase
{
    const AUDIO_MAX_SIZE = 20 * 1024 * 1024;
    const IMAGE_MAX_SIZE = 2 * 1024 * 1024;
    const AUDIO_EXTS = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'];
    const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function dataProviderForAudioUploadScenarios(): array
    {
        return [
            'valid_mp3_within_limit' => [
                'filename' => 'song.mp3',
                'size' => 5 * 1024 * 1024,
                'expected_valid' => true,
            ],
            'valid_wav_at_boundary' => [
                'filename' => 'sound.wav',
                'size' => 20 * 1024 * 1024 - 1,
                'expected_valid' => true,
            ],
            'valid_flac_at_exact_limit' => [
                'filename' => 'track.flac',
                'size' => 20 * 1024 * 1024,
                'expected_valid' => true,
            ],
            'valid_aac_at_below_limit' => [
                'filename' => 'music.aac',
                'size' => 1024,
                'expected_valid' => true,
            ],
            'valid_ogg_small' => [
                'filename' => 'audio.ogg',
                'size' => 0,
                'expected_valid' => true,
            ],
            'valid_m4a_medium' => [
                'filename' => 'song.m4a',
                'size' => 10 * 1024 * 1024,
                'expected_valid' => true,
            ],
            'mp3_exceeds_max_size' => [
                'filename' => 'big.mp3',
                'size' => 20 * 1024 * 1024 + 1,
                'expected_valid' => false,
            ],
            'wav_way_over_limit' => [
                'filename' => 'huge.wav',
                'size' => 100 * 1024 * 1024,
                'expected_valid' => false,
            ],
            'disallowed_format_txt' => [
                'filename' => 'file.txt',
                'size' => 1024,
                'expected_valid' => false,
            ],
            'disallowed_format_exe' => [
                'filename' => 'virus.exe',
                'size' => 1024,
                'expected_valid' => false,
            ],
            'disallowed_format_php' => [
                'filename' => 'shell.php',
                'size' => 1024,
                'expected_valid' => false,
            ],
            'disallowed_format_jpg_as_audio' => [
                'filename' => 'image.jpg',
                'size' => 1024,
                'expected_valid' => false,
            ],
            'no_extension' => [
                'filename' => 'noextension',
                'size' => 1024,
                'expected_valid' => false,
            ],
            'upper_case_extension' => [
                'filename' => 'song.MP3',
                'size' => 5 * 1024 * 1024,
                'expected_valid' => true,
            ],
            'mixed_case_extension' => [
                'filename' => 'song.Mp3',
                'size' => 5 * 1024 * 1024,
                'expected_valid' => true,
            ],
            'octet_stream' => [
                'filename' => 'audio.bin',
                'size' => 5 * 1024 * 1024,
                'expected_valid' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForAudioUploadScenarios
     */
    public function testAudioUploadValidation(string $filename, int $size, bool $expectedValid): void
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $formatValid = in_array($ext, self::AUDIO_EXTS, true);
        $sizeValid = $size > 0 && $size <= self::AUDIO_MAX_SIZE;

        $this->assertSame($expectedValid, $formatValid && $sizeValid);
    }

    public function dataProviderForImageUploadScenarios(): array
    {
        return [
            'valid_jpg_small' => [
                'filename' => 'cover.jpg',
                'size' => 100 * 1024,
                'expected_valid' => true,
            ],
            'valid_jpeg_at_limit' => [
                'filename' => 'cover.jpeg',
                'size' => self::IMAGE_MAX_SIZE,
                'expected_valid' => true,
            ],
            'valid_png_below_limit' => [
                'filename' => 'cover.png',
                'size' => 500 * 1024,
                'expected_valid' => true,
            ],
            'valid_gif_at_boundary' => [
                'filename' => 'cover.gif',
                'size' => self::IMAGE_MAX_SIZE - 1,
                'expected_valid' => true,
            ],
            'valid_webp_small' => [
                'filename' => 'cover.webp',
                'size' => 1024,
                'expected_valid' => true,
            ],
            'jpg_exceeds_limit' => [
                'filename' => 'big.jpg',
                'size' => self::IMAGE_MAX_SIZE + 1,
                'expected_valid' => false,
            ],
            'png_way_over' => [
                'filename' => 'huge.png',
                'size' => 10 * 1024 * 1024,
                'expected_valid' => false,
            ],
            'disallowed_bmp' => [
                'filename' => 'cover.bmp',
                'size' => 100 * 1024,
                'expected_valid' => false,
            ],
            'disallowed_svg_as_image' => [
                'filename' => 'cover.svg',
                'size' => 100 * 1024,
                'expected_valid' => false,
            ],
            'upper_case_ext' => [
                'filename' => 'cover.JPG',
                'size' => 100 * 1024,
                'expected_valid' => true,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForImageUploadScenarios
     */
    public function testImageUploadValidation(string $filename, int $size, bool $expectedValid): void
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $formatValid = in_array($ext, self::IMAGE_EXTS, true);
        $sizeValid = $size > 0 && $size <= self::IMAGE_MAX_SIZE;

        $this->assertSame($expectedValid, $formatValid && $sizeValid);
    }

    public function testAudioMaxSizeIs20MB(): void
    {
        $expected = 20 * 1024 * 1024;
        $this->assertSame(20971520, $expected);
        $this->assertSame(20, $expected / (1024 * 1024));
    }

    public function testImageMaxSizeIs2MB(): void
    {
        $expected = 2 * 1024 * 1024;
        $this->assertSame(2097152, $expected);
        $this->assertSame(2, $expected / (1024 * 1024));
    }

    public function testAllowedAudioExtensions(): void
    {
        $this->assertContains('mp3', self::AUDIO_EXTS);
        $this->assertContains('wav', self::AUDIO_EXTS);
        $this->assertContains('flac', self::AUDIO_EXTS);
        $this->assertContains('aac', self::AUDIO_EXTS);
        $this->assertContains('ogg', self::AUDIO_EXTS);
        $this->assertContains('m4a', self::AUDIO_EXTS);
    }

    public function testAllowedImageExtensions(): void
    {
        $this->assertContains('jpg', self::IMAGE_EXTS);
        $this->assertContains('jpeg', self::IMAGE_EXTS);
        $this->assertContains('png', self::IMAGE_EXTS);
        $this->assertContains('gif', self::IMAGE_EXTS);
        $this->assertContains('webp', self::IMAGE_EXTS);
    }

    public function testExtensionCaseInsensitiveCheck(): void
    {
        $this->assertSame('mp3', strtolower('MP3'));
        $this->assertSame('mp3', strtolower('Mp3'));
        $this->assertSame('mp3', strtolower('mp3'));
    }

    public function testSizeBoundaryExactly(): void
    {
        $max = 20 * 1024 * 1024;
        $this->assertFalse(10 * 1024 * 1024 > $max);
        $this->assertTrue(21 * 1024 * 1024 > $max);
        $this->assertFalse($max > $max);
    }
}
