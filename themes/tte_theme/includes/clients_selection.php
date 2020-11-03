<?php
	$page = $c;
	$pageType = $page->getAttribute('page_type');

?>
	<section class="clients-selection" id="clients-selection">
		<div class="container">
			<div class="clients-selection-wrap">

				<div class="row">
					<div class="col-sm-6">
						<?php
							$a = new Area("Client Selections Title"); $a->display($c);
						?>
					</div><!-- /.col -->
					<div class="col-sm-6"></div><!-- /.col -->
				</div><!-- /.row -->
				<div class="clients-selection-slider">
					<ul class="row main-client-selection-slider five-slides" id="main-client-selection-slider">
						<?php if ($pageType == 'mep-solutions'){ ?>
							<?php $stack = Stack::getByName('MEP Clients Selection Logos'); $stack->display(); ?>
						<?php } else { ?>
							<?php $stack = Stack::getByName('Facilities Management Clients Selection Logos'); $stack->display(); ?>
						<?php } ?>
					</ul>
				</div><!-- /.clients-selection-slider -->

			</div><!-- /.clients-selection-wrap -->
	</div><!-- /.container -->
</section><!-- /.clients-selection -->
