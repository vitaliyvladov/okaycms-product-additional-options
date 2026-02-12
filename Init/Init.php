<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Init;

use Okay\Core\Modules\EntityField;
use Okay\Core\Modules\AbstractInit;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsValuesEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\PurchasesAdditionalOptionsEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsCategoriesEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\CartExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\ProductExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\OrderExtender;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders\EmailExtender;
use Okay\Core\Cart;
use Okay\Entities\ProductsEntity;
use Okay\Helpers\OrdersHelper;
use Okay\Helpers\CartHelper;
use Okay\Helpers\NotifyHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;

class Init extends AbstractInit
{
    public function install()
    {
        $this->setBackendMainController('ProductAdditionalOptionsAdmin');
        
        // Таблиця значень опцій (спрощена структура - без опцій, тільки значення)
        $this->migrateEntityTable(ProductAdditionalOptionsValuesEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('name'))->setTypeVarchar(255)->setIsLang(),
            (new EntityField('position'))->setTypeInt(11),
        ]);
        
        // Таблиця збережених опцій для покупок
        $this->migrateEntityTable(PurchasesAdditionalOptionsEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('purchase_id'))->setTypeInt(11),
            (new EntityField('value_id'))->setTypeInt(11),
            (new EntityField('value_name'))->setTypeVarchar(255),
        ]);
        
        // Таблиця зв'язку категорій з модулем
        $this->migrateEntityTable(ProductAdditionalOptionsCategoriesEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('category_id'))->setTypeInt(11),
        ]);
    }
    
    public function init()
    {
        // Реєстрація бек-контроллера
        $this->registerBackendController('ProductAdditionalOptionsAdmin');
        $this->addBackendControllerPermission('ProductAdditionalOptionsAdmin', 'lavvod__product_additional_options');
        
        // Додавання пункту меню
        $this->extendBackendMenu('left_product_additional_options_title', [
            'left_product_additional_options_menu_item' => ['ProductAdditionalOptionsAdmin'],
        ]);

        // Реєстрація front block для селекту опцій
        $this->addFrontBlock('front_product_additional_option', 'product_additional_option_select.tpl');

        // Реєстрація extenders для кошика
        $this->registerQueueExtension(
            [Cart::class, 'addItem'],
            [CartExtender::class, 'addItem']
        );
        $this->registerQueueExtension(
            [Cart::class, 'updateItem'],
            [CartExtender::class, 'updateItem']
        );
        $this->registerChainExtension(
            [Cart::class, 'addPurchase'],
            [CartExtender::class, 'addPurchase']
        );
        $this->registerChainExtension(
            [Cart::class, 'updatePurchase'],
            [CartExtender::class, 'updatePurchase']
        );
        $this->registerChainExtension(
            [Cart::class, 'getPurchases'],
            [CartExtender::class, 'getPurchases']
        );

        // Реєстрація extender для товарів
        $this->registerChainExtension(
            [ProductsEntity::class, 'get'],
            [ProductExtender::class, 'get']
        );

        // Реєстрація extenders для замовлень
        $this->registerQueueExtension(
            [CartHelper::class, 'cartToOrder'],
            [OrderExtender::class, 'cartToOrder']
        );
        $this->registerQueueExtension(
            [OrdersHelper::class, 'getOrderPurchasesList'],
            [OrderExtender::class, 'getOrderPurchasesList']
        );
        // Реєстрація extender для адмін-панелі
        $this->registerQueueExtension(
            [BackendOrdersHelper::class, 'findOrderPurchases'],
            [OrderExtender::class, 'findOrderPurchases']
        );

        // Реєстрація extenders для email-листів
        $this->registerQueueExtension(
            [NotifyHelper::class, 'finalEmailOrderUser'],
            [EmailExtender::class, 'finalEmailOrderUser']
        );
        $this->registerQueueExtension(
            [NotifyHelper::class, 'finalEmailOrderAdmin'],
            [EmailExtender::class, 'finalEmailOrderAdmin']
        );
    }
}

