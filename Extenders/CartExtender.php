<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders;

use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Classes\Purchase;
use Okay\Core\ServiceLocator;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers\ProductAdditionalOptionsHelper;

class CartExtender implements ExtensionInterface
{
    private function getRequest()
    {
        $SL = ServiceLocator::getInstance();
        return $SL->getService(Request::class);
    }

    private function getHelper()
    {
        $SL = ServiceLocator::getInstance();
        return $SL->getService(ProductAdditionalOptionsHelper::class);
    }

    /**
     * Зберігаємо опцію в сесії при додаванні в кошик
     */
    public function addItem($cart, $variantId, $amount = 1)
    {
        $request = $this->getRequest();
        $optionValueId = $request->post('additional_option_value_id', 'integer');
        if (empty($optionValueId)) {
            $optionValueId = $request->get('additional_option_value_id', 'integer');
        }
        
        if (!empty($optionValueId)) {
            if (!isset($_SESSION['product_additional_options'])) {
                $_SESSION['product_additional_options'] = [];
            }
            $_SESSION['product_additional_options'][$variantId] = $optionValueId;
        }
    }

    /**
     * Додаємо опцію до Purchase
     */
    public function addPurchase($purchase, $variantId, $amount = 1)
    {
        if (empty($purchase)) {
            return $purchase;
        }

        // Отримуємо variant_id якщо не передано
        if (empty($variantId) && !empty($purchase->variant->id)) {
            $variantId = $purchase->variant->id;
        } elseif (empty($variantId) && !empty($purchase->variant_id)) {
            $variantId = $purchase->variant_id;
        }

        $optionValueId = null;
        if (!empty($variantId) && isset($_SESSION['product_additional_options'][$variantId])) {
            $optionValueId = $_SESSION['product_additional_options'][$variantId];
        }

        if (!empty($optionValueId)) {
            try {
                $helper = $this->getHelper();
                $valueData = $helper->getValueDisplayData($optionValueId);
                
                if (!empty($valueData)) {
                    if (!isset($purchase->meta)) {
                        $purchase->meta = new \stdClass();
                    }
                    $purchase->meta->additional_option = $valueData;
                }
            } catch (\Exception $e) {
                // Якщо помилка при отриманні опції, просто ігноруємо
            }
        }

        return $purchase;
    }

    /**
     * Оновлюємо опцію при оновленні товару в кошику
     */
    public function updateItem($cart, $variantId, $amount = 1)
    {
        $request = $this->getRequest();
        $optionValueId = $request->post('additional_option_value_id', 'integer');
        if (empty($optionValueId)) {
            $optionValueId = $request->get('additional_option_value_id', 'integer');
        }
        if (!empty($optionValueId)) {
            if (!isset($_SESSION['product_additional_options'])) {
                $_SESSION['product_additional_options'] = [];
            }
            $_SESSION['product_additional_options'][$variantId] = $optionValueId;
        }
    }

    /**
     * Оновлюємо опцію в Purchase при оновленні
     */
    public function updatePurchase($purchase, $variantId, $amount)
    {
        if (empty($purchase)) {
            return $purchase;
        }

        // Отримуємо variant_id якщо не передано
        if (empty($variantId) && !empty($purchase->variant->id)) {
            $variantId = $purchase->variant->id;
        } elseif (empty($variantId) && !empty($purchase->variant_id)) {
            $variantId = $purchase->variant_id;
        }

        $optionValueId = null;
        if (!empty($variantId) && isset($_SESSION['product_additional_options'][$variantId])) {
            $optionValueId = $_SESSION['product_additional_options'][$variantId];
        }

        if (!empty($optionValueId)) {
            try {
                $helper = $this->getHelper();
                $valueData = $helper->getValueDisplayData($optionValueId);
                if (!empty($valueData)) {
                    if (!isset($purchase->meta)) {
                        $purchase->meta = new \stdClass();
                    }
                    $purchase->meta->additional_option = $valueData;
                }
            } catch (\Exception $e) {
                // Якщо помилка при отриманні опції, просто ігноруємо
                // Щоб не ламати роботу кошика
            }
        }

        return $purchase;
    }

    /**
     * Отримуємо опції для всіх покупок в кошику
     */
    public function getPurchases($purchases, $purchasesVariants)
    {
        if (empty($purchases)) {
            return $purchases;
        }

        try {
            $helper = $this->getHelper();
            foreach ($purchases as $purchase) {
                // Отримуємо variant_id з purchase
                $variantId = null;
                if (!empty($purchase->variant->id)) {
                    $variantId = $purchase->variant->id;
                } elseif (!empty($purchase->variant_id)) {
                    $variantId = $purchase->variant_id;
                }
                
                // Перевіряємо чи вже є опція
                if (isset($purchase->meta) && isset($purchase->meta->additional_option)) {
                    continue;
                }
                
                if (!empty($variantId) && isset($_SESSION['product_additional_options'][$variantId])) {
                    $optionValueId = $_SESSION['product_additional_options'][$variantId];
                    
                    try {
                        $valueData = $helper->getValueDisplayData($optionValueId);
                        
                        if (!empty($valueData)) {
                            if (!isset($purchase->meta)) {
                                $purchase->meta = new \stdClass();
                            }
                            $purchase->meta->additional_option = $valueData;
                        }
                    } catch (\Exception $e) {
                        // Якщо помилка при отриманні опції, просто ігноруємо
                    }
                }
            }
        } catch (\Exception $e) {
            // Якщо помилка, повертаємо purchases як є
        }

        return $purchases;
    }
}

