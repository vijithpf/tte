<?php   defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<style>
.ccm-note { color: #999; font-size: 11px; font-style: italic; line-height: 14px; margin-bottom: 10px; }
.input { margin-left: 150px; } 
.searchrule a.mover { float: left; cursor: move; background: url(<?php  echo ASSETS_URL_IMAGES?>/icons_sprite.png) no-repeat top left; height: 20px; width: 20px; margin-left: 10px !important; background-position: -23px -1093px !important;}
.searchrule a.remove { cursor:pointer;height:16px;width:25px;float:left; margin-left:5px; }
.option_button{float:left!important;font-size:18px!important;line-height:18px!important;padding:4px!important;margin-left:5px!important;width:18px!important;text-align:center!important}
label.checkbox {margin-top: 4px;}
.ccm-ui .input-prepend input, .ccm-ui .input-prepend .add-on, .ccm-ui .input-append .add-on { position: static !important; }
.add-on {float:left;}
</style>

<?php 
$pkg = Package::getByHandle('formidable');

Loader::model('block_types');
$bt = BlockType::getByHandle('results');
$tools_dir = Loader::helper('concrete/urls')->getBlockTypeToolsURL($bt);
?>

<script>

var search_values = [['any_value', '<?php  echo t('any value') ?>'], ['no_value', '<?php  echo t('no value'); ?>']];
var search_condition_placeholder = '<?php  echo t('Value') ?>';
var regex_condition_placeholder = '/[0-9A-z]{5}/i';
var search_confirm = '<?php  echo t('Are you sure you want to delete this search rule?'); ?>';
var condition_values = [['empty', '<?php  echo t('is empty'); ?>'], ['not_empty', '<?php  echo ('is not empty') ?>'], ['equals', '<?php  echo ('equals') ?>'], ['not_equals', '<?php  echo ('not equal to') ?>'], ['contains', '<?php  echo ('contains') ?>'], ['not_contains', '<?php  echo t('does not contain') ?>']];

var formID = <?php  echo intval($formID); ?>;
var bID = <?php  echo intval($bID); ?>;

ccmFormidableLoadForm = function(fID) {
    
    formID = fID;
    
    $.ajax({ 
        type: "POST",
        url: '<?php  echo $tools_dir; ?>/searchables',
        data: 'formID='+formID+'&bID='+bID,
        beforeSend: function () {

        },
        success: function(html) {
            $('#ccm-tab-content-query').html(html);

            $("#searchrule").sortable({
                items: "div.search",
                handle: "a.mover",
                sort: function(event, ui) {
                    $(this).removeClass( "ui-state-default" );
                },
                stop: function(event, ui) {
                    $("#searchrule").find('.search').each(function(i, row) {
                        $(row).find('span.rule').text(i + 1);
                        if (i == 0) $(row).find('div.operator').hide(); 
                        else $(row).find('div.operator').show();
                    });
                }
            });

        }
    }); 

    $.ajax({ 
        type: "POST",
        url: '<?php  echo $tools_dir; ?>/columns',
        data: 'formID='+formID+'&bID='+bID,
        beforeSend: function () {

        },
        success: function(html) {
            $('#ccm-tab-content-columns').html(html);
            
            $('#ccm-sortable-column-wrapper').sortable({
                cursor: 'move',
                opacity: 0.5
            });

            $('#ccm-tab-content-columns input[type=checkbox]').click(function() {
                var thisLabel = $(this).parent().find('span').html();
                var thisID = $(this).attr('id');
                if ($(this).prop('checked')) {
                    if ($('#field_' + thisID).length == 0) {
                        $('#ccm-sortable-column-default').append('<option value="' + thisID + '" id="opt_' + thisID + '">' + thisLabel + '<\/option>');
                        $('div.ccm-sortable-column-sort-controls select').attr('disabled', false);
                        $('#ccm-sortable-column-wrapper').append('<li id="field_' + thisID + '"><input type="hidden" name="column[]" value="' + thisID + '" />' + thisLabel + '<\/li>');
                    }
                } else {
                    $('#field_' + thisID).remove();
                    $('#opt_' + thisID).remove();
                    if ($('#ccm-sortable-column-wrapper li').length == 0) {
                        $('div.ccm-sortable-column-sort-controls select').attr('disabled', true);
                    }
                }
            });  
        }
    }); 
}

ccmFormidableSearchCheck = function(s) {
    var element = $("input[name=limit_value]");
    if ($('input[name=limit]').is(':checked')) {
        element.attr('disabled', false);
        if (s && s.attr('name') == 'limit') element.focus();
    } else 
        element.val("").attr('disabled', true);
}

ccmFormidableAddSearch = function(rule) {    
    var objDep = $('#searchrule');
    if (rule === undefined) rule = parseInt(objDep.attr('data-next_rule'));
    $.ajax({ 
        type: "POST",
        url: '<?php  echo $tools_dir; ?>/tools',
        data: 'action=add_search&rule='+rule+'&formID='+formID+'&bID='+bID,
        dataType: 'html',
        beforeSend: function () {
            //console.log('loading');
        },
        success: function(ret) {
            objDep.append(ret).attr('data-next_rule', rule+1);
            ccmFormidableInitSearch(rule);
            ccmFormidableInitSearchElement(rule, 0);
        }
    });     
}

ccmFormidableInitSearch = function(rule) {
        
    var objRule = $('div#search_rule_'+rule);
        
    objRule.find('div.searchelements, div.operator').hide();
    
    if ($('div#searchrule').children().length > 1 && rule > 0) {
        objRule.find('div.operator').show();
    }

    $('div#search_rule_'+rule+' div.searchelements').show();

    if ($('div#searchrule').children().length <= 0) {        
        ccmFormidableAddSearchElement(rule);
    }

    $('div#searchrule').find('div.searchrule').each(function(i, row) {
        $(row).find('span.rule').text(i + 1);
    });
}

ccmFormidableDeleteSearch = function(rule) {
    var objRule = $('div#search_rule_'+rule);   
    if (confirm(search_confirm)) {
        objRule.remove();
             
        $('div#searchrule').find('div.searchrule').each(function(i, row) {
            $(row).find('span.rule').text(i + 1);
            if (i == 0)
                $(row).find('div.operator').hide();
        });
    }       
}

ccmFormidableAddSearchElement = function(search_rule, rule) {    
    var query = '';
    var objDep = $('div#search_rule_'+search_rule+' div.searchelements');
    if (rule === undefined) rule = parseInt(objDep.attr('data-next_rule'));
    $.ajax({ 
        type: "POST",
        url: '<?php  echo $tools_dir; ?>/tools',
        data: 'action=add_search_element&search_rule='+search_rule+'&rule='+rule+'&formID='+formID+'&bID='+bID,
        dataType: 'html',
        beforeSend: function () {
            //console.log('loading');
        },
        success: function(ret) {
            objDep.append(ret).attr('data-next_rule', rule+1);
            ccmFormidableInitSearchElement(search_rule, rule);
        }
    });     
}

ccmFormidableDeleteSearchElement = function(search_rule, rule) {
    if ($('div#search_rule_'+search_rule+' div.searchelements').children().length == 1)
        return false;
    
    var objRule = $('div#search_rule_'+search_rule+' div#element_'+rule);   
    objRule.remove();   
    
    if ($('div#search_rule_'+search_rule+' div.searchelements').children().length == 1)
        $('div#search_rule_'+search_rule+' div.searchelements a.error').attr('disabled', true);
        
    objNext = $('div#search_rule_'+search_rule+' div.searchelements').children(':first');
    objNext.find('span.element_label').hide();
    objNext.find('select.element').width(433);  
}   

ccmFormidableInitSearchElement = function(search_rule, rule) {
       
    console.log(search_rule, rule);    
    var objRule = $('div#search_rule_'+search_rule+' div#element_'+rule);
        
    objRule.find('div.element_value, div.condition, span.element_label').hide();    
    
    var element_select = objRule.find('select.element');
    var element_value_select = objRule.find('select.element_value');
    var condition_select = objRule.find('select.condition');
    var condition_value = objRule.find('input.condition_value');
    
    if (objRule.parents('div.searchelements').children().length > 1 && rule > 0) {
        objRule.find('span.element_label').show();
        element_select.width(408);
    }
        
    var element_select = objRule.find('select.element');
    var element_value_select = objRule.find('select.element_value');
    var condition_select = objRule.find('select.condition');
    var condition_value = objRule.find('input.condition_value');
    
    if (element_select.val() != '') {
        objRule.find('div.element_value, div.condition').hide();
        if (element_value_select.find('option').length > 0)
            objRule.find('div.element_value').show();
        else {
            element_value_select.append($('<option>').val('').text('').attr('selected', 'selected'));
            objRule.find('div.condition').show();
        }
    }
    
    element_select.on('change', function() {
        objRule.find('div.element_value, div.condition').hide();
        if (element_select.val() != '') {
            $.ajax({ 
                type: "POST",
                url: '<?php  echo $tools_dir; ?>/tools',
                data: 'action=search_load_element&elementID='+element_select.val()+'&formID='+formID+'&bID='+bID,
                dataType: 'json',
                beforeSend: function () {
                    //console.log('loading');
                },
                success: function(ret) {
                    element_value_select.find('option').remove();
                    condition_select.find('option:gt(1)').remove(); 
                    if (ret.length > 1) {
                        for( var i=0; i<search_values.length; i++) {
                            element_value_select.append($('<option>').val(search_values[i][0]).text(search_values[i][1]));
                        }
                        for( var i=0; i<ret.length; i++) {
                            element_value_select.append($('<option>').val(ret[i]['value']).text(ret[i]['name']));
                        }
                        objRule.find('div.element_value').show();
                    } else { 
                        //element_value_select.append($('<option>').val('').text('').attr('selected', 'selected')); 
                        for( var i=0; i<condition_values.length; i++) {
                            condition_select.append($('<option>').val(condition_values[i][0]).text(condition_values[i][1]));
                        }                       
                        objRule.find('div.condition').show();
                    }
                }
            });         
        }
        
    });
    
    condition_value.hide(); 
    if (condition_select.val() != 'empty' && condition_select.val() != 'not_empty')
        condition_value.show();
    
    condition_select.on('change', function() {
        condition_value.hide();
        if (condition_select.val() != 'empty' && condition_select.val() != 'not_empty')
            condition_value.show().val('').attr('placeholder', search_condition_placeholder) 
        
        if (condition_select.val() == 'regex') 
            condition_value.show().val('').attr('placeholder', regex_condition_placeholder)    
    });
    

    $('div#search_rule_'+search_rule+' div.searchelements a.error').attr('disabled', true);
    if ($('div#search_rule_'+search_rule+' div.searchelements').children().length > 1)
        $('div#search_rule_'+search_rule+' div.searchelements a.error').attr('disabled', false);
}

$(function() {
    $('#ccm-tab-content-form #formID').on('change', function() {
        ccmFormidableLoadForm($(this).val());
    });
    ccmFormidableLoadForm($('#ccm-tab-content-form #formID').val());  

    $("input[name=limit]").click(function() {
        ccmFormidableSearchCheck($(this));
    });
    ccmFormidableSearchCheck();

});
</script>

<div class="ccm-ui">

    
    
    <?php   if (sizeof($forms) > 0) { 

        echo '<input type="hidden" id="FormidableResultsbID" value="'.$bID.'">';

        $tabs = array(
            array('form', t('Form'), true),
            array('query', t('Query / Filter')),
            array('columns', t('Columns')),
            array('view', t('View')),
            //array('preview', t('Preview')),
        );
        echo $concrete_interface->tabs($tabs);
    ?>    
        <div id="ccm-tab-content-form" class="ccm-tab-content">

            <fieldset>
                <legend><?php  echo t('Select a Formidable Form')?></legend>
                <div class="clearfix">
                    <?php  echo $form->label('formID', t('Form Name').' <span class="ccm-required">*</span>')?>
                    <div class="input">
                    	<?php  echo $form->select('formID', $forms, $data['formID']);?>
                    </div>
                </div>            	
            </fieldset>

        </div>

        <div id="ccm-tab-content-query" class="ccm-tab-content"> 
        </div>

        <div id="ccm-tab-content-columns" class="ccm-tab-content">
        </div>

        <div id="ccm-tab-content-view" class="ccm-tab-content">

            <fieldset>
                <legend><?php  echo t('Results and Pagination')?></legend>
                <div class="clearfix">
                    <?php  echo $form->label('limit', t('Limit items').' <span class="ccm-required">*</span>')?>
                    <div class="input">
                        <div class="input-prepend">
                            <label class="add-on"><?php  echo $form->checkbox('limit', 1, intval($data['limit']) != 0)?></label>
                            <?php   echo $form->text('limit_value', intval($data['limit_value']))?>
                        </div>
                    </div>

                    <div class="input">
                    </div>
                </div> 
                <div class="clearfix">
                    <?php  echo $form->label('pagination', t('Pagination'))?>
                    <div class="input">
                        <label class="checkbox">
                            <?php  echo $form->checkbox('pagination', 1, $data['pagination']);?>
                            <?php  echo t('Enable pagination'); ?>
                        </label>
                    </div>
                </div>
                <legend><?php  echo t('Sortable')?></legend>
                <div class="clearfix">
                    <?php  echo $form->label('sortable', t('Sortable'))?>
                    <div class="input">
                        <label class="checkbox">
                            <?php  echo $form->checkbox('sortable', 1, $data['sortable']);?>
                            <?php  echo t('Enable sortable columns, if columns allow it'); ?>
                        </label>
                    </div>
                </div>                             
            </fieldset>

        </div>

        <div id="ccm-tab-content-preview" class="ccm-tab-content">
        </div>
        
    <?php   } else { ?>
    
        <strong><?php   echo t('There are no Formidable forms!') ?></strong>
        <div class="ccm-note"><?php   echo t('Go to dashboard and create a Formidable Form') ?></div>
        <div><a href="<?php   echo View::url('/dashboard/formidable/forms/'); ?>" class="btn success"><?php   echo t('Create a new FormidableForm') ?></a></div>
    
    <?php   } ?>
</div>