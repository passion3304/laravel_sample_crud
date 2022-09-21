<?php

namespace Backpack\CRUD\Tests81\Unit\Models\Enums;

    enum StatusEnum: string
    {
        case DRAFT = 'DRAFT';
        case PUBLISHED = 'PUBLISHED';

        public static function getOptions()
        {
            return array_combine(array_column(self::cases(), 'name'), array_column(self::cases(), 'value'));
        }
    }
