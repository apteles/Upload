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

    /**
     *
     * @param string $name
     * @return FileInfoInterface
     */
    public function setName(string $name): FileInfoInterface
    {
        $this->name = \basename($this->sureThatNameBeSafe($name));

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     *
     * @param string $extension
     * @return FileInfoInterface
     */
    public function setExtension(string $extension): FileInfoInterface
    {
        $this->extension = \strtolower($extension);

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getNameWithExtension(): string
    {
        if (!$this->extension) {
            return $this->name;
        }
        return \sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     *
     * @return string
     */
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
    
    /**
     *
     * @return string
     */
    public function getMd5(): string
    {
        return \md5_file($this->getPathname());
    }

    /**
     * This function support algorithms like sha1,sha256, md5 etc.
     * @param string $algo
     * @return string
     */
    public function getHash($algo = 'md5'): string
    {
        return \hash_file($algo, $this->getPathname());
    }

    /**
     *
     * @return array
     */
    public function getDimensions(): array
    {
        [$width, $height] = \getimagesize($this->getPathname());

        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     *
     * @return boolean
     */
    public function isUploadedFile(): bool
    {
        return \is_uploaded_file($this->getPathname());
    }
}
