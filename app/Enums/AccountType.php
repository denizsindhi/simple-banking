<?php

namespace App\Enums;

enum AccountType: string
{
    case PERSONAL = 'personal';
    case SAVINGS = 'savings';
    case BUSINESS = 'business';
}


