<?php // $Id: forums.tpl.php,v 1.2 2007/07/29 17:28:23 dries Exp $
/**
 * @file forums.tpl.php
 * Default theme implementation to display a forum, which may contain forum
 * containers as well as forum topics.
 *
 * Variables available:
 *
 * - $links: An array of links that allow a user to post new forum topics.
 *   It may also contain a string telling a user they must log in in order
 *   to post.
 * - $forums: The forums to display (as processed by forum-list.tpl.php)
 * - $topics: The topics to display (as processed by forum-topic-list.tpl.php)
 *
 * @see template_preprocess_forums()
 * @see theme_forums()
 *
 */
?>
<?php if ($forums_defined): ?>
<div id="forum">
  <?php print theme('links', $links); ?>
  <?php print $forums; ?>
  <?php print $topics; ?>
</div>
<?php endif; ?>
