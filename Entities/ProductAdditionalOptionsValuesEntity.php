<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Entities;

use Okay\Core\Entity\Entity;

class ProductAdditionalOptionsValuesEntity extends Entity
{
    protected static $fields = [
        'id',
        'name',
        'position',
    ];

    protected static $langFields = [
        'name',
    ];

    protected static $defaultOrderFields = [
        'position',
    ];

    protected static $table = '__lavvod__product_additional_options_values';
    protected static $langTable = 'lavvod__product_additional_options_values';
    protected static $langObject = 'product_additional_option_value';
    protected static $tableAlias = 'paov';
}

