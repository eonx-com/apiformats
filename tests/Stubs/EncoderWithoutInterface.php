<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use Psr\Http\Message\ServerRequestInterface;

class EncoderWithoutInterface
{
    /**
     * EncoderWithoutInterface constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) Contructor matches but does not implement interface
     */
    public function __construct(ServerRequestInterface $request)
    {
    }
}
