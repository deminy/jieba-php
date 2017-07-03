<?php

namespace Jieba\ModelConverters;

use Jieba\Exception;
use Jieba\Helper\Helper;
use Jieba\Traits\BasePathTrait;
use Jieba\Traits\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractConverter
 *
 * @package Jieba\ModelConverters
 */
abstract class AbstractConverter
{
    use BasePathTrait, LoggerTrait;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $data;

    /**
     * AbstractConverter constructor.
     *
     * @param string $basePath
     * @param string $filename
     * @param LoggerInterface $logger
     */
    public function __construct(string $basePath, string $filename, LoggerInterface $logger)
    {
        $this->setBasePath($basePath)->setFilename($filename)->setLogger($logger);
    }

    /**
     * @return AbstractConverter
     * @throws Exception
     */
    public function convert(): AbstractConverter
    {
        return $this->preProcess()->process()->postProcess();
    }

    /**
     * @return AbstractConverter
     * @throws Exception
     */
    abstract protected function process(): AbstractConverter;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return AbstractConverter
     */
    public function setFilename(string $filename): AbstractConverter
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return AbstractConverter
     */
    public function setData(string $data): AbstractConverter
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getPyFile(): string
    {
        $pyFile = $this->getBasePath() . $this->getFilename() . '.py';
        if (!is_readable($pyFile)) {
            throw new Exception("Python file '{$pyFile}' not readable");
        }

        return $pyFile;
    }

    /**
     * @return AbstractConverter
     * @throws Exception
     */
    protected function preProcess(): AbstractConverter
    {
        $pyFile = $this->getPyFile();
        $data   = trim(file_get_contents($pyFile));

        $pos = strpos($data, 'P={');
        if (false !== $pos) {
            $this->setData('{' . substr($data, $pos + 3));
        } else {
            throw new Exception("Unable to parse data from Python file '{$pyFile}'");
        }

        return $this;
    }

    /**
     * @return AbstractConverter
     */
    protected function postProcess(): AbstractConverter
    {
        // print_r($this->getData());
        $file = Helper::getModelFilePath($this->getFilename());
        file_put_contents($file, json_encode(json_decode($this->getData())));
        $this->getLogger()->debug("Data wrote to file '{$file}'");

        return $this;
    }

    /**
     * @return AbstractConverter
     */
    protected function replaceQuotes(): AbstractConverter
    {
        $this->data = str_replace('\'', '"', $this->data);

        return $this;
    }

    /**
     * In files like "posseg/prob_trans.py", array keys are like "('B', 'a')" ("['B', 'a']" in PHP). This method is
     * to fix the key making them as string kes look like "Ba".
     * @return AbstractConverter
     * @see https://github.com/fxsjy/jieba/blob/master/jieba/posseg/prob_trans.py
     */
    protected function fixArrayKeys(): AbstractConverter
    {
        $this->data = preg_replace('/\(\'(\w+)\',\s*\'(\w+)\'\)/m', '"$1$2"', $this->data);

        return $this;
    }

    /**
     * In files like "posseg/char_state_tab.py", arrays are like ("Sng", "En") or ("Sg",). This method is to fix the
     * array making them like {"Sng", "En"}.
     * @return AbstractConverter
     * @see https://github.com/fxsjy/jieba/blob/master/jieba/posseg/char_state_tab.py
     */
    protected function fixArrays(): AbstractConverter
    {
        $this->data = preg_replace('/\(([^\(\)]*[^,]),?\)/m', '[$1]', $this->data);

        return $this;
    }
}
