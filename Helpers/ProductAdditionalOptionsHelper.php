<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers;

use Okay\Core\EntityFactory;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsValuesEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsCategoriesEntity;

class ProductAdditionalOptionsHelper
{
    private function getValuesEntity()
    {
        $SL = \Okay\Core\ServiceLocator::getInstance();
        return $SL->getService(ProductAdditionalOptionsValuesEntity::class);
    }

    private function getCategoriesEntity()
    {
        $SL = \Okay\Core\ServiceLocator::getInstance();
        return $SL->getService(ProductAdditionalOptionsCategoriesEntity::class);
    }

    /**
     * Отримати всі значення опцій
     */
    public function getAllValues()
    {
        $valuesEntity = $this->getValuesEntity();
        return $valuesEntity->order('position ASC')->find();
    }

    /**
     * Отримати значення опції за ID
     */
    public function getValue($valueId)
    {
        $valuesEntity = $this->getValuesEntity();
        return $valuesEntity->get($valueId);
    }

    /**
     * Отримати дані значення для відображення
     */
    public function getValueDisplayData($valueId)
    {
        $value = $this->getValue($valueId);
        if (empty($value)) {
            return null;
        }

        // Повертаємо об'єкт для зручного доступу в шаблонах
        $data = new \stdClass();
        $data->value_name = $value->name;
        $data->value_id = $value->id;
        
        return $data;
    }

    /**
     * Отримати категорії модуля
     */
    public function getModuleCategories()
    {
        $categoriesEntity = $this->getCategoriesEntity();
        $categories = $categoriesEntity->find();
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category->category_id;
        }
        return $categoryIds;
    }

    /**
     * Оновити категорії модуля
     */
    public function updateModuleCategories(array $categoryIds)
    {
        $categoriesEntity = $this->getCategoriesEntity();
        
        // Отримуємо всі поточні категорії
        $currentCategories = $categoriesEntity->find();
        $currentIds = [];
        foreach ($currentCategories as $cat) {
            $currentIds[] = $cat->id;
        }
        
        // Видаляємо категорії які не в новому списку
        if (!empty($currentIds)) {
            $categoriesEntity->delete($currentIds);
        }

        // Додаємо нові зв'язки
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categoriesEntity->add([
                    'category_id' => (int)$categoryId,
                ]);
            }
        }
    }

    /**
     * Перевірити, чи товар належить до категорій модуля
     */
    public function isProductInModuleCategories($product)
    {
        if (empty($product->id)) {
            return false;
        }

        try {
            $moduleCategoryIds = $this->getModuleCategories();

            if (empty($moduleCategoryIds)) {
                return false;
            }

            // Перевіряємо main_category_id
            if (!empty($product->main_category_id) && in_array($product->main_category_id, $moduleCategoryIds)) {
                return true;
            }

            // Перевіряємо категорії товару
            if (!empty($product->categories)) {
                foreach ($product->categories as $category) {
                    if (isset($category->id) && in_array($category->id, $moduleCategoryIds)) {
                        return true;
                    }
                }
            }

            // Якщо категорії не завантажені, завантажуємо їх
            try {
                $SL = \Okay\Core\ServiceLocator::getInstance();
                $categoriesEntity = $SL->getService(\Okay\Entities\CategoriesEntity::class);
                if ($categoriesEntity) {
                    $productCategories = $categoriesEntity->getProductCategories($product->id);
                    if (!empty($productCategories)) {
                        foreach ($productCategories as $pc) {
                            if (isset($pc->category_id) && in_array($pc->category_id, $moduleCategoryIds)) {
                                return true;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Якщо CategoriesEntity недоступний, просто ігноруємо
                // Це не критична помилка
            }
        } catch (\Exception $e) {
            // У разі помилки повертаємо false
            return false;
        }
        
        return false;
    }
}

