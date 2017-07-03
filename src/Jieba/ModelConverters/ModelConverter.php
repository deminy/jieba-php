<?php

namespace Jieba\ModelConverters;

use Jieba\Exception;
use Jieba\Factory\CacheFactory;
use Jieba\ModelConverters\Finalseg\ProbEmit  as FinalsegProbEmit;
use Jieba\ModelConverters\Finalseg\ProbStart as FinalsegProbStart;
use Jieba\ModelConverters\Finalseg\ProbTrans as FinalsegProbTrans;
use Jieba\ModelConverters\Posseg\CharState   as PossegCharState;
use Jieba\ModelConverters\Posseg\ProbEmit    as PossegProbEmit;
use Jieba\ModelConverters\Posseg\ProbStart   as PossegProbStart;
use Jieba\ModelConverters\Posseg\ProbTrans   as PossegProbTrans;
use Jieba\Traits\BasePathTrait;
use Jieba\Traits\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Class ModelConverter
 *
 * @package Jieba\Helper
 */
class ModelConverter
{
    use BasePathTrait, LoggerTrait;

    /**
     * @var array
     */
    protected $converters = [
        CacheFactory::FINALSEG_PROB_EMIT  => FinalsegProbEmit::class,
        CacheFactory::FINALSEG_PROB_START => FinalsegProbStart::class,
        CacheFactory::FINALSEG_PROB_TRANS => FinalsegProbTrans::class,
        CacheFactory::POSSEG_CHAR_STATE   => PossegCharState::class,
        CacheFactory::POSSEG_PROB_EMIT    => PossegProbEmit::class,
        CacheFactory::POSSEG_PROB_START   => PossegProbStart::class,
        CacheFactory::POSSEG_PROB_TRANS   => PossegProbTrans::class,
    ];

    /**
     * ModelConverter constructor.
     *
     * @param LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);

        $this->basePath = dirname(__DIR__, 3) . '/vendor/fxsjy/jieba/jieba/';
        if (!is_dir($this->basePath)) {
            throw new Exception("Path '{$this->basePath}' does not point to a directory");
        }
    }

    /**
     * @param array $fileTypes
     * @return ModelConverter
     */
    public function convert(array $fileTypes = []): ModelConverter
    {
        if (empty($fileTypes)) {
            $fileTypes = CacheFactory::MODEL_FILES;
        }

        foreach ($fileTypes as $fileType) {
            if (array_key_exists($fileType, $this->converters)) {
                $this->getLogger()->debug("Start to convert model '{$fileType}'");

                /** @var AbstractConverter $converter */
                $converter = new $this->converters[$fileType]($this->basePath, $fileType, $this->getLogger());
                $converter->convert();

                $this->getLogger()->debug("Done converting model '{$fileType}'");
            } else {
                $this->getLogger()->warning("Model converter '{$fileType}' not defined yet");
            }
        }

        return $this;
    }
}
