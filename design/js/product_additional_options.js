/* Додавання опції до AJAX запиту при додаванні в кошик */
/* Перехоплюємо AJAX запити до cart_ajax і додаємо параметр additional_option_value_id */
(function($) {
    'use strict';
    
    $(document).on("ajaxSend", function(event, jqXHR, settings) {
    // Перевіряємо чи це запит до cart_ajax
    if (settings.url && typeof settings.url === 'string' && settings.url.indexOf('cart_ajax') !== -1) {
        // Перевіряємо чи це додавання товару в кошик
        // Може бути в URL параметрах або в data
        var isAddCitem = false;
        var urlHasAction = settings.url.indexOf('action=add_citem') !== -1;
        var dataHasAction = false;
        
        if (settings.data) {
            if (typeof settings.data === 'string' && settings.data.indexOf('action=add_citem') !== -1) {
                dataHasAction = true;
            } else if (typeof settings.data === 'object' && settings.data.action === 'add_citem') {
                dataHasAction = true;
            }
        }
        
        isAddCitem = urlHasAction || dataHasAction;
        
        if (isAddCitem) {
            // Знаходимо select з опцією
            var $additionalOption = $("select.fn_additional_option");
            
            // Якщо не знайдено через клас, шукаємо по name
            if (!$additionalOption || $additionalOption.length === 0) {
                $additionalOption = $("select[name='additional_option_value_id']");
            }
            
            if ($additionalOption && $additionalOption.length > 0) {
                var additionalOptionValueId = $additionalOption.val();
                
                if (additionalOptionValueId) {
                    // Якщо дані в URL
                    if (urlHasAction && !settings.url.includes('additional_option_value_id')) {
                        var separator = settings.url.indexOf('?') !== -1 ? '&' : '?';
                        settings.url += separator + 'additional_option_value_id=' + encodeURIComponent(additionalOptionValueId);
                    }
                    
                    // Додаємо параметр до data
                    if (typeof settings.data === 'string') {
                        // Якщо data - це рядок (URL encoded)
                        if (!settings.data.includes('additional_option_value_id')) {
                            settings.data += '&additional_option_value_id=' + encodeURIComponent(additionalOptionValueId);
                        }
                    } else if (typeof settings.data === 'object' && settings.data !== null) {
                        // Якщо data - це об'єкт
                        settings.data.additional_option_value_id = additionalOptionValueId;
                    }
                }
            }
        }
    }
    });
})(jQuery);

