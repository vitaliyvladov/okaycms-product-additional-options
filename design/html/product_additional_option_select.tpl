{if !empty($product->additional_option_values)}
    <div class="details_boxed__item">
        <div class="details_boxed__select">
            <div class="details_boxed__title">{$product->additional_option_block_title|escape}:</div>
            <select name="additional_option_value_id" class="fn_additional_option variant_select fn_select2">
                {foreach $product->additional_option_values as $value}
                    <option value="{$value->id}" {if $product->additional_option_selected_value_id == $value->id}selected{/if}>
                        {$value->name|escape}
                    </option>
                {/foreach}
            </select>
            <div class="dropDownSelect2"></div>
        </div>
    </div>
{/if}

