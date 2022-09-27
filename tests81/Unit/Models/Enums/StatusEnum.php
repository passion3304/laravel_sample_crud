<?php

namespace Backpack\CRUD\Tests81\Unit\Models\Enums;

enum StatusEnum: string
{
    case DRAFT = 'drafted';
    case PUBLISHED = 'publish';
}
