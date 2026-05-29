<?php

namespace App\Services;

use Core\Constants\Constants;
use Core\Database\ActiveRecord\Model;

class FoodImage
{
    /** @var array<string, mixed> $image */
    private array $image;

    /** @param array<string, mixed> $validations */
    public function __construct(
        private Model $model,
        private array $validations = [
            'extension' => ['jpg', 'jpeg', 'png'],
            'size' => 5 * 1024 * 1024
        ]
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

        if (!$this->isValidImage()) {
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

        $resp = move_uploaded_file(
            $this->getTmpFilePath(),
            $this->getAbsoluteDestinationPath()
        );

        if (!$resp) {
            $error = error_get_last();
            throw new \RuntimeException(
                'Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error')
            );
        }

        return true;
    }

    private function getTmpFilePath(): string
    {
        return $this->image['tmp_name'];
    }

    private function removeImage(): void
    {
        if ($this->model->photo_url) {
            unlink($this->getAbsoluteSavedFilePath());
        }
    }

    private function getFileName(): string
    {
        $file_name_splitted = explode('.', $this->image['name']);
        $file_extension = end($file_name_splitted);
        return 'food.' . $file_extension;
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

    private function isValidImage(): bool
    {
        if (isset($this->validations['extension'])) {
            $this->validateImageExtension();
        }

        if (isset($this->validations['size'])) {
            $this->validateImageSize();
        }

        return $this->model->errors('photo') === null;
    }

    private function validateImageExtension(): void
    {
        $file_name_splitted = explode('.', $this->image['name']);
        $file_extension = end($file_name_splitted);

        if (!in_array($file_extension, $this->validations['extension'])) {
            $this->model->addError('photo', 'Extensão de arquivo inválida!');
        }
    }

    private function validateImageSize(): void
    {
        if ($this->image['size'] > $this->validations['size']) {
            $this->model->addError('photo', 'Tamanho do arquivo excede o limite permitido!');
        }
    }

    /** @param array<string, mixed> $image */
    public function validate(array $image): void
    {
        $this->image = $image;
        $this->isValidImage();
    }
}
