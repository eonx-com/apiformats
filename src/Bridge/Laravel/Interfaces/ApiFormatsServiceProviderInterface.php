<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Interfaces;

interface ApiFormatsServiceProviderInterface
{
    /**
     * Boot api formats services.
     *
     * @return void
     */
    public function boot(): void;

    /**
     * Register api formats services.
     *
     * @return void
     */
    public function register(): void;
}
