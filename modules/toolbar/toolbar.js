// $Id: toolbar.js,v 1.8 2009/11/15 21:13:26 webchick Exp $
(function ($) {

/**
 * Implementation of Drupal.behaviors for admin.
 */
Drupal.behaviors.admin = {
  attach: function(context) {

    // Set the initial state of the toolbar.
    $('#toolbar', context).once('toolbar', Drupal.admin.toolbar.init);

    // Toggling toolbar drawer.
    $('#toolbar a.toggle', context).once('toolbar-toggle').click(function() {
      Drupal.admin.toolbar.toggle();
      return false;
    });
  }
};

/**
 * Initialize cautiously to avoid collisions with other modules.
 */
Drupal.admin = Drupal.admin || {};
Drupal.admin.toolbar = Drupal.admin.toolbar || {};

/**
 * Retrieve last saved cookie settings and set up the initial toolbar state.
 */
Drupal.admin.toolbar.init = function() {
  // Retrieve the collapsed status from a stored cookie.
  var collapsed = $.cookie('Drupal.admin.toolbar.collapsed');

  // Expand or collapse the toolbar based on the cookie value.
  if (collapsed == 1) {
    Drupal.admin.toolbar.collapse();
  }
  else {
    Drupal.admin.toolbar.expand();
  }
}

/**
 * Collapse the admin toolbar.
 */
Drupal.admin.toolbar.collapse = function() {
  var toggle_text = Drupal.t('Open the drawer');
  $('#toolbar div.toolbar-drawer').addClass('collapsed');
  $('#toolbar a.toggle')
    .removeClass('toggle-active')
    .attr('title',  toggle_text)
    .html(toggle_text);
  $('body').removeClass('toolbar-drawer');
  $.cookie(
    'Drupal.admin.toolbar.collapsed', 
    1, 
    {path: Drupal.settings.basePath}
  );
}

/**
 * Expand the admin toolbar.
 */
Drupal.admin.toolbar.expand = function() {
  var toggle_text = Drupal.t('Close the drawer');
  $('#toolbar div.toolbar-drawer').removeClass('collapsed');
  $('#toolbar a.toggle')
    .addClass('toggle-active')
    .attr('title',  toggle_text)
    .html(toggle_text);
  $('body').addClass('toolbar-drawer');
  $.cookie(
    'Drupal.admin.toolbar.collapsed', 
    0, 
    {path: Drupal.settings.basePath}
  );
}

/**
 * Toggle the admin toolbar.
 */
Drupal.admin.toolbar.toggle = function() {
  if ($('#toolbar div.toolbar-drawer').hasClass('collapsed')) {
    Drupal.admin.toolbar.expand();
  }
  else {
    Drupal.admin.toolbar.collapse();
  }
}

Drupal.admin.toolbar.height = function() {
  return $("#toolbar").height();
}

})(jQuery);
