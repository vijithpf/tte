<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<section class="prequalification-documents">
  <div class="container">
    <div class="prequalification-documents-wrap">
      <div class="row">
        <div class="col-sm-8 left">
          <?php  if (!empty($field_1_wysiwyg_content)): ?>
            <?php  echo $field_1_wysiwyg_content; ?>
          <?php  endif; ?>
        </div><!-- /.col -->
        <div class="col-sm-4 right">
          <?php $a = new Area('Callback Form'); $a->display($c); ?>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.prequalification-documents-wrap -->
  </div><!-- /.container -->
</section><!-- /.prequalification-documents -->