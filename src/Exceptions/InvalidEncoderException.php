<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats\Exceptions;

use EoneoPay\ApiFormats\Interfaces\ApiFormatsExceptionInterface;
use EoneoPay\Utils\Exceptions\CriticalException;

class InvalidEncoderException extends CriticalException implements ApiFormatsExceptionInterface
{
    //
}
