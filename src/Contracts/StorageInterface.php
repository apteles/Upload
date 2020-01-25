<?php
declare(strict_types=1);
namespace Apteles\Upload\Contracts;

interface StorageInterface
{
    public function upload(FileInfoInterface $file): bool;
}
