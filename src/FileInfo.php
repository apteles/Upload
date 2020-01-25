<?php
declare(strict_types=1);
namespace Apteles\Upload;

use finfo;
use SplFileInfo;
use Apteles\Upload\Contracts\FileInfoInterface;

class FileInfo extends SplFileInfo implements FileInfoInterface
{
    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $extension;

    /**
     *
     * @var string
     */
    protected $mimeType;

    /**
     *
     * @param string $filePathname
     * @param string $newName
     */
    public function __construct(string $filePathname, string $newName = null)
    {
        $this->init($filePathname, $newName);

        parent::__construct($filePathname);
    }

    /**
     *
     * @param string $filePathname
     * @param string $name
     * @return void
     */
    public function init(string $filePathname, string $name = null)
    {
        $desiredName = \is_null($name) ? $filePathname : $name;

        $this->setName(\pathinfo($desiredName, PATHINFO_FILENAME));
        $this->setExtension(\pathinfo($desiredName, PATHINFO_EXTENSION));
    }

    private function sureThatNameBeSafe(string $name)
    {
        return \preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})/", "", $name);
    }

    public function setName(string $name): FileInfoInterface
    {
        $name = \basename($this->sureThatNameBeSafe($name));
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): FileInfoInterface
    {
        $this->extension = \strtolower($extension);

        return $this;
    }

    public function getNameWithExtension(): string
    {
        if (!$this->extension) {
            return $this->name;
        }
        return \sprintf('%s.%s', $this->name, $this->extension);
    }

    public function getMimetype(): string
    {
        if (!$this->mimeType) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimeType = $finfo->file($this->getPathname());
            $this->mimeType = \strtolower(\preg_split('/\s*[;,]\s*/', $mimeType)[0]);
            unset($finfo);
        }

        return $this->mimeType;
    }

    public function getMd5(): string
    {
        return \md5_file($this->getPathname());
    }

    public function getHash($algo = 'md5'): string
    {
        return \hash_file($algo, $this->getPathname());
    }

    public function getDimensions()
    {
        [$width, $height] = \getimagesize($this->getPathname());

        return [
            'width' => $width,
            'height' => $height
        ];
    }

    public function isUploadedFile()
    {
        return \is_uploaded_file($this->getPathname());
    }
}
