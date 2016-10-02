<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Exception;

use Fidry\AliceDataFixtures\Loader\ErrorTracker;
use Fidry\AliceDataFixtures\Loader\FileTracker;
use Nelmio\Alice\Throwable\LoadingThrowable;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MaxPassReachedException extends \RuntimeException implements LoadingThrowable
{
    /**
     * @param int             $limit
     * @param FileTracker     $fileTracker
     * @param ErrorTracker    $errorTracker
     * @param int             $code
     * @param \Throwable|null $previous
     *
     * @return static
     */
    public static function createForLimit(
        int $limit,
        FileTracker $fileTracker,
        ErrorTracker $errorTracker,
        int $code = 0,
        \Throwable $previous = null
    ) {
        return new static(
            static::createMessage($limit, $fileTracker, $errorTracker),
            $code,
            $previous
        );
    }

    private static function createMessage(int $limit, FileTracker $fileTracker, ErrorTracker $errorTracker): string
    {
        $unloadedFiles = $fileTracker->getUnloadedFiles();

        $messageLines = [
            sprintf('Loading files limit of %d reached. Could not load the following files:', $limit),
        ];

        $errorStack = $errorTracker->getStack();
        foreach ($unloadedFiles as $unloadedFile) {
            $messageLines = static::createMessageLines($messageLines, $unloadedFile, $errorStack);
        }

        return implode(PHP_EOL, $messageLines);
    }

    private static function createMessageLines(array $messageLines, string $unloadedFile, array $errorStack): array
    {
        if (false === array_key_exists($unloadedFile, $errorStack) || 0 === count($errorStack[$unloadedFile])) {
            $messageLines[] = $unloadedFile;

            return $messageLines;
        }

        $messageLines[] = sprintf('%s:', $unloadedFile);
        $fileErrorMessages = array_unique($errorStack[$unloadedFile]);
        foreach ($fileErrorMessages as $errorMessage) {
            $messageLines[] = sprintf(' - %s', $errorMessage);
        }

        return $messageLines;
    }
}