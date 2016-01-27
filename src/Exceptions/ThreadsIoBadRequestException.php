<?php

namespace Jobinja\ThreadsIo\Exceptions;

use GuzzleHttp\Exception\ClientException;

class ThreadsIoBadRequestException extends ClientException implements ThreadsIoExceptionInterface
{
    public function __construct(ClientException $e)
    {
        parent::__construct(
            $e->getMessage(),
            $e->getRequest(),
            $e->getResponse(),
            $e,
            $e->getHandlerContext()
        );
    }
}