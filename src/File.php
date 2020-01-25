<?php
declare(strict_types=1);
namespace Apteles\Upload;

use Countable;
use Exception;
use Throwable;
use ArrayAccess;
use ArrayIterator;
use RuntimeException;
use IteratorAggregate;
use InvalidArgumentException;
use Apteles\Upload\Contracts\StorageInterface;
use Apteles\Upload\Contracts\FileInfoInterface;

class File implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     *
     * @var StorageInterface
     */
    private $storage;

    private $objects = [];

    private $validations = [];

    private $errors = [];

    private $acessor;

    /**
     *
     * @var callable
     */
    private $beforeUpload;

    /**
    *
    * @var callable
    */
    private $afterUpload;

    public function __construct(string $accessor = null, StorageInterface $storage = null)
    {
        $this->init($accessor, $storage);
    }

    private function init(string $accessor = null, StorageInterface $storage = null)
    {
        $this->uploadIsEnabled();
        $this->setAcessor($accessor);

        if (!\is_null($storage)) {
            $this->setStorage($storage);
        }
    }

    public function beforeUpload(callable $handle)
    {
        $this->beforeUpload = $handle;
    }

    public function afterUpload(callable $handle)
    {
        $this->afterUpload = $handle;
    }

    private function applyCallback(string $callbackName, FileInfoInterface $file)
    {
        if (\in_array($callbackName, ['beforeUpload', 'afterUpload'], true)) {
            if (\method_exists($this, $callbackName)) {
                \call_user_func_array($this->$callbackName, [$file]);
            }
        }
    }

    private function uploadIsEnabled():bool
    {
        if (!\ini_get('file_uploads')) {
            throw new RuntimeException("File uploads are disabled in your PHP.ini");
        }

        return true;
    }

    public function setAcessor(?string $key): void
    {
        $this->acessor = $key;
    }

    public function setStorage(StorageInterface $storage): void
    {
        $this->storage = $storage;
    }

    private function keyExists(string $key)
    {
        if (!isset($_FILES[$key])) {
            throw new InvalidArgumentException("Key: ${$key} not found");
        }
        return true;
    }

    public function single(string $acessor = ''): File
    {
        if ($this->acessor) {
            $this->setAcessor($acessor);
        }

        if ($this->hasMultipleFiles($acessor)) {
            throw new RuntimeException("You are sending more than one file. Use multiple Method");
        }

        $this->objects[] = new FileInfo(
            $_FILES[$acessor]['tmp_name'],
            $_FILES[$acessor]['name'],
        );

        return $this;
    }

    public function multiple(string $acessor): File
    {
        if ($this->acessor) {
            $this->setAcessor($acessor);
        }

        if (!$this->hasMultipleFiles($acessor)) {
            return $this->single($acessor);
        }

        foreach ($_FILES[$acessor]['tmp_name'] as $key => $tmp) {
            if ($_FILES[$acessor]['error'][$key] !== UPLOAD_ERR_OK) {
                $this->storeErrors($acessor, (int) $key);
                continue;
            }
            $this->objects[] = new FileInfo(
                $_FILES[$acessor]['tmp_name'][$key],
                $_FILES[$acessor]['name'][$key],
            );
        }

        return $this;
    }

    private function hasMultipleFiles(string $acessor): bool
    {
        if (\is_array($_FILES[$acessor]['tmp_name'])) {
            return true;
        }

        return false;
    }

    private function storeErrors(string $accessor, int $key): void
    {
        $files = $_FILES[$accessor];

        if (!\is_array($files['error'])) {
            $this->storeErrorFormated(
                $files['name'],
                $files['error']
            );

            return;
        }

        $this->storeErrorFormated(
            $files['name'][$key],
            $files['error'][$key]
        );

        return;
    }

    public function storeErrorFormated($key, $value): void
    {
        $this->errors[]= \sprintf('%s: %s', $key, $value);
    }

    public function isValid()
    {
        foreach ($this->objects as $fileInfo) {
            $this->verifyIfIsAValidFileUploaded($fileInfo);
            $this->runValidations($fileInfo);
        }
        return empty($this->errors);
    }

    private function verifyIfIsAValidFileUploaded(FileInfoInterface $fileInfo): void
    {
        if (!$fileInfo->isUploadedFile()) {
            $this->storeErrorFormated(
                $fileInfo->getNameWithExtension(),
                'Is not an uploaded file'
            );
        }
    }

    private function runValidations(FileInfoInterface $fileInfo): void
    {
        foreach ($this->validations as $validation) {
            try {
                $validation->validate($fileInfo);
            } catch (Throwable $th) {
                $this->storeErrorFormated(
                    $fileInfo->getNameWithExtension(),
                    $th->getMessage()
                );
            }
        }
    }

    public function upload(): bool
    {
        if (!$this->isValid()) {
            throw new Exception("File validation failed");
        }

        foreach ($this->objects as $fileInfo) {
            $this->applyCallback('beforeUpload', $fileInfo);
            $uploaded = $this->storage->upload($fileInfo);
            $this->applyCallback('afterUpload', $fileInfo);
        }

        return $uploaded;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->objects[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->objects);
    }

    public function count()
    {
        return \count($this->objects);
    }
}
