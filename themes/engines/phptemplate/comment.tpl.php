<div class="comment<?php print ($comment->new) ? ' comment-new' : ''; print ($comment->status == COMMENT_NOT_PUBLISHED) ? ' comment-unpublished' : ''; ?> clear-block">
  <?php if ($comment->new) : ?>
  <a id="new"></a>
  <span class="new"><?php print $new ?></span>
  <?php endif; ?>

  <div class="title"><?php print $title ?></div>
  <?php print $picture ?>
  <div class="author"><?php print $submitted ?></div>
  <div class="content"><?php print $content ?></div>
  <div class="links"><?php print $links ?></div>
</div>
