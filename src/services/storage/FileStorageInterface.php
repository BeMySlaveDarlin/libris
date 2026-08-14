<?php

declare(strict_types=1);

namespace app\services\storage;

use yii\web\UploadedFile;

interface FileStorageInterface
{
    public function save(UploadedFile $file): string;

    public function saveContents(string $contents, string $extension): string;

    public function delete(?string $path): void;

    public function url(?string $path): ?string;
}
