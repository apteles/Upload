<?php

use Apteles\Upload\FileInfo;
use PHPUnit\Framework\TestCase;
use Apteles\Upload\Contracts\FileInfoInterface;

class FileInfoTest extends TestCase
{
    /**
     *
     * @var FileInfoInterface
     */
    private $fileinfo;

    private $filePath;

    private $fileGlobal;

    public function setUp():void
    {
        $this->filePath = __DIR__ . '/../resources/foo.txt';
        $this->fileinfo = new FileInfo(__DIR__ . '/../resources/foo.txt');
    }

    public function testIfFileNameIsSamePasssedInContructor()
    {
        $expected = 'foo';
        $this->assertEquals($expected, $this->fileinfo->getName());
    }

    public function testIfExtesionWasDefined()
    {
        $expected = 'txt';
        $this->assertEquals($expected, $this->fileinfo->getExtension());
    }

    public function testItShouldReturnFileWithExtesion()
    {
        $expected = 'foo.txt';
        $this->assertEquals($expected, $this->fileinfo->getNameWithExtension());
    }

    public function testItShouldReturnMimeTypeOfFile()
    {
        $this->assertEquals('inode/x-empty', $this->fileinfo->getMimetype());
    }

    public function testIfShouldReturnHasMd5BasedInNameOfTheFile()
    {
        $hash = \md5_file($this->filePath);
        $this->assertEquals($hash, $this->fileinfo->getMd5());
    }

    public function testIfShouldGenerateHashWithManyDifferentAlgo()
    {
        $hashMd5 = \hash_file('md5', $this->filePath);
        $this->assertEquals($hashMd5, $this->fileinfo->gethash());

        $hashSha256 = \hash_file('sha256', $this->filePath);
        $this->assertEquals($hashSha256, $this->fileinfo->gethash('sha256'));
    }
}
