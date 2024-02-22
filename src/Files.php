<?php

declare(strict_types=1);

namespace FeWeDev\Base;

use Exception;
use InvalidArgumentException;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Files
{
    /** @var Variables */
    protected $variables;

    /** @var Arrays */
    protected $arrays;

    /**
     * @param Arrays|null    $arrays
     * @param Variables|null $variables
     */
    public function __construct(Variables $variables = null, Arrays $arrays = null)
    {
        if ($variables === null) {
            $variables = new Variables();
        }

        if ($arrays === null) {
            $arrays = new Arrays($variables);
        }

        $this->variables = $variables;
        $this->arrays = $arrays;
    }

    /**
     * Method to set path as relative (in Magento directories) or absolute for server
     *
     * @param string $path
     * @param string $basePath
     * @param bool   $makeDir
     *
     * @return string
     * @throws Exception
     */
    public function determineFilePath(string $path, string $basePath, bool $makeDir = false): string
    {
        if ($this->variables->isEmpty($path)) {
            throw new Exception('No path specified');
        }

        // for Windows systems
        $path = preg_replace('/\\\\/', '/', $path);

        $path = preg_match('/^\//', (string)$path) || preg_match('/^[a-zA-Z]:\//', (string)$path) ?
            rtrim((string)$path, '/') : rtrim($basePath, '/') . '/' . trim((string)$path, '/');

        $fileCheck = pathinfo($path);
        // Check for last character
        $pathEnding = substr($path, -1);

        if ((! array_key_exists('extension', $fileCheck) || $this->variables->isEmpty($fileCheck[ 'extension' ])) &&
            $pathEnding != '/' && $pathEnding != "\\") {
            $path .= '/';
        }

        $dirPath = ! isset($fileCheck[ 'extension' ]) ? $path : dirname($path);

        if ($makeDir && ! is_dir($dirPath)) {
            $this->createDirectory($dirPath);
        }

        return $path;
    }

    /**
     * Method to read Files from directory
     *
     * @param string $path
     * @param string $basePath
     *
     * @return array<int, string>
     * @throws Exception
     */
    public function determineFilesFromFilePath(string $path, string $basePath): array
    {
        return $this->determineFromFilePath($path, $basePath, true, false);
    }

    /**
     * Method to read Files from directory
     *
     * @param string $path
     * @param string $basePath
     *
     * @return array<int, string>
     * @throws Exception
     */
    public function determineDirectoriesFromFilePath(string $path, string $basePath): array
    {
        return $this->determineFromFilePath($path, $basePath, false);
    }

    /**
     * @param string $path
     * @param string $basePath
     * @param bool   $includeFiles
     * @param bool   $includeDirectories
     *
     * @return array<int, string>
     * @throws Exception
     */
    public function determineFromFilePath(
        string $path,
        string $basePath,
        bool $includeFiles = true,
        bool $includeDirectories = true
    ): array {
        $path = $this->determineFilePath($path, $basePath);

        if (! file_exists($path) || ! is_readable($path)) {
            return [];
        }

        $pathContent = scandir($path);
        if ($pathContent === false) {
            $pathContent = [];
        }

        $fileNames = preg_grep('/^\.+$/', $pathContent, PREG_GREP_INVERT);
        if ($fileNames === false) {
            $fileNames = [];
        }

        $files = [];

        if (count($fileNames) > 0) {
            natcasesort($fileNames);

            foreach ($fileNames as $fileName) {
                $fileName = $this->determineFilePath($fileName, $path);

                if ($includeFiles && is_file($fileName)) {
                    $files[] = $fileName;
                }

                if ($includeDirectories && is_dir($fileName)) {
                    $files[] = $fileName;
                }
            }
        }

        return $files;
    }

    /**
     * Create directories if they not exist
     *
     * @param string $dir
     * @param int    $mode
     *
     * @return bool
     */
    public function createDirectory(string $dir, int $mode = 0777): bool
    {
        return @mkdir($dir, $mode, true);
    }

    /**
     * @param string $dir
     * @param bool   $recursive
     *
     * @return bool
     */
    public function removeDirectory(string $dir, bool $recursive = true): bool
    {
        if ($recursive) {
            $result = self::recursiveRemoval($dir, ['unlink'], ['rmdir']);
        } else {
            $result = @rmdir($dir);
        }

        return $result;
    }

    /**
     * @param string            $dir
     * @param array<int, mixed> $fileCallback
     * @param array<int, mixed> $dirCallback
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected static function recursiveRemoval(string $dir, array $fileCallback, array $dirCallback = []): bool
    {
        if (empty($fileCallback) || ! is_array($dirCallback)) {
            throw new InvalidArgumentException('file/dir callback is not specified');
        }

        if (empty($dirCallback)) {
            $dirCallback = $fileCallback;
        }

        if (is_dir($dir)) {
            $directoryContent = scandir($dir, SCANDIR_SORT_NONE);
            if ($directoryContent === false) {
                $directoryContent = [];
            }

            foreach ($directoryContent as $item) {
                if (! strcmp($item, '.') || ! strcmp($item, '..')) {
                    continue;
                }

                self::recursiveRemoval($dir . '/' . $item, $fileCallback, $dirCallback);
            }

            $callback = $dirCallback[ 0 ];

            if (! is_callable($callback)) {
                throw new InvalidArgumentException("'dirCallback' parameter is not callable");
            }

            $parameters = $dirCallback[ 1 ] ?? [];
        } else {
            $callback = $fileCallback[ 0 ];

            if (! is_callable($callback)) {
                throw new InvalidArgumentException("'fileCallback' parameter is not callable");
            }

            $parameters = $fileCallback[ 1 ] ?? [];
        }

        if (! is_array($parameters)) {
            throw new InvalidArgumentException('Invalid callback parameters');
        }

        array_unshift($parameters, $dir);

        $result = @call_user_func_array($callback, $parameters);

        if (! is_bool($result)) {
            throw new InvalidArgumentException('Invalid callback result');
        }

        return $result;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    public function removeFile(string $fileName): bool
    {
        return @unlink($fileName);
    }

    /**
     * @param string $src
     * @param string $destination
     *
     * @return bool
     */
    public function copyFile(string $src, string $destination): bool
    {
        return @copy($src, $destination);
    }

    /**
     * @param string $image
     *
     * @return bool
     */
    public function isImage(string $image): bool
    {
        return preg_match('/\.(jpe?g|png|gif|bmp|tiff?|webp|svg)$/i', $image) !== false;
    }
}
