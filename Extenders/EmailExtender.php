<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders;

use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\PurchasesAdditionalOptionsEntity;

class EmailExtender implements ExtensionInterface
{
    private $design;
    private $entityFactory;
    private $purchasesOptionsEntity;

    public function __construct(
        Design $design,
        EntityFactory $entityFactory
    ) {
        $this->design = $design;
        $this->entityFactory = $entityFactory;
        $this->purchasesOptionsEntity = $entityFactory->get(PurchasesAdditionalOptionsEntity::class);
    }

    /**
     * Додаємо опції до email листів
     */
    public function finalEmailOrderUser($order)
    {
        $this->attachOptionsToEmail($order);
    }

    /**
     * Додаємо опції до email листів адміну
     */
    public function finalEmailOrderAdmin($order)
    {
        $this->attachOptionsToEmail($order);
    }

    private function attachOptionsToEmail($order)
    {
        try {
            if (empty($order)) {
                return;
            }

            // Перевіряємо чи order має id (може бути об'єкт або ID)
            $orderId = null;
            if (is_object($order) && !empty($order->id)) {
                $orderId = $order->id;
            } elseif (is_numeric($order)) {
                $orderId = $order;
            } else {
                return;
            }

            /** @var \Okay\Entities\PurchasesEntity $purchasesEntity */
            $purchasesEntity = $this->entityFactory->get(\Okay\Entities\PurchasesEntity::class);
            $purchases = $purchasesEntity->find(['order_id' => $orderId]);

            if (empty($purchases)) {
                return;
            }

            $purchaseIds = [];
            foreach ($purchases as $purchase) {
                if (!empty($purchase->id)) {
                    $purchaseIds[] = $purchase->id;
                }
            }

            if (empty($purchaseIds)) {
                return;
            }

            $purchasesOptions = $this->purchasesOptionsEntity->find(['purchase_id' => $purchaseIds]);
            $optionsByPurchase = [];
            if (!empty($purchasesOptions)) {
                foreach ($purchasesOptions as $po) {
                    if (!empty($po->purchase_id)) {
                        $optionsByPurchase[$po->purchase_id] = $po;
                    }
                }
            }

            // Додаємо опції до purchases
            foreach ($purchases as $purchase) {
                if (!empty($purchase->id) && isset($optionsByPurchase[$purchase->id])) {
                    $po = $optionsByPurchase[$purchase->id];
                    if (!empty($po->value_name)) {
                        $purchase->additional_option = [
                            'value_name' => $po->value_name,
                        ];
                    }
                }
            }

            // Призначаємо purchases до design
            $this->design->assign('purchases', $purchases);
        } catch (\Exception $e) {
            // Не викидаємо виняток, щоб не ламати відправку email
            // Просто ігноруємо помилку
        }
    }
}

