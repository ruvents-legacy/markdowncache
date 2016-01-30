<?php

namespace Vudaltsov\MarkdownCache;

/**
 * Class MarkdownCache
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
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getCachePath($path)
    {
        return $this->cacheDir.'/'.md5($path).'.'.self::CACHE_EXT;
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
     */
    public function setCacheDir($cacheDir)
    {
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
     * @param string $sourcePath
     * @param int    $returnType
     *
     * @return string
     * @throws MarkdownCacheException
     */
    public function get($sourcePath, $returnType = self::RETURN_PATH)
    {
        if (!file_exists($sourcePath)) {
            throw new MarkdownCacheException("File $sourcePath doesn't exist");
        }

        $cachePath = $this->getCachePath($sourcePath);

        // no cache found
        if (!file_exists($cachePath) || filemtime($cachePath) < filemtime($sourcePath)) {
            $mdContent = file_get_contents($sourcePath);
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
     * Returns path of a parsed and cached md file
     *
     * @param $path
     *
     * @return string
     */
    public function getPath($path)
    {
        return $this->get($path, self::RETURN_PATH);
    }

    /**
     * Returns html code of a parsed and cached md file
     *
     * @param $path
     *
     * @return string
     */
    public function getHtml($path)
    {
        return $this->get($path, self::RETURN_HTML);
    }

    /**
     * Renders md file
     *
     * @param $path
     */
    public function render($path)
    {
        include $this->get($path, self::RETURN_PATH);
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
