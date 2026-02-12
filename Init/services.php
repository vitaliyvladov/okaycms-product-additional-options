<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Init;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\CartExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\OrderExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\ProductExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\EmailExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers\ProductAdditionalOptionsHelper;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsValuesEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\PurchasesAdditionalOptionsEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsCategoriesEntity;

return [
    ProductAdditionalOptionsHelper::class => [
        'class' => ProductAdditionalOptionsHelper::class,
    ],
    CartExtender::class => [
        'class' => CartExtender::class,
    ],
    ProductExtender::class => [
        'class' => ProductExtender::class,
    ],
    OrderExtender::class => [
        'class' => OrderExtender::class,
    ],
    EmailExtender::class => [
        'class' => EmailExtender::class,
        'arguments' => [
            new SR(Design::class),
            new SR(EntityFactory::class),
        ],
    ],
    ProductAdditionalOptionsValuesEntity::class => [
        'class' => ProductAdditionalOptionsValuesEntity::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    PurchasesAdditionalOptionsEntity::class => [
        'class' => PurchasesAdditionalOptionsEntity::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    ProductAdditionalOptionsCategoriesEntity::class => [
        'class' => ProductAdditionalOptionsCategoriesEntity::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
];

