<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreviewUnavailableException extends HttpException
{
    public function __construct(string $message = 'Buy this song to listen to it.')
    {
        parent::__construct(Response::HTTP_PAYMENT_REQUIRED, $message);
    }
}
