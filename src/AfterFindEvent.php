<?php

namespace rock\db\common;


use rock\events\Event;

class AfterFindEvent extends Event
{
    /**
     * @var mixed the query result.
     */
    public $result;
} 