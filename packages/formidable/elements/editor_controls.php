<?php    defined('C5_EXECUTE') or die("Access Denied."); ?> 
<div class="ccm-editor-controls-left-cap" <?php    if (isset($editor_width)) { ?>style="width: <?php   echo $editor_width?>px"<?php    } ?>>
<div class="ccm-editor-controls-right-cap">
<div class="ccm-editor-controls">
<ul>
<li ccm-file-manager-field="rich-text-editor-image"><a class="ccm-file-manager-launch" onclick="ccm_editorSetupImagePicker(); return false" href="#"><?php   echo t('Add Image')?></a></li>
<li><a class="ccm-file-manager-launch" onclick="ccm_editorSetupFilePicker(); return false;" href="#"><?php   echo t('Add File')?></a></li>
<li><a href="#" onclick="ccm_editorSitemapOverlay();"><?php   echo t('Insert Link to Page')?></a></li>
<?php  if ($form_id) { ?>
<li><a href="#" onclick="ccm_editorFormidableOverlay(<?php   echo $form_id ?>);"><?php   echo t('Insert Formidable Element')?></a></li>
<?php  } elseif ($template) { ?>
<li><a href="#" onclick="ccm_editorFormidableMailtag();"><?php   echo t('Insert Mailing-tag')?></a></li>
<?php  } ?>
</ul>
</div>
</div>
</div>
<div id="rich-text-editor-image-fm-display">
<input type="hidden" name="fType" class="ccm-file-manager-filter" value="<?php   echo FileType::T_IMAGE?>" />
</div>

<div class="ccm-spacer">&nbsp;</div>
<script type="text/javascript">
$(function() {
	ccm_activateFileSelectors();
});
</script>
