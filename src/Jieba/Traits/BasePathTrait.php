<?php

namespace Jieba\Traits;

use Jieba\Exception;

trait BasePathTrait
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     * @return $this
     * @throws Exception
     */
    public function setBasePath(string $basePath)
    {
        if (!is_dir($basePath)) {
            throw new Exception("Path '{$basePath}' does not point to a directory");
        }

        $this->basePath = $basePath;

        return $this;
    }
}
