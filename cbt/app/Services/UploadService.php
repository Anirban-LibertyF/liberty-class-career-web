<?php
namespace App\Services;

use App\Core\HttpException;

final class UploadService
{
    private const INPUT_MAX_BYTES = 10485760;
    private const OUTPUT_MAX_BYTES = 1048576;

    public function image(array $file, string $folder): string
    {
        if (!in_array($folder, ['profiles', 'questions', 'tests'], true)) {
            throw new HttpException(422, 'Invalid upload destination.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new HttpException(422, 'Image upload failed.');
        }
        if ((int) ($file['size'] ?? 0) > self::INPUT_MAX_BYTES) {
            throw new HttpException(422, 'Image must be 10 MB or smaller.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new HttpException(422, 'Only JPEG, JPG or PNG is allowed.');
        }
        $src = match ($mime) {
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default => @imagecreatefromjpeg($file['tmp_name']),
        };
        if (!$src) {
            throw new HttpException(422, 'Invalid image.');
        }

        $width = imagesx($src);
        $height = imagesy($src);
        if ($width < 1 || $height < 1) {
            throw new HttpException(422, 'Invalid image dimensions.');
        }

        $encoded = $this->optimizedWebp($src, $width, $height);
        if ($encoded === null) {
            throw new HttpException(422, 'Image could not be compressed below 1 MB.');
        }

        $name = bin2hex(random_bytes(16)) . '.webp';
        $dir = dirname(__DIR__, 2) . '/storage/uploads/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new HttpException(500, 'Upload storage is unavailable.');
        }
        if (file_put_contents($dir . '/' . $name, $encoded, LOCK_EX) === false) {
            throw new HttpException(500, 'Image could not be stored.');
        }
        return $folder . '/' . $name;
    }

    public function dataImage(string $dataUrl, string $folder): string
    {
        if (!preg_match('#^data:(image/(?:jpeg|png|webp));base64,([A-Za-z0-9+/=\r\n]+)$#', $dataUrl, $matches)) {
            throw new HttpException(422, 'Thumbnail must be a JPEG, PNG or WebP data URL.');
        }
        if (strlen($matches[2]) > (self::INPUT_MAX_BYTES * 4 / 3) + 8) {
            throw new HttpException(422, 'Image must be 10 MB or smaller.');
        }
        $bytes = base64_decode($matches[2], true);
        if ($bytes === false || strlen($bytes) > self::INPUT_MAX_BYTES) {
            throw new HttpException(422, 'Invalid thumbnail image data.');
        }
        $temporary = tempnam(sys_get_temp_dir(), 'lcc-image-');
        if ($temporary === false || file_put_contents($temporary, $bytes, LOCK_EX) === false) {
            throw new HttpException(500, 'Thumbnail could not be processed.');
        }
        try {
            return $this->image([
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($bytes),
                'tmp_name' => $temporary,
            ], $folder);
        } finally {
            @unlink($temporary);
        }
    }

    public function delete(string $relativePath): void
    {
        if (!preg_match('#^(profiles|questions|tests)/[a-f0-9]{32}\\.webp$#', $relativePath)) {
            return;
        }
        $path = dirname(__DIR__, 2) . '/storage/uploads/' . $relativePath;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function optimizedWebp(\GdImage $src, int $width, int $height): ?string
    {
        $scale = min(1, 2200 / max($width, $height));
        for ($pass = 0; $pass < 10; $pass++) {
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            ob_start();
            imagewebp($dst, null, max(70, 90 - ($pass * 2)));
            $data = (string) ob_get_clean();
            if ($data !== '' && strlen($data) <= self::OUTPUT_MAX_BYTES) {
                return $data;
            }
            $scale *= 0.86;
        }
        return null;
    }
}
