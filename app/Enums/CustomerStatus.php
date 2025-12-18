<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case CLOSED = 'closed';
}


