<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Interfaces;

interface FormattedApiResponseInterface
{
    /**
     * Get response content.
     *
     * @return mixed
     */
    public function getContent();

    /**
     * Get response headers.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get response status code.
     *
     * @return int
     */
    public function getStatusCode(): int;
}
