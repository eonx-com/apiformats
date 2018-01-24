<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;

/**
 * This class has for purpose to easily customise status code and/or headers for given content.
 */
class FormattedApiResponse implements FormattedApiResponseInterface
{
    /**
     * @var mixed
     */
    private $content;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * FormattedApiResponse constructor.
     *
     * @param mixed      $content
     * @param int|null   $statusCode
     * @param array|null $headers
     */
    public function __construct($content, int $statusCode = null, array $headers = null)
    {
        $this->content = $content;
        $this->statusCode = $statusCode ?? 200;
        $this->headers = $headers ?? [];
    }

    /**
     * Get content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
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
        return $this->statusCode;
    }
}
