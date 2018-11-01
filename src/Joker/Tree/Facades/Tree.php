<?php

namespace MrsJoker\Tree\Facades;

use Illuminate\Support\Facades\Facade;

class Tree extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tree';
    }
}
