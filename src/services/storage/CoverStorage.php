<?php

declare(strict_types=1);

namespace app\services\storage;

use Carbon\CarbonImmutable;
use RuntimeException;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

final readonly class CoverStorage implements FileStorageInterface
{
    public function __construct(
        private string $basePath,
        private string $baseUrl,
    ) {
    }

    public function save(UploadedFile $file): string
    {
        $relative = CarbonImmutable::now()->format('Y/m');
        $directory = $this->basePath . '/' . $relative;
        FileHelper::createDirectory($directory);

        $name = bin2hex(random_bytes(16)) . '.' . $file->getExtension();
        if (!$file->saveAs($directory . '/' . $name)) {
            throw new RuntimeException('Не удалось сохранить файл обложки.');
        }

        return $relative . '/' . $name;
    }

    public function saveContents(string $contents, string $extension): string
    {
        $relative = CarbonImmutable::now()->format('Y/m');
        $directory = $this->basePath . '/' . $relative;
        FileHelper::createDirectory($directory);

        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        if (file_put_contents($directory . '/' . $name, $contents) === false) {
            throw new RuntimeException('Не удалось сохранить файл обложки.');
        }

        return $relative . '/' . $name;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $absolute = $this->basePath . '/' . $path;
        if (is_file($absolute)) {
            unlink($absolute);
        }
    }

    public function url(?string $path): ?string
    {
        return $path === null || $path === '' ? null : $this->baseUrl . '/' . $path;
    }
}
