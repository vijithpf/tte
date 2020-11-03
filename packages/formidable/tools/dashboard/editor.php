<?php    
	
	defined('C5_EXECUTE') or die(_("Access Denied.")); 
	
	if ($_GET['object'] != '')
		$object = $_GET['object'];
	
	if ($_GET['content'] != '')
		$content = urldecode($_GET['content']);
		
	$form = Loader::helper('form');
    Loader::element('editor_init');
    Loader::element('editor_config');
   	
	if ($_GET['type'] == 'formidable')
		Loader::packageElement('editor_controls', 'formidable', array('mode'=>'full'));
	else	
    	Loader::element('editor_controls', array('mode'=>'full'));

?>
	<style> form {margin-bottom: 0px !important; } </style>
    <form id="editorForm" action="javascript:ccmFormidableLoadContent('<?php   echo $object ?>');void(1);" name="editForm">
    <div class="ccm-ui">
    <?php   echo $form->textarea('content', $content, array('style' => 'width: 100%; height: 454px', 'class' => 'ccm-advanced-editor')) ?>
    <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
    <a href="javascript:void(0)" class="btn" onclick="ccm_blockWindowClose()"><?php   echo t('Cancel')?></a>
    <a style="float: right" href="javascript:;" onclick="$('#editorForm').trigger('submit');" class="btn primary"><?php   echo t('Save content') ?></a>
    <a style="float: right; margin-right: 10px;" href="javascript:;" onclick="ccmFormidableClearContent('<?php   echo $object ?>');" class="btn error"><?php   echo t('Clear content') ?></a>
    </div>
    </div>
    </form>