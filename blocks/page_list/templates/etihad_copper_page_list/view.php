<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Loader::helper('date');
$i = 1;
$ih = Loader::helper('image');
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)
?>

<?php $count = 0; ?>
		<?php foreach ($pages as $page):
        // Prepare data for each page being listed...
				$title = $th->entities($page->getCollectionName());
        $url = $nh->getLinkToCollection($page);
        $target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
        $target = empty($target) ? '_self' : $target;
        $description = $page->getCollectionDescription();
        $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
        $description = $th->entities($description);
				$description = ($description ? $description : 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci. Aenean dignissim pellentesque felis.');
        $date = $dh->date('d F', strtotime($page->getCollectionDatePublic()));
        $year = $dh->date('Y', strtotime($page->getCollectionDatePublic()));

				$thumb = $page->getAttribute('thumbnail_image');
				$thumbImage = $ih->getThumbnail($thumb, 1000, 1000, false);

				$buttonText = $page->getAttribute('button_text');
				$buttonText = ($buttonText ? $buttonText : 'Download product list');

        $count++;

				$subPageList = new PageList();
				$subPageList->filterByAttribute('is_featured', 1);
				$subPageList->filterByParentID($page->getCollectionID());
				$subPages = $subPageList->get(2);
				$subpageInfo = [];
				if($subPages) {
				foreach ($subPages as $subPage) {

						$titleSub = $th->entities($subPage->getCollectionName());
						$discSub = $description = $th->entities($subPage->getCollectionDescription());
						$subThumbImage = $subPage->getAttribute('thumbnail_image');
						$subThumbImage = $ih->getThumbnail($subThumbImage, 1000, 1000, false);

						$subpageInfoTemp['title'] = $titleSub;
						$subpageInfoTemp['desc'] = $discSub;
						$subpageInfoTemp['image'] = $subThumbImage->src;

						$subpageInfo[] = $subpageInfoTemp;

					}
				}
    	?>

    <?php if ($count == 1){ ?>
      <section class="section section_image_content_block left">
      	<div class="container">
      		<div class="block block_left">
      			<div class="block_image full_bg mainImage" style="background-image: url('<?php echo $thumbImage->src; ?>')">
      				<div class="the_block">
								<div class="block_space"></div>
    							<div class="block_content">
    								<div class="blurred_image_wrap blurWrap right">
    									<div class="blurred_image blurImage" style="background-image: url('<?php echo $thumbImage->src; ?>');"></div>
    								</div>
    								<div class="block_details">
    									<h2><?php echo $title; ?></h2>
                      <p><?php echo $description; ?></p>
											<a href="<?php echo $url; ?>" class="btn btn-md btn-white" title="<?php echo $title; ?>"><?php echo $buttonText; ?></a>
    							  </div>

      						</div><!-- /.block_content -->

      					</div><!-- /.the_block -->
      				</div><!-- /.block_image -->
      			</div><!-- /.block -->
      		</div><!-- /.container -->
      	</section>
      <?php } elseif($count == 2){ ?>
        <section class="section grey_block_section top">
          <div class="container">
                <div class="grey_block_row row">
									<div class="col-sm-6">
										<div class="grey_block_image full_bg" style="background-image: url('<?php echo $thumbImage->src; ?>')"></div>
									</div>
									<div class="col-sm-6">
                    <div class="grey_block_content">
                      <h2><?php echo $title; ?></h2>
											<p><?php echo $description; ?></p>
                    </div>
                  </div>

                </div>
            </div>
        </section>
      <?php } elseif($count == 3){ ?>
        <section class="section section_image_content_block right">
					<div class="container">
						<div class="block block_right">
							<div class="block_image full_bg mainImage" style="background-image: url('<?php echo $thumbImage->src; ?>')">
								<div class="the_block">
										<div class="block_space">

										</div>

											<div class="block_content">
												<div class="blurred_image_wrap blurWrap left">
													<div class="blurred_image blurImage" style="background-image: url('<?php echo $thumbImage->src; ?>');">

													</div>
												</div>
												<div class="block_details">
													<h2><?php echo $title; ?></h2>
													<p><?php echo $description; ?></p>
													<a href="<?php echo $url; ?>" class="btn btn-md btn-white" title="<?php echo $title; ?>"><?php echo $buttonText; ?></a>
											</div>

										</div><!-- /.block_content -->

									</div><!-- /.the_block -->
								</div><!-- /.block_image -->
							</div><!-- /.block -->
						</div><!-- /.container -->
					</section>
      <?php } elseif($count == 4){ ?>
        <section class="section grey_block_section bottom">
          <div class="container">
            <div class="grey_block_row row">
							<div class="col-sm-6">
                <div class="grey_block_image full_bg" style="background-image: url('<?php echo $thumbImage->src; ?>')"></div><!-- /.grey_block_image -->
              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="grey_block_content">
                  <h2><?php echo $title; ?></h2>
									<p><?php echo $description; ?></p>
									<a href="<?php echo $url; ?>" class="btn btn-md btn-black" title="<?php echo $title; ?>"><?php echo $buttonText; ?></a>
                </div><!-- /.grey_block_content -->
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container -->
        </section><!-- /.section -->
        <?php $count = 1; } ?>
<?php endforeach; ?>
