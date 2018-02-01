<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use ArrayAccess;
use Illuminate\Contracts\Foundation\Application;

abstract class ApplicationMockStub implements Application, ArrayAccess
{
}
