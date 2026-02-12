<?php

namespace Okay\Modules\Lavvod\ProductAdditionalOptions\Backend\Controllers;

use Okay\Modules\Lavvod\ProductAdditionalOptions\Entities\ProductAdditionalOptionsValuesEntity;
use Okay\Modules\Lavvod\ProductAdditionalOptions\Helpers\ProductAdditionalOptionsHelper;
use Okay\Admin\Controllers\IndexAdmin;
use Okay\Admin\Helpers\BackendCategoriesHelper;
use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Core\Languages;
use Okay\Core\QueryFactory;
use Okay\Entities\LanguagesEntity;

class ProductAdditionalOptionsAdmin extends IndexAdmin
{
    public function fetch(
        EntityFactory $entityFactory,
        Settings $settings,
        ProductAdditionalOptionsHelper $helper,
        BackendCategoriesHelper $backendCategoriesHelper,
        Languages $languagesCore,
        LanguagesEntity $languagesEntity,
        QueryFactory $queryFactory
    ) {
        /** @var ProductAdditionalOptionsValuesEntity $valuesEntity */
        $valuesEntity = $entityFactory->get(ProductAdditionalOptionsValuesEntity::class);

        // Отримуємо всі мови
        $languages = $languagesEntity->mappedBy('id')->find();
        $currentLangId = $languagesCore->getLangId();
        $this->design->assign('languages', $languages);
        $this->design->assign('current_lang_id', $currentLangId);

        if ($this->request->method('post')) {
            // Збереження назви блоку опцій з мультимовністю
            if ($this->request->post('save_block_title')) {
                $currentLangId = $languagesCore->getLangId();
                $blockTitle = $this->request->post('block_title', 'string');
                $otherLanguagesBlockTitles = $this->request->post('other_languages_block_titles');
                
                $param = 'lavvod_product_additional_options_block_title';
                
                // Зберігаємо для поточної мови
                if ($blockTitle !== null) {
                    $this->saveSettingForLanguage($queryFactory, $param, $blockTitle, $currentLangId);
                }
                
                // Зберігаємо для інших мов
                if (!empty($otherLanguagesBlockTitles) && is_array($otherLanguagesBlockTitles)) {
                    foreach ($otherLanguagesBlockTitles as $langId => $langBlockTitle) {
                        $langId = (int)$langId;
                        if ($langBlockTitle !== null) {
                            $this->saveSettingForLanguage($queryFactory, $param, $langBlockTitle, $langId);
                        }
                    }
                }
                
                // Оновлюємо кеш Settings
                $settings->initSettings();
            }
            
            // Збереження категорій
            if ($this->request->post('save_categories')) {
                $categoryIds = $this->request->post('module_categories', 'array');
                if ($categoryIds === null) {
                    $categoryIds = [];
                }
                $helper->updateModuleCategories($categoryIds);
            }
            
            // Додавання нового значення з мультимовністю
            if ($this->request->post('add_value')) {
                $currentLangId = $languagesCore->getLangId();
                $name = $this->request->post('name', 'string');
                $otherLanguagesNames = $this->request->post('other_languages_names', 'array');
                
                if (!empty($name)) {
                    // Отримуємо максимальну позицію
                    $allValues = $valuesEntity->order('position DESC')->find();
                    $position = 0;
                    if (!empty($allValues)) {
                        $maxValue = reset($allValues);
                        $position = $maxValue->position + 1;
                    }
                    
                    // Додаємо значення для поточної мови
                    $valueId = $valuesEntity->add([
                        'name' => $name,
                        'position' => $position,
                    ]);
                    
                    // Додаємо значення для інших мов
                    if (!empty($valueId) && !empty($otherLanguagesNames)) {
                        foreach ($otherLanguagesNames as $langId => $langName) {
                            if (!empty($langName)) {
                                $languagesCore->setLangId($langId);
                                $valuesEntity->update($valueId, ['name' => $langName]);
                            }
                        }
                    }
                    
                    $languagesCore->setLangId($currentLangId);
                }
            }
            
            // Оновлення значень з мультимовністю
            if ($this->request->post('update_values')) {
                $currentLangId = $languagesCore->getLangId();
                $valuesData = $this->request->post('values', 'array');
                $otherLanguagesNames = $this->request->post('other_languages_names', 'array');
                
                if (!empty($valuesData)) {
                    foreach ($valuesData as $valueId => $valueName) {
                        if (!empty($valueName)) {
                            // Оновлюємо для поточної мови
                            $valuesEntity->update($valueId, ['name' => $valueName]);
                            
                            // Оновлюємо для інших мов
                            if (!empty($otherLanguagesNames[$valueId])) {
                                foreach ($otherLanguagesNames[$valueId] as $langId => $langName) {
                                    if (!empty($langName)) {
                                        $languagesCore->setLangId($langId);
                                        $valuesEntity->update($valueId, ['name' => $langName]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $languagesCore->setLangId($currentLangId);
            }
            
            // Видалення значень
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        $valuesEntity->delete($ids);
                        break;
                    }
                }
            }

            // Сортировка через drag-n-drop
            $positions = $this->request->post('positions');
            if (!empty($positions)) {
                $ids = array_keys($positions);
                sort($positions);
                foreach($positions as $i => $position) {
                    $valuesEntity->update($ids[$i], ['position' => $position]);
                }
            }
        }

        // Отримуємо назву блоку опцій для поточної мови
        $currentLangId = $languagesCore->getLangId();
        $blockTitle = $settings->get('lavvod_product_additional_options_block_title');
        if (empty($blockTitle)) {
            $blockTitle = 'Опції';
        }
        
        // Отримуємо назви блоку для всіх мов через прямий SQL запит
        $param = 'lavvod_product_additional_options_block_title';
        $select = $queryFactory->newSelect();
        $select->from('__settings_lang')
            ->cols(['lang_id', 'value'])
            ->where('param = :param')
            ->bindValue('param', $param);
        
        $this->db->query($select);
        $settingsResults = $this->db->results();
        
        $blockTitlesByLang = [];
        foreach ($languages as $lang) {
            $langBlockTitle = 'Опції'; // За замовчуванням
            // Шукаємо значення для цієї мови
            foreach ($settingsResults as $setting) {
                if ($setting->lang_id == $lang->id) {
                    $langBlockTitle = $setting->value;
                    break;
                }
            }
            $blockTitlesByLang[$lang->id] = $langBlockTitle;
        }

        // Отримуємо категорії модуля
        $moduleCategories = $helper->getModuleCategories();
        
        // Отримуємо дерево категорій
        $categories = $backendCategoriesHelper->getCategoriesTree();

        // Отримуємо всі значення з мультимовними даними
        $currentLangId = $languagesCore->getLangId();
        $values = $valuesEntity->order('position ASC')->find();
        
        // Для кожного значення отримуємо назви для всіх мов
        $valuesWithLanguages = [];
        foreach ($values as $value) {
            $valueData = new \stdClass();
            $valueData->id = $value->id;
            $valueData->position = $value->position;
            $valueData->names = [];
            
            // Отримуємо назву для кожної мови
            foreach ($languages as $lang) {
                $languagesCore->setLangId($lang->id);
                $langValue = $valuesEntity->get($value->id);
                $valueData->names[$lang->id] = $langValue ? $langValue->name : '';
            }
            
            // Встановлюємо поточну мову назад
            $languagesCore->setLangId($currentLangId);
            $currentValue = $valuesEntity->get($value->id);
            $valueData->name = $currentValue ? $currentValue->name : '';
            
            $valuesWithLanguages[] = $valueData;
        }
        
        $this->design->assign('values', $valuesWithLanguages);
        $this->design->assign('values_count', count($valuesWithLanguages));
        $this->design->assign('block_title', $blockTitle);
        $this->design->assign('block_titles_by_lang', $blockTitlesByLang);
        $this->design->assign('categories', $categories);
        $this->design->assign('module_categories', $moduleCategories);
        
        $this->response->setContent($this->design->fetch('product_additional_options.tpl'));
    }
    
    /**
     * Зберігає налаштування для конкретної мови
     */
    private function saveSettingForLanguage(QueryFactory $queryFactory, $param, $value, $langId)
    {
        $db = $this->db;
        $value = is_array($value) ? serialize($value) : (string) $value;
        $langId = (int)$langId;
        
        // Перевіряємо, чи існує запис для цієї мови
        $select = $queryFactory->newSelect();
        $select->from('__settings_lang')
            ->cols(['id'])
            ->where('param = :param')
            ->where('lang_id = :lang_id')
            ->bindValue('param', $param)
            ->bindValue('lang_id', $langId)
            ->limit(1);
        
        $db->query($select);
        $existing = $db->result('id');
        
        if ($existing) {
            // Оновлюємо існуючий запис
            $sql = $queryFactory->newSqlQuery();
            $sql->setStatement("UPDATE `__settings_lang` SET `value` = :value 
                                WHERE `param` = :param AND `lang_id` = :lang_id");
            $sql->bindValue('param', $param);
            $sql->bindValue('value', $value);
            $sql->bindValue('lang_id', $langId);
            
            $db->query($sql);
        } else {
            // Додаємо новий запис через REPLACE INTO
            $sql = $queryFactory->newSqlQuery();
            $sql->setStatement("REPLACE INTO `__settings_lang` (`param`, `value`, `lang_id`) 
                                VALUES (:param, :value, :lang_id)");
            $sql->bindValue('param', $param);
            $sql->bindValue('value', $value);
            $sql->bindValue('lang_id', $langId);
            
            $db->query($sql);
        }
        
        return true;
    }
}

