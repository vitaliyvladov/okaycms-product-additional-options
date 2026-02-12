<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Extenders;

use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers\ProductAdditionalOptionsHelper;

class ProductExtender implements ExtensionInterface
{
    private function getHelper()
    {
        $SL = ServiceLocator::getInstance();
        return $SL->getService(ProductAdditionalOptionsHelper::class);
    }

    private function getSettings()
    {
        $SL = ServiceLocator::getInstance();
        return $SL->getService(Settings::class);
    }

    /**
     * Додаємо значення опцій до товару
     */
    public function get($product)
    {
        try {
            if (empty($product)) {
                return $product;
            }

            try {
                $helper = $this->getHelper();
            } catch (\Exception $e) {
                // Якщо Helper недоступний, повертаємо товар без змін
                return $product;
            }
            
            // Перевіряємо чи товар в категоріях модуля
            // Якщо категорій модуля немає, показуємо опції для всіх товарів
            try {
                $moduleCategories = $helper->getModuleCategories();
                
                if (!empty($moduleCategories)) {
                    $isInCategories = $helper->isProductInModuleCategories($product);
                    if (!$isInCategories) {
                        return $product;
                    }
                }
            } catch (\Exception $e) {
                // Якщо помилка при перевірці категорій, просто продовжуємо
            }
            
            try {
                $values = $helper->getAllValues();
                
                if (!empty($values) && is_array($values)) {
                    $product->additional_option_values = $values;
                    // Автоматично вибираємо перший варіант
                    if (!empty($values[0]) && !empty($values[0]->id)) {
                        $product->additional_option_selected_value_id = $values[0]->id;
                    }
                    
                    // Додаємо назву блоку опцій
                    try {
                        $settings = $this->getSettings();
                        $blockTitle = $settings->get('lavvod_product_additional_options_block_title');
                        if (empty($blockTitle)) {
                            $blockTitle = 'Опції';
                        }
                        $product->additional_option_block_title = $blockTitle;
                    } catch (\Exception $e) {
                        // Якщо помилка при отриманні налаштувань, використовуємо за замовчуванням
                        $product->additional_option_block_title = 'Опції';
                    }
                }
            } catch (\Exception $e) {
                // Якщо помилка при отриманні values, просто повертаємо товар без змін
            }
        } catch (\Exception $e) {
            // Критична помилка - повертаємо товар як є
        }

        return $product;
    }
}

