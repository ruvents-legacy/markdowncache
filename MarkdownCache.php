<?php

namespace Vudaltsov\MarkdownCache;

/**
 * Class MarkdownCache
 *
 * @package Vudaltsov\MarkdownCache
 */
class MarkdownCache
{
    /**
     * Html file extension
     */
    const CACHE_EXT = 'html';

    /**
     * Return html code
     */
    const RETURN_HTML = 0;

    /**
     * Return file path
     */
    const RETURN_PATH = 1;

    /**
     * Parsedown parser instance
     *
     * @var Parsedown
     */
    protected $parsedown;

    /**
     * Cache directory
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Constructor
     *
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->setCacheDir($cacheDir);
    }

    /**
     * @param string $mdPath
     *
     * @return string
     */
    protected function getCachePath($mdPath)
    {
        return $this->cacheDir.'/'.md5($mdPath).'.'.self::CACHE_EXT;
    }

    /**
     * @param string $cachePath
     * @param string $data
     *
     * @return int
     * @throws MarkdownCacheException
     */
    protected function putCache($cachePath, $data)
    {
        if (!@file_put_contents($cachePath, $data)) {
            throw new MarkdownCacheException("Failed to write to $cachePath");
        }
    }

    /**
     * @param string $cacheDir
     *
     * @return $this
     * @throws MarkdownCacheException
     */
    public function setCacheDir($cacheDir)
    {
        if (!is_writable($cacheDir)) {
            throw new MarkdownCacheException("$cacheDir doesn't exist or is not writable");
        }

        $this->cacheDir = $cacheDir;

        return $this;
    }

    /**
     * @return Parsedown
     */
    public function getParsedown()
    {
        if (!isset($this->parsedown)) {
            $this->parsedown = new \Parsedown();
        }

        return $this->parsedown;
    }

    /**
     * Performs parsing and caching
     * Returns path or html depending on $returnType
     *
     * @param string $mdPath
     * @param int    $returnType
     *
     * @return string
     * @throws MarkdownCacheException
     */
    public function get($mdPath, $returnType = self::RETURN_PATH)
    {
        if (!file_exists($mdPath)) {
            throw new MarkdownCacheException("File $mdPath doesn't exist");
        }

        $cachePath = $this->getCachePath($mdPath);

        // no cache found
        if (!file_exists($cachePath) || filemtime($cachePath) < filemtime($mdPath)) {
            $mdContent = file_get_contents($mdPath);
            $htmlContent = $this->getParsedown()->text($mdContent);
            $this->putCache($cachePath, $htmlContent);

            if ($returnType === self::RETURN_HTML) {
                return $htmlContent;
            }
        } // cache exists
        else {
            if ($returnType === self::RETURN_HTML) {
                return file_get_contents($cachePath);
            }
        }

        // return path by default
        return $cachePath;
    }

    /**
     * Returns the path of the parsed and cached html version of md file
     *
     * @param $mdPath
     *
     * @return string
     */
    public function getPath($mdPath)
    {
        return $this->get($mdPath, self::RETURN_PATH);
    }

    /**
     * Returns html code of the parsed and cached md file
     *
     * @param $mdPath
     *
     * @return string
     */
    public function getHtml($mdPath)
    {
        return $this->get($mdPath, self::RETURN_HTML);
    }

    /**
     * Renders md file
     *
     * @param $mdPath
     */
    public function render($mdPath)
    {
        include $this->get($mdPath, self::RETURN_PATH);
    }

    /**
     * Clears cache dir
     */
    public function clearCache()
    {
        $files = glob($this->cacheDir.'/*.'.self::CACHE_EXT);

        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
