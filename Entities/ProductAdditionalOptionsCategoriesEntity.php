<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Entities;

use Okay\Core\Entity\Entity;

class ProductAdditionalOptionsCategoriesEntity extends Entity
{
    protected static $fields = [
        'id',
        'category_id',
    ];

    protected static $defaultOrderFields = [
        'category_id',
    ];

    protected static $table = '__lavvod__product_additional_options_categories';
    protected static $tableAlias = 'paoc';
}

