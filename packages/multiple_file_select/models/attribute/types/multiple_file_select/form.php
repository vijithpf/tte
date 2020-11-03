<script>
function assignChooseMultiFileAttrFunc<?php echo $this->attributeKey->getAttributeKeyID() ?>(){

	ccm_chooseAsset = function (data){
		if(!parseInt(data.fID)) return false;
		var html = '<div class="ccm-file-selected-wrapper" id="ak<?php echo $this->attributeKey->getAttributeKeyID() ?>_fileAttachmentRow'+data.fID+'">';
        html = html+'<input name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][fID][]" type="checkbox" checked="checked" value="'+data.fID+'" />';
		html = html+'<div class="ccm-file-selected-thumbnail"><img src="'+data.thumbnailLevel1+'"></div>';
		html = html+'<div class="ccm-file-selected-data"><div><a class="fileAttachmentTitle" href="'+data.filePathDirect+'" target="_blank">'+data.title+'</a></div></div>';
		html = html+'<div class="ccm-spacer">&nbsp;</div>';
		html = html+'<div class="half"><label>Horizontal Align</label><select name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][align]['+data.fID+']"><option value="center">Center</option><option value="left">Left</option><option value="right">Right</option></select></div>';
        html = html+'<div class="half"><label>Vertical Align</label><select name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][vAlign]['+data.fID+']"><option value="center">Center</option><option value="top">Top</option><option value="bottom">Bottom</option></select></div>';
        html = html+'<div class="ccm-spacer">&nbsp;</div></div>';
		$('#ak<?php echo $this->attributeKey->getAttributeKeyID() ?>_attachedFilesList').append(html);
	}
}
</script>
<style>
	.ccm-spacer{ clear: both; }
	.half{ width: 48%; margin: 10px 1% 0; float: left; position: relative; }
	.half select{ width: 100%; }
</style>
<div class="ccm-file-manager-select">
	<a onclick="assignChooseMultiFileAttrFunc<?php echo $this->attributeKey->getAttributeKeyID() ?>();ccm_alLaunchSelectorFileManager(''); return false" href="javascript:;"><?php echo t('Choose Images') ?></a>
</div>
<div class="unselect-link">
	<a href="javascript:;">Remove/Unselect all</a>
</div>
<div id="ak<?php echo $this->attributeKey->getAttributeKeyID() ?>_attachedFilesList">
<?php
if (is_object($this->attributeValue)){
	$multiFilesValueObj = $this->attributeValue->getValue();
    foreach ($multiFilesValueObj as $image) {
        $file = $image['file'];
        $align = $image['align'];
        $vAlign = $image['vAlign'];
        if (!is_object($file) || !$file->getFileID()) continue;
		$fv = $file->getApprovedVersion();
		?>
		<div class="ccm-file-selected-wrapper" id="ak<?php echo $this->attributeKey->getAttributeKeyID() ?>_fileAttachmentRow<?php echo  $file->getFileID() ?>">
          	<input name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][fID][]" type="checkbox" checked="checked" value="<?php echo  $file->getFileID() ?>" />
			<div class="ccm-file-selected-thumbnail"><?php echo $fv->getThumbnail(1); ?></div>
			<div class="ccm-file-selected-data"><div><a class="fileAttachmentTitle" href="<?php echo  $fv->getRelativePath() ?>" target="_blank"><?php echo  $fv->getTitle() ?></a></div></div>
			<div class="ccm-spacer">&nbsp;</div>
			<div class="half">
				<label>Horizontal Align</label>
				 <select name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][align][<?php echo $file->getFileID(); ?>]">
		            <option <?php echo $align === 'center' ? 'selected' : ''; ?> value="center">Center</option>
		            <option <?php echo $align === 'left' ? 'selected' : ''; ?> value="left">Left</option>
		            <option <?php echo $align === 'right' ? 'selected' : ''; ?> value="right">Right</option>
		        </select>
		    </div>
		    <div class="half">
				<label>Vertical Align</label>
		        <select name="akID[<?php echo $this->attributeKey->getAttributeKeyID() ?>][vAlign][<?php echo $file->getFileID(); ?>]">
		            <option <?php echo $vAlign === 'center' ? 'selected' : ''; ?> value="center">Center</option>
		            <option <?php echo $vAlign === 'top' ? 'selected' : ''; ?> value="top">Top</option>
		            <option <?php echo $vAlign === 'bottom' ? 'selected' : ''; ?> value="bottom">Bottom</option>
		        </select>
		    </div>
		    <div class="ccm-spacer">&nbsp;</div>
		</div>
	<?php  }
} ?>
</div>
<script>$(document).ready(function(){$(".unselect-link a").click(function(){$(".ccm-file-selected-wrapper input").removeAttr('checked');});});</script>
<style>
	.unselect-link, .ccm-file-selected-wrapper {margin-top:8px;}
	.ccm-file-selected-wrapper input[type=checkbox] {float: left; margin-right:10px;}
</style>