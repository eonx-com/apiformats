<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Exceptions;

use EoneoPay\ApiFormats\Interfaces\ApiFormatsExceptionInterface;

abstract class AbstractApiFormatsException extends \Exception implements ApiFormatsExceptionInterface
{
}
