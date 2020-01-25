<?php
declare(strict_types=1);
namespace Apteles\Upload\Contracts;

interface FileInfoInterface
{
    public function getPathname();

    public function getName(): string;

    public function setName(string $name): FileInfoInterface;

    public function getExtension(): string;

    public function setExtension(string $extension):FileInfoInterface;

    public function getNameWithExtension(): string;

    public function getMimetype(): string;

    public function getSize();

    public function getMd5(): string;

    public function gethash(string $algo = 'md5'): string;

    public function getDimensions();

    public function isUploadedFile();
}
