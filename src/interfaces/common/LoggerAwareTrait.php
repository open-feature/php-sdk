<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function is_null;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     */
    protected ?LoggerInterface $logger = null;

    /**
     * Sets a logger.
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gets the logger, defaulting to NullLogger if not set
     */
    public function getLogger(): LoggerInterface
    {
        if (!is_null($this->logger)) {
            return $this->logger;
        }

        return new NullLogger();
    }
}
