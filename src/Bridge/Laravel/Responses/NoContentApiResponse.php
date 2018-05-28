<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Responses;

/**
 * This class has for purpose to easily customise status code and/or headers for given content.
 */
class NoContentApiResponse extends FormattedApiResponse
{
    /**
     * FormattedApiResponse constructor.
     *
     * @param int|null $statusCode
     * @param mixed[]|null $headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(?int $statusCode = null, ?array $headers = null)
    {
        parent::__construct('', $statusCode ?? 204, $headers);
    }
}
