{* Title *}
{$meta_title = $btr->product_additional_options_title scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->product_additional_options_title|escape}
            </div>
        </div>
    </div>
</div>

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    {* Налаштування назви блоку опцій *}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="heading_box">
                <div class="box_heading box_heading_first">
                    {$btr->product_additional_options_block_title|escape}
                </div>
            </div>
            <form method="post" class="fn_form_list">
                <input type="hidden" name="session_id" value="{$smarty.session.id}">
                <div class="row">
                    {* Поле для поточної мови *}
                    <div class="col-lg-6 col-md-6">
                        <div class="heading_label">{$btr->product_additional_options_block_title_label|escape}</div>
                        <div class="form_group">
                            <input class="form-control" name="block_title" type="text" value="{$block_title|escape}" placeholder="{$btr->product_additional_options_block_title_default|escape}">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="heading_label">&nbsp;</div>
                        <div class="form_group">
                            <button type="submit" name="save_block_title" value="1" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_save|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
                {* Поля для інших мов *}
                {if count($languages) > 1}
                    <div class="row mt-1">
                        <div class="col-lg-12 col-md-12">
                            <div class="heading_label">{$btr->product_additional_options_other_languages|escape}</div>
                            <div class="row">
                                {foreach $languages as $lang}
                                    {if $lang->id != $current_lang_id}
                                        <div class="col-lg-4 col-md-4 col-sm-6 mb-1">
                                            <div class="heading_label mb-h">
                                                {if is_file("{$config->lang_images_dir|escape}{$lang->label|escape}.png")}
                                                    <span class="wrap_flag">
                                                        <img src="{("{$lang->label|escape}.png")|resize:32:32:false:$config->lang_resized_dir}" alt="{$lang->name|escape}" />
                                                    </span>
                                                {/if}
                                                <span>{$lang->name|escape}</span>
                                            </div>
                                            <div class="form_group">
                                                <input class="form-control" name="other_languages_block_titles[{$lang->id}]" type="text" value="{$block_titles_by_lang[{$lang->id}]|escape}" placeholder="{$btr->product_additional_options_block_title_default|escape} ({$lang->name|escape})">
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}
            </form>
        </div>
    </div>

    {* Форма для додавання нового значення *}
    <div class="row mt-1">
        <div class="col-lg-12 col-md-12">
            <div class="heading_box">
                <div class="box_heading box_heading_first">
                    {$btr->product_additional_options_add_value|escape}
                </div>
            </div>
            <form method="post" class="fn_form_list">
                <input type="hidden" name="session_id" value="{$smarty.session.id}">
                <div class="row">
                    {* Поле для поточної мови *}
                    <div class="col-lg-6 col-md-6">
                        <div class="heading_label">{$btr->product_additional_options_value_name|escape}</div>
                        <div class="form_group">
                            <input class="form-control" name="name" type="text" value="" placeholder="{$btr->product_additional_options_value_name|escape}" required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="heading_label">&nbsp;</div>
                        <div class="form_group">
                            <button type="submit" name="add_value" value="1" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='plus'}
                                <span>{$btr->product_additional_options_add|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
                {* Поля для інших мов *}
                {if count($languages) > 1}
                    <div class="row mt-1">
                        <div class="col-lg-12 col-md-12">
                            <div class="heading_label">{$btr->product_additional_options_other_languages|escape}</div>
                            <div class="row">
                                {foreach $languages as $lang}
                                    {if $lang->id != $current_lang_id}
                                        <div class="col-lg-4 col-md-4 col-sm-6 mb-1">
                                            <div class="heading_label mb-h">
                                                {if is_file("{$config->lang_images_dir|escape}{$lang->label|escape}.png")}
                                                    <span class="wrap_flag">
                                                        <img src="{("{$lang->label|escape}.png")|resize:32:32:false:$config->lang_resized_dir}" />
                                                    </span>
                                                {/if}
                                                {$lang->name|escape}
                                            </div>
                                            <div class="form_group">
                                                <input class="form-control" name="other_languages_names[{$lang->id}]" type="text" value="" placeholder="{$btr->product_additional_options_value_name|escape} ({$lang->name|escape})">
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}
            </form>
        </div>
    </div>

    {if $values}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <form id="list_form" method="post" class="fn_form_list">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">

                    <div class="pages_wrap okay_list">
                        {*Шапка таблицы*}
                        <div class="okay_list_head">
                            <div class="okay_list_boding okay_list_drag"></div>
                            <div class="okay_list_heading okay_list_check">
                                <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                                <label class="okay_ckeckbox" for="check_all_1"></label>
                            </div>
                            <div class="okay_list_heading okay_list_option_name">{$btr->product_additional_options_value_name|escape}</div>
                            <div class="okay_list_heading okay_list_close"></div>
                        </div>

                        {*Параметры элемента*}
                        <div id="sortable" class="okay_list_body sortable">
                            {foreach $values as $value}
                                <div class="fn_row okay_list_body_item">
                                    <div class="okay_list_row">
                                        <input type="hidden" name="positions[{$value->id}]" value="{$value->position|escape}">

                                        <div class="okay_list_boding okay_list_drag move_zone">
                                            {include file='svg_icon.tpl' svgId='drag_vertical'}
                                        </div>

                                        <div class="okay_list_boding okay_list_check">
                                            <input class="hidden_check" type="checkbox" id="id_{$value->id}" name="check[]" value="{$value->id}"/>
                                            <label class="okay_ckeckbox" for="id_{$value->id}"></label>
                                        </div>

                                        <div class="okay_list_boding okay_list_option_name">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6">
                                                    <input class="form-control" name="values[{$value->id}]" type="text" value="{$value->name|escape}" placeholder="{$btr->product_additional_options_value_name|escape}">
                                                </div>
                                                {if count($languages) > 1}
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="heading_label mb-h">{$btr->product_additional_options_other_languages|escape}</div>
                                                        <div class="row">
                                                            {foreach $languages as $lang}
                                                                {if $lang->id != $current_lang_id}
                                                                    <div class="col-lg-6 col-md-6 mb-h">
                                                                        <div class="heading_label mb-h" style="font-size: 11px;">
                                                                            {if is_file("{$config->lang_images_dir|escape}{$lang->label|escape}.png")}
                                                                                <span class="wrap_flag">
                                                                                    <img src="{("{$lang->label|escape}.png")|resize:16:16:false:$config->lang_resized_dir}" alt="{$lang->name|escape}" />
                                                                                </span>
                                                                            {/if}
                                                                            <span>{$lang->name|escape}</span>
                                                                        </div>
                                                                        <input class="form-control" name="other_languages_names[{$value->id}][{$lang->id}]" type="text" value="{$value->names[{$lang->id}]|escape}" placeholder="{$btr->product_additional_options_value_name|escape} ({$lang->name|escape})" style="font-size: 12px;">
                                                                    </div>
                                                                {/if}
                                                            {/foreach}
                                                        </div>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>

                                        <div class="okay_list_boding okay_list_close">
                                            {*delete*}
                                            <button data-hint="{$btr->pages_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                                {include file='svg_icon.tpl' svgId='trash'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        {*Блок массовых действий*}
                        <div class="okay_list_footer fn_action_block">
                            <div class="okay_list_foot_left">
                                <div class="okay_list_boding okay_list_drag"></div>
                                <div class="okay_list_heading okay_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                                    <label class="okay_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="okay_list_option">
                                    <select name="action" class="selectpicker form-control">
                                        <option value="delete">{$btr->general_delete|escape}</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="update_values" value="1" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->product_additional_options_no_values|escape}</div>
        </div>
    {/if}

    {* Блок вибору категорій *}
    <div class="row mt-1">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_210px">
                <div class="heading_box">
                    <div class="box_heading box_heading_first">
                        {$btr->product_additional_options_use_in_categories|escape}
                    </div>
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="icon-arrow-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="alert alert--icon alert--error">
                        <div class="alert__content">
                            <div class="alert__title">{$btr->alert_error|escape}</div>
                            <p>{$btr->product_additional_options_categories_message|escape}</p>
                        </div>
                    </div>

                    <form method="post" class="fn_form_list">
                        <input type="hidden" name="session_id" value="{$smarty.session.id}">
                        <button id="select_all_categories" type="button" class="btn btn_small btn-secondary mb-1">
                            {include file='svg_icon.tpl' svgId='checked'}
                            <span>{$btr->product_additional_options_select_all_categories|escape}</span>
                        </button>

                        <select class="selectpicker form-control fn_select_all_categories col-xs-12 px-0" multiple name="module_categories[]" size="10" data-selected-text-format="count">
                            {function name=category_select level=0}
                                {foreach $categories as $category}
                                    <option value='{$category->id}' {if in_array($category->id, $module_categories)}selected{/if} category_name='{$category->single_name|escape}'>{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name|escape}</option>
                                    {category_select categories=$category->subcategories level=$level+1}
                                {/foreach}
                            {/function}
                            {category_select categories=$categories}
                        </select>

                        <div class="mt-1">
                            <button type="submit" name="save_categories" value="1" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_save|escape}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '#select_all_categories', function () {
    $('.fn_select_all_categories option').prop("selected", true);
    $('.fn_select_all_categories').selectpicker('refresh');
});
</script>

