<?php

namespace Jieba\Traits;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * We still need provide developers with enough flexibility to define construct methods in a best way they want,
     * thus we should not define a construct method here.
     */
    // public function __construct(LoggerInterface $logger)
    // {
    //     $this->logger = $logger;
    // }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
