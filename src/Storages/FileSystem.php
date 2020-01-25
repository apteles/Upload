<?php
declare(strict_types=1);
namespace Apteles\Upload\Storage;

use Exception;
use InvalidArgumentException;
use Apteles\Upload\Contracts\StorageInterface;
use Apteles\Upload\Contracts\FileInfoInterface;

class FileSystem implements StorageInterface
{
    private $directory;

    private $overwrite;

    public function __construct(string $directory, bool $overwrite = false)
    {
        $this->init($directory, $overwrite);
    }

    public function init(string $directory, bool $overwrite = false): void
    {
        if (!\is_dir($directory)) {
            throw new InvalidArgumentException("Directory is not valid");
        }

        if (!\is_writable($directory)) {
            throw new InvalidArgumentException("Directory is nor writable");
        }

        $this->directory = \rtrim($directory, '/') . DIRECTORY_SEPARATOR;
        $this->overwrite = $overwrite;
    }

    public function upload(FileInfoInterface $file): bool
    {
        $destination = "{$this->directory}{$file->getNameWithExtension()}";

        if (!$this->overwrite && \file_exists($destination)) {
            throw new Exception("File already exists");
        }

        if (!$this->moveUploadedFile($file->getPathname(), $destination)) {
            throw new Exception("File could not be moved to final destination.");
        }
        return true;
    }

    public function moveUploadedFile(string $source, string $destination): bool
    {
        return \move_uploaded_file($source, $destination);
    }
}
