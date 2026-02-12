<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders;

use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\EntityFactory;
use Okay\Core\ServiceLocator;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\PurchasesAdditionalOptionsEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers\ProductAdditionalOptionsHelper;

class OrderExtender implements ExtensionInterface
{
    private function getEntityFactory()
    {
        $SL = ServiceLocator::getInstance();
        return $SL->getService(EntityFactory::class);
    }

    private function getPurchasesOptionsEntity()
    {
        $entityFactory = $this->getEntityFactory();
        return $entityFactory->get(PurchasesAdditionalOptionsEntity::class);
    }

    /**
     * Зберігаємо опції при створенні замовлення
     */
    public function cartToOrder($preparedCart, $orderId)
    {
        try {
            if (empty($preparedCart) || empty($preparedCart->purchases) || empty($preparedCart->purchasesToDB)) {
                return $preparedCart;
            }

            // purchasesToDB вже містить purchases з id після додавання в БД
            foreach ($preparedCart->purchasesToDB as $index => $purchaseDB) {
                if (!isset($preparedCart->purchases[$index])) {
                    continue;
                }

                $purchase = $preparedCart->purchases[$index];
                
                // Перевіряємо наявність meta та additional_option
                if (!isset($purchase->meta) || !isset($purchase->meta->additional_option) || empty($purchase->meta->additional_option)) {
                    continue;
                }

                $valueData = $purchase->meta->additional_option;

                // Перевіряємо структуру valueData
                if (!is_object($valueData) || empty($valueData->value_id) || empty($valueData->value_name)) {
                    continue;
                }

                if (empty($purchaseDB->id)) {
                    continue;
                }

                try {
                    $purchasesOptionsEntity = $this->getPurchasesOptionsEntity();
                    $purchasesOptionsEntity->add([
                        'purchase_id' => $purchaseDB->id,
                        'value_id' => $valueData->value_id,
                        'value_name' => $valueData->value_name,
                    ]);
                } catch (\Exception $e) {
                    // Якщо помилка при збереженні, просто ігноруємо
                }
            }
        } catch (\Exception $e) {
            // Якщо критична помилка, повертаємо preparedCart як є
        }

        return $preparedCart;
    }

    /**
     * Отримуємо опції для замовлення
     */
    public function getOrderPurchasesList($purchases, $orderId)
    {
        try {
            if (empty($purchases) || $purchases === false) {
                return $purchases;
            }

            $purchaseIds = [];
            foreach ($purchases as $purchase) {
                if (!empty($purchase->id)) {
                    $purchaseIds[] = $purchase->id;
                }
            }

            if (empty($purchaseIds)) {
                return $purchases;
            }

            try {
                $purchasesOptionsEntity = $this->getPurchasesOptionsEntity();
                $purchasesOptions = $purchasesOptionsEntity->find(['purchase_id' => $purchaseIds]);
                
                $optionsByPurchase = [];
                if (!empty($purchasesOptions)) {
                    foreach ($purchasesOptions as $po) {
                        if (!empty($po->purchase_id)) {
                            $optionsByPurchase[$po->purchase_id] = $po;
                        }
                    }
                }

                foreach ($purchases as $purchase) {
                    if (!empty($purchase->id) && isset($optionsByPurchase[$purchase->id])) {
                        $po = $optionsByPurchase[$purchase->id];
                        
                        if (!empty($po->value_name)) {
                            // Додаємо опцію до additional_option
                            $purchase->additional_option = [
                                'value_name' => $po->value_name,
                            ];
                            
                            // Також додаємо опцію безпосередньо до variant_name для відображення
                            if (!empty($purchase->variant_name)) {
                                $purchase->variant_name = $purchase->variant_name . ' | ' . $po->value_name;
                            } else {
                                $purchase->variant_name = $po->value_name;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Якщо помилка при отриманні опцій, повертаємо purchases як є
            }
        } catch (\Exception $e) {
            // Якщо критична помилка, повертаємо purchases як є
        }

        return $purchases;
    }

    /**
     * Отримуємо опції для замовлення в адмін-панелі
     */
    public function findOrderPurchases($purchases, $order)
    {
        try {
            if (empty($purchases) || $purchases === false) {
                return $purchases;
            }

            $purchaseIds = [];
            foreach ($purchases as $key => $purchase) {
                // Якщо purchases mappedBy('id'), то ключ = id
                $purchaseId = null;
                if (is_numeric($key)) {
                    if (is_object($purchase) && !empty($purchase->id)) {
                        $purchaseId = $purchase->id;
                    } elseif (is_array($purchase) && !empty($purchase['id'])) {
                        $purchaseId = $purchase['id'];
                    }
                } else {
                    // Якщо ключ не числовий (mappedBy), то ключ = id
                    $purchaseId = $key;
                }
                
                if (!empty($purchaseId)) {
                    $purchaseIds[] = $purchaseId;
                }
            }

            if (empty($purchaseIds)) {
                return $purchases;
            }

            try {
                $purchasesOptionsEntity = $this->getPurchasesOptionsEntity();
                $purchasesOptions = $purchasesOptionsEntity->find(['purchase_id' => $purchaseIds]);
                
                $optionsByPurchase = [];
                if (!empty($purchasesOptions)) {
                    foreach ($purchasesOptions as $po) {
                        if (!empty($po->purchase_id)) {
                            $optionsByPurchase[$po->purchase_id] = $po;
                        }
                    }
                }

                foreach ($purchases as $key => $purchase) {
                    // Визначаємо purchaseId
                    $purchaseId = null;
                    if (is_numeric($key)) {
                        if (is_object($purchase) && !empty($purchase->id)) {
                            $purchaseId = $purchase->id;
                        } elseif (is_array($purchase) && !empty($purchase['id'])) {
                            $purchaseId = $purchase['id'];
                        }
                    } else {
                        // Якщо ключ не числовий (mappedBy), то ключ = id
                        $purchaseId = $key;
                    }
                    
                    if (!empty($purchaseId) && isset($optionsByPurchase[$purchaseId])) {
                        $po = $optionsByPurchase[$purchaseId];
                        
                        if (!empty($po->value_name)) {
                            if (is_object($purchase)) {
                                // Додаємо опцію до additional_option
                                $purchase->additional_option = [
                                    'value_name' => $po->value_name,
                                ];
                                
                                // Також додаємо опцію безпосередньо до variant_name для відображення
                                if (!empty($purchase->variant_name)) {
                                    $purchase->variant_name = $purchase->variant_name . ' | ' . $po->value_name;
                                } else {
                                    $purchase->variant_name = $po->value_name;
                                }
                            } elseif (is_array($purchase)) {
                                $purchases[$key]['additional_option'] = [
                                    'value_name' => $po->value_name,
                                ];
                                
                                // Також додаємо опцію безпосередньо до variant_name
                                if (!empty($purchases[$key]['variant_name'])) {
                                    $purchases[$key]['variant_name'] = $purchases[$key]['variant_name'] . ' | ' . $po->value_name;
                                } else {
                                    $purchases[$key]['variant_name'] = $po->value_name;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Якщо помилка при отриманні опцій, повертаємо purchases як є
            }
        } catch (\Exception $e) {
            // Якщо критична помилка, повертаємо purchases як є
        }

        return $purchases;
    }
}

