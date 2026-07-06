<?php

namespace App\Services;

use Core\Constants\Constants;
use Core\Database\ActiveRecord\Model;

class FoodImage
{
    /** @var array<string, mixed> $image */
    private array $image;

    public function __construct(
        private Model $model,
    ) {
    }

    public function path(): string
    {
        if ($this->model->photo_url) {
            $hash = md5_file($this->getAbsoluteSavedFilePath());
            return $this->baseDir() . $this->model->photo_url . '?' . $hash;
        }

        return '/assets/images/defaults/food.svg';
    }

    /**
     * @param array<string, mixed> $image
     */
    public function upload(array $image): bool
    {
        $this->image = $image;

        if ($this->model->errors('imageFile')) {
            return false;
        }

        if ($this->uploadFile()) {
            $result = $this->model->update([
                'photo_url' => $this->getFileName(),
            ]);

            return true;
        }

        return false;
    }

    public function remove(): void
    {
        if ($this->model->photo_url) {
            $this->removeImage();
            $this->removeDir();
            $this->model->update(['photo_url' => null]);
        }
    }

    /**
     * @param array<string, mixed> $image
     */
    public function hasValidMimeType(array $image): bool
    {
        if (empty($image['tmp_name']) || $image['error'] !== UPLOAD_ERR_OK) {
            return true;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $image['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg',
            'image/png',
        ];

        return in_array($mimeType, $allowedMimes);
    }

    private function removeDir(): void
    {
        $dir = $this->storeDir();
        if (is_dir($dir) && count(scandir($dir)) === 2) {
            rmdir($dir);
        }
    }

    protected function uploadFile(): bool
    {
        if (empty($this->getTmpFilePath())) {
            return false;
        }

        $this->removeImage();

        $destination = $this->getAbsoluteDestinationPath();

        $resp = move_uploaded_file(
            $this->getTmpFilePath(),
            $destination
        );

        if (!$resp) {
            $error = error_get_last();
            throw new \RuntimeException(
                'Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error')
            );
        }

        $this->createThumbnail($destination);

        return true;
    }

    private function createThumbnail(string $sourcePath): void
    {
        $info = getimagesize($sourcePath);
        $mimeType = $info['mime'];

        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => imagecreatefrompng($sourcePath),
            default      => null,
        };

        if (!$image) {
            return;
        }

        $maxWidth = 300;
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $ratio = $maxWidth / $originalWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) ($originalHeight * $ratio);

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $thumbnailPath = $this->storeDir() . 'thumb_' . $this->getFileName();

        match ($mimeType) {
            'image/jpeg' => imagejpeg($thumbnail, $thumbnailPath, 80),
            'image/png'  => imagepng($thumbnail, $thumbnailPath),
            default      => null,
        };

        imagedestroy($image);
        imagedestroy($thumbnail);
    }

    private function getTmpFilePath(): string
    {
        return $this->image['tmp_name'];
    }

    private function removeImage(): void
    {
        if ($this->model->photo_url) {
            unlink($this->getAbsoluteSavedFilePath());

            $thumbPath = $this->storeDir() . 'thumb_' . $this->model->photo_url;
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
        }
    }

    private function getFileName(): string
    {
        $file_name_splitted = explode('.', $this->image['name']);
        $file_extension = end($file_name_splitted);

        $file_extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $file_extension));
        return $this->model->uuid . '.' . $file_extension;
    }

    private function getAbsoluteDestinationPath(): string
    {
        return $this->storeDir() . $this->getFileName();
    }

    private function baseDir(): string
    {
        return "/assets/uploads/{$this->model::table()}/{$this->model->id}/";
    }

    private function storeDir(): string
    {
        $path = Constants::rootPath()->join('public' . $this->baseDir());
        if (!is_dir($path)) {
            mkdir(directory: $path, recursive: true);
        }

        return $path;
    }

    private function getAbsoluteSavedFilePath(): string
    {
        return Constants::rootPath()->join('public' . $this->baseDir())->join($this->model->photo_url);
    }

    public function thumbnailPath(): string
    {
        if ($this->model->photo_url) {
            $thumbPath = $this->storeDir() . 'thumb_' . $this->model->photo_url;
            if (file_exists($thumbPath)) {
                $hash = md5_file($thumbPath);
                return $this->baseDir() . 'thumb_' . $this->model->photo_url . '?' . $hash;
            }
        }

        return '/assets/images/defaults/food.svg';
    }
}
