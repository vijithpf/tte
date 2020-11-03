<?php   
defined('C5_EXECUTE') or die("Access Denied.");

$cnt = Loader::controller('/dashboard/formidable/forms/elements');

$layouts = $cnt->get_layouts();

if(sizeof($layouts) > 0) 
{
	foreach($layouts as $rowID => $row) { ?>
		<div class="f-row" data-id="<?php  echo $rowID; ?>" id="row_<?php  echo $rowID; ?>">
			<div class="overlay"></div>
			<div class="inner">
				<div class="clearfix">
					<?php 
					$width = round(906/count($row)); $i=0;
					foreach($row as $layoutID => $layout) { ?>
						<div class="f-col <?php  echo ($i==(count($row)-1)?'f-col-last':''); ?>" data-id="<?php  echo $layoutID; ?>" style="width:<?php  echo $width; ?>px">
							<div class="inner">
								<div class="overlay"></div>
								<div class="element_row_wrapper element-empty <?php  echo count($layout->elements)?'hide':''; ?>"><em><?php  echo t('Empty column'); ?></em></div>
								<?php  
								if(count($layout->elements)) 
									foreach($layout->elements as $element) 
										echo Loader::packageElement('dashboard/element/list', 'formidable', array('element' => $element));
								?>
								<div class="tools show_menu col-tools" target="#coloptions_<?php  echo $layoutID ?>" itemID="col<?php  echo $layoutID ?>">
									<div class="tools-link"><a href="javascript:;" class="ccm-menu-icon ccm-icon-sets"><?php  echo t('Column'); ?></a></div>
								</div>
								<div id="coloptions_<?php  echo $layoutID ?>" style="display:none;">
									<a href="javascript:ccmFormidableOpenNewElementDialog(<?php  echo $layoutID ?>);" class="ccm-menu-icon ccm-icon-add-block-menu"><?php  echo t('Add element') ?></a>
									<a href="javascript:ccmFormidableOpenLayoutDialog(<?php  echo $layoutID ?>,<?php  echo intval($rowID) ?>);" class="ccm-menu-icon ccm-icon-edit-menu"><?php  echo t('Edit') ?></a>
									<a href="javascript:ccmFormidableMoveColumns(<?php  echo $rowID; ?>);" class="ccm-menu-icon ccm-icon-move-menu"><?php  echo t('Move') ?></a>
									<a href="javascript:ccmFormidableDeleteLayout(<?php  echo $layoutID ?>,<?php  echo intval($rowID) ?>);" class="ccm-menu-icon ccm-icon-delete-menu"><?php  echo t('Delete') ?></a>
								</div>
							</div>
						</div>
					<?php 
						$i++;
					}
					?>
				</div>
			</div>
			<div class="tools show_menu row-tools" target="#rowoptions_<?php  echo intval($rowID) ?>" itemID="row<?php  echo $rowID ?>">
				<div class="tools-link"><a href="javascript:;" class="ccm-menu-icon ccm-icon-sets"><?php  echo t('Row'); ?></a></div>
			</div>
			<div id="rowoptions_<?php  echo intval($rowID) ?>" style="display:none;">
				<a href="javascript:ccmFormidableOpenLayoutDialog(-1,<?php  echo intval($rowID) ?>);" class="ccm-menu-icon ccm-icon-edit-menu"><?php  echo t('Edit') ?></a>
				<a href="javascript:ccmFormidableMoveLayout();" class="ccm-menu-icon ccm-icon-move-menu"><?php  echo t('Move') ?></a>
				<a href="javascript:ccmFormidableDeleteLayout(-1,<?php  echo intval($rowID) ?>);" class="ccm-menu-icon ccm-icon-delete-menu"><?php  echo t('Delete') ?></a>
			</div>
		</div>
		<?php 
	}
	?>
	<div class="tools row-tools row-add">
		<a href="javascript:ccmFormidableOpenLayoutDialog(-1,-1);" class="ccm-menu-icon ccm-icon-add-block-menu"><?php  echo t('Add row') ?></a>
	</div>
	<?php 
}
