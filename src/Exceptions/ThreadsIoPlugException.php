<?php

namespace Jobinja\ThreadsIo\Exceptions;

/**
 * Exception raised when an error occurs whithin the package
 *
 * Class ThreadsIoPlugException
 * @package Jobinja\ThreadsIo\Exceptions
 */
class ThreadsIoPlugException extends \Exception implements ThreadsIoExceptionInterface
{
    /**
     * Exception constructor.
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}