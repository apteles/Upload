<?php
require_once __DIR__ . '/../vendor/autoload.php';
\ini_set('file_uploads', 1);
use Apteles\Upload\File;
use Apteles\Upload\FileInfo;
use Apteles\Upload\Storage\FileSystem;

if ($_FILES) {
    $directory = __DIR__ . '/storage';

    \var_dump($_FILES);
    try {
        $storage = new FileSystem($directory);
        $file = new File();
        $file->setStorage($storage);

        $file->beforeUpload(function (FileInfo $file) {
            $file->setName($file->getHash());
        });

        $file->afterUpload(function (FileInfo $file) {
            echo 'uploaded successfuly!';
        });
        $file->multiple('foo');
        $file->upload();
    } catch (Throwable $th) {
        \var_dump($th->getMessage());
    }
}



?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foo[]" value="" multiple/>
    <input type="submit" value="Upload File"/>
</form>
