<?php

namespace Wabel\ThreadsIo\Exceptions;


class ThreadsIoPlugException extends \Exception
{
    public function __construct($message) {
        parent::__construct($message);
    }
}