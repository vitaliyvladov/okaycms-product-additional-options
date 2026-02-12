<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Entities;

use Okay\Core\Entity\Entity;

class PurchasesAdditionalOptionsEntity extends Entity
{
    protected static $fields = [
        'id',
        'purchase_id',
        'value_id',
        'value_name',
    ];

    protected static $defaultOrderFields = [
        'id',
    ];

    protected static $table = '__lavvod__purchases_additional_options';
    protected static $tableAlias = 'paop';
}

