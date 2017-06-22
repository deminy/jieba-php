<?php

namespace Jieba\Option;

use Jieba\Exception;
use Jieba\Helper;
use Jieba\Serializer\SerializerFactory;

/**
 * Class Dict
 *
 * @package Jieba\Option
 */
class Dict extends AbstractOption
{
    const NORMAL  = 'normal';
    const SMALL   = 'small';
    const BIG     = 'big';

    const EXT_DEFAULT    = '';
    const EXT_JSON       = '.json';
    const EXT_CACHE_JSON = '.cache.json';

    const VALID_DICTIONARIES = [
        self::NORMAL => self::NORMAL,
        self::SMALL  => self::SMALL,
        self::BIG    => self::BIG,
    ];

    /**
     * @var string
     */
    protected $dict;

    /**
     * Dict constructor.
     *
     * @param string $dict
     */
    public function __construct(string $dict = self::NORMAL)
    {
        $this->setDict($dict);
    }

    /**
     * @param string|null $fileType
     * @param string|null $dict
     * @return bool|mixed|string
     * @throws Exception
     */
    public function getDictFileContent(string $fileType = null, string $dict = null)
    {
        switch ($fileType) {
            case self::EXT_JSON:
            case self::EXT_CACHE_JSON:
                $file = $this->getDictFilePath($fileType, $dict);
                if (!file_exists($file)) {
                    throw new Exception(
                        'Dictionary files missing. Please run script "./bin/gen_dict_json.php" to generate them'
                    );
                }

                return SerializerFactory::getSerializer()->decode(file_get_contents($file), true);
                break;
            case self::EXT_DEFAULT:
            default:
                return file_get_contents($this->getDictFilePath($fileType, $dict));
                break;
        }
    }

    /**
     * @param string|null $fileType
     * @param string|null $dict
     * @return string
     */
    public function getDictFilePath(string $fileType = null, string $dict = null): string
    {
        return (Helper::getDictBasePath($fileType) . $this->getDictBaseName($fileType, $dict));
    }

    /**
     * @param string|null $fileType
     * @param string|null $dict
     * @return string
     * @throws Exception
     */
    public function getDictBaseName(string $fileType = null, string $dict = null): string
    {
        if (empty($dict)) {
            $dict = $this->getDict();
        }
        if (empty($fileType)) {
            $fileType = self::EXT_DEFAULT;
        }
        if (!in_array($fileType, [self::EXT_DEFAULT, self::EXT_JSON, self::EXT_CACHE_JSON])) {
            throw new Exception("invalid dictionary extension '{$fileType}'");
        }

        switch ($dict) {
            case self::SMALL:
                $basename = 'dict.small.txt';
                break;
            case self::BIG:
                $basename = 'dict.big.txt';
                break;
            case self::NORMAL:
            default:
                $basename = 'dict.txt';
                break;
        }

        return ($basename . $fileType);
    }

    /**
     * @return string
     */
    public function getDict(): string
    {
        return $this->dict;
    }

    /**
     * @param string $dict
     * @return Dict
     * @throws Exception
     */
    public function setDict(string $dict): Dict
    {
        if (!$this->isValid($dict)) {
            throw new Exception("invalid dictionary '{$dict}' specified");
        }

        $this->dict = $dict;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function isValid(string $value): bool
    {
        return array_key_exists($value, self::VALID_DICTIONARIES);
    }
}
