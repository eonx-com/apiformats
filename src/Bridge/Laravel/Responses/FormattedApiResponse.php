<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Responses;

use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class has for purpose to easily customise status code and/or headers for given content.
 */
class FormattedApiResponse extends Response implements FormattedApiResponseInterface
{
    /**
     * FormattedApiResponse constructor.
     *
     * @param mixed $content
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content, int $statusCode = null, array $headers = null)
    {
        parent::__construct();

        $this->content = $content;
        $this->statusCode = $statusCode ?? 200;
        $this->headers = $headers ?? [];
    }

    /**
     * Get headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return parent::getStatusCode();
    }
}
