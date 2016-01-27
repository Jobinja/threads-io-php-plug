<?php

namespace Jobinja\ThreadsIo\Exceptions;

use GuzzleHttp\Exception\ServerException;

class ThreadsIoServerException extends ServerException implements ThreadsIoExceptionInterface
{
    public function __construct(ServerException $e)
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