<?php   

	defined('C5_EXECUTE') or die("Access Denied.");
	
	$concrete_interface = Loader::helper('concrete/interface');
	$lh = Loader::helper('link', 'formidable');
	
	$cnt = Loader::controller('/dashboard/formidable/results');		
	$f = $cnt->getResult();	

	$pkg = Package::getByHandle('formidable');
		
    if (sizeof($f->results) <= 0) { 
?>
	<div class="ccm-ui element-body">
     <div class="message alert-message error dialog_message">
      <?php  echo t('There are no results found, the answers are empty or not set') ?>
     </div>
    </div>
    <div class="dialog-buttons">
     <a href="javascript:void(0)" class="btn" onclick="ccm_blockWindowClose()"><?php   echo t('Cancel')?></a>
    </div>  
<?php   
	die(); }
?>
	<style>
		.ccm-ui form {margin:0px;}
		.password_strength{-moz-border-radius: 3px;-webkit-border-radius: 3px;border-radius: 3px;position:relative;float:left;width:27%;height:26px;margin:2px 0 2px 10px;}
		.password_strength span{position:absolute;left:5px;line-height:26px;font-size:11px;color:gray; font-weight:normal;}
		.ui-progressbar { height:26px; text-align: left; overflow: hidden; }
		.ui-progressbar .ui-progressbar-value {margin: -1px; height:100%; }
		.rating{padding-top:6px;}
		.input {margin-left:0px!important;}
		.option_other{clear:both;display:none;}
		div.rating-cancel,div.star-rating{float:left;width:17px;height:15px;text-indent:-999em;cursor:pointer;display:block;background:transparent;overflow:hidden;}
		div.rating-cancel,div.rating-cancel a{background:url(<?php  echo $pkg->getRelativePath()?>/images/delete.gif) no-repeat 0 -16px;}
		div.star-rating,div.star-rating a{background:url(<?php  echo $pkg->getRelativePath()?>/images/star.gif) no-repeat 0 0;}
		div.rating-cancel a,div.star-rating a{display:block;width:16px;height:100%;background-position:0 0;border:0;}
		div.star-rating-on a{background-position:0 -16px!important;}
		div.star-rating-hover a{background-position:0 -32px;}
		.ui-timepicker-div .ui-widget-header{position:relative;border:none;border-bottom:1px solid #B6B6B6;-moz-border-radius:0;-webkit-border-radius:0;border-radius:0;padding:.35em 0;}
		.ui-timepicker-div dl{text-align:left;width:100%;font-size:.9em;margin:0 0 .4em;padding:5px;}
		.ui-timepicker-div dl dt{margin-top:20px;height:25px;margin-bottom:-20px;}
		.ui-timepicker-div dl dd{margin:0 15px 0 65px;}
		.ui-timepicker-div td{font-size:90%;}
		.formidable div.slider {margin-top:10px!important; width:65%;display:inline-block;}
		.formidable span.slider {margin-top:2px;padding:4px;line-height:19px;width:25%;color:#999;float:left;margin-left:10px;}
		.ui-tpicker-grid-label{background:none;border:none;margin:0;padding:0;}
		.formidable .ui-slider-horizontal {height:9px!important;}
		.formidable .ui-slider-horizontal .ui-slider-handle{top:-4px!important;margin-left:-9px!important;}
		.formidable .ui-slider .ui-slider-handle, .ui-timepicker-div .ui-slider .ui-slider-handle{background:url(<?php  echo $pkg->getRelativePath()?>/images/slider_handles.png) 0 -17px no-repeat!important;width:17px!important;height:17px!important;z-index:1!important;}
		.formidable .ui-slider .ui-slider-handle.ui-state-active, .ui-timepicker-div .ui-slider .ui-slider-handle.ui-state-active{background-position:0 0!important;}
		.ui_tpicker_time_label{margin-top:5px!important;}
		.ui_tpicker_time{margin-top:-25px!important;}
		.ui-timepicker-div .ui-slider-horizontal .ui-slider-handle {top:-5px}
		select.day,select.month,select.year,select.hour,select.minute,select.second,select.ampm{width:auto;float:none;}
		input.datepicker,input.timeslider{width:235px;}
		div.tagsinput { border:1px solid #CCC; background: #FFF; padding:5px; width:300px; height:100px; overflow-y: auto;}
		div.tagsinput span.tag { border: 1px solid #a5d24a; -moz-border-radius:2px; -webkit-border-radius:2px; display: block; float: left; padding: 5px; text-decoration:none; background: #cde69c; color: #638421; margin-right: 5px; margin-bottom:5px;font-family: helvetica;  font-size:13px;}
		div.tagsinput span.tag span {display: inline-block;}
		div.tagsinput span.tag a { font-weight: bold; color: #82ad2b; text-decoration:none; font-size: 11px;  } 
		div.tagsinput input { width:80px; margin:0px; font-family: helvetica; font-size: 13px; border:1px solid transparent; padding:5px; background: transparent; color: #000; outline:0px;  margin-right:5px; margin-bottom:5px; }
		div.tagsinput div { display:block; float: left; } 
		.tags_clear { clear: both; width: 100%; height: 0px; }
		.not_valid {background: #FBD8DB !important; color: #90111A !important;}
		.ui-slider {width: 90%;float: right;}
		.ccm-attribute-editable-field-error{color:red;display: none;}

	</style>
	<div class="ccm-ui element-body" id="ccm-mailing-results">
    
    <?php 
	$tabs = array(
		array('elements', t('Submitted data'), true),
		array('details', t('Submission details'))
	);
	// Print tab element
	echo Loader::helper('concrete/interface')->tabs($tabs);
	?>
    
	<div id="ccm-tab-content-elements" class="ccm-tab-content">

    <div class="alert alert-warning">
       <strong><?php  echo t('Beta:'); ?></strong> <?php  echo t('Having problems? Please send a support ticket through Concrete5.'); ?>
    </div> 

    <fieldset>
    <table width="100%" class="entry result_entry ccm-results-list">
     <tr>
      <th class="result_dialog_label"><?php   echo t('Label'); ?></th>
      <th colspan="2"><?php   echo t('Value'); ?></th>
     </tr>
     <?php  
     	foreach ($f->elements as $element) { 
     		if ($element->is_layout) 
     			continue;
     		echo $cnt->getElementResult($element, $f->answerSetID);
     	} 
    ?>
    </table>
	</fieldset>    
    </div>
    <div id="ccm-tab-content-details" class="ccm-tab-content">    
	<fieldset>
    <table width="100%" class="entry ccm-results-list">
     <tr>
      <th class="result_dialog_label"><?php   echo t('Label'); ?></th>
      <th><?php   echo t('Value'); ?></th>
     </tr>
     <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Submitted on') ?></td>
      <td><?php   echo $f->results->submitted; ?></td>      
     </tr> 
     <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('From page') ?></td>
      <td>
      <?php  
		$p = Page::getById($f->results->collectionID);
		if (intval($p->getCollectionID()) != 0) { 
			echo '<a href="'.BASE_URL.DIR_REL.View::url($p->getCollectionPath()).'" target="_blank">'.$p->getCollectionName().'</a> ';			
			echo t('(Page ID: %s)', $p->getCollectionID());
		} else
			echo t('Unknown or deleted page');
	  ?>
      </td>      
     </tr>
     <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Submitted by') ?></td>
      <td>
      <?php   
	  	$u = User::getByUserID($f->results->userID);
		if ($u instanceof User) { 
			echo '<a href="'.BASE_URL.DIR_REL.View::url('/dashboard/users/search?uID='.$u->getUserID()).'" target="_blank">'.$u->getUserName().'</a> ';
			echo t('(User ID: %s)', $u->getUserID());
		} else {
			if (!empty($fr->userID))
				echo t('Unknown or deleted user');
			else
				echo t('Guest');
		}
	  ?>
      </td>      
     </tr>
     <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Answerset ID (unique)') ?></td>
      <td><?php  echo $f->results->answerSetID; ?></td>      
     </tr>  
     <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Submitters IP') ?></td>
      <td><?php  echo $f->results->ip; ?></td>      
     </tr> 	 
	 <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Used Browser') ?></td>
      <td><?php  echo (!empty($f->results->browser))?$f->results->browser:t('Unknown'); ?></td>      
     </tr> 
	 <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Platform') ?></td>
      <td><?php  echo (!empty($f->results->platform))?$f->results->platform:t('Unknown'); ?></td>      
     </tr> 
	 <tr class="ccm-list-record">
      <td class="result_dialog_label"><?php   echo t('Screen resolution') ?></td>
      <td><?php  echo (!empty($f->results->resolution))?$f->results->resolution.t('px'):t('Unknown'); ?></td>      
     </tr>   
    </table>
	</fieldset>
	</div>
    </div>
    
	<div class="dialog-buttons">
	  <?php   echo $concrete_interface->button_js(t('Cancel'), 'ccm_blockWindowClose()', 'left'); ?>
	</div> 
	
	<script>
		var confirm_clear = '<?php  echo t('Are you sure you want to clear this data?'); ?>';
	</script>
	<?php  echo Loader::helper('html')->javascript('plugins.js', 'formidable'); ?> 
	<?php  echo Loader::helper('html')->javascript('dashboard/result_dialog.js', 'formidable'); ?> 

	<?php 
		// Getting all javascript and bundle...			
		if (sizeof($f->elements) > 0)
			foreach ($f->elements as $element)				
				if (sizeof($element->javascript) > 0)
					foreach ($element->javascript as $_js)	
						$_javascript .= $_js.PHP_EOL;
			
		if (sizeof($f->elements) > 0)
			foreach ($f->elements as $element)				
				if (sizeof($element->jquery) > 0)
					foreach ($element->jquery as $_js)	
						$_jquery .= $_js.PHP_EOL;		
		
		if (!empty($_javascript) || !empty($_jquery)) {	
			echo "<script>".PHP_EOL;
			if (!empty($_javascript)) {
				echo $_javascript.PHP_EOL; 
			}
			if (!empty($_jquery)) {
				echo "$(function() {".PHP_EOL; 
				echo $_jquery.PHP_EOL; 
				echo "if (typeof ccmFormidableAddressStatesTextList !== 'undefined')";
				echo "ccmFormidableAddressStates = ccmFormidableAddressStatesTextList.split('|');";
				echo "});".PHP_EOL;
			}
			echo "</script>".PHP_EOL;
		}
	?> 
