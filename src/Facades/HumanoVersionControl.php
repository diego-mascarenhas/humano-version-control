<?php

namespace Idoneo\HumanoVersionControl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Idoneo\HumanoVersionControl\HumanoVersionControl
 */
class HumanoVersionControl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Idoneo\HumanoVersionControl\HumanoVersionControl::class;
    }
}
