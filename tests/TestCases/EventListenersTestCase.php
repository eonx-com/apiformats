<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\TestCases;

use PHPUnit\Framework\TestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EventListenersTestCase extends TestCase
{
    /**
     * Get GetResponseEvent with given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    protected function getGetResponseEvent(Request $request): GetResponseEvent
    {
        return new GetResponseEvent(
            Mockery::mock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
