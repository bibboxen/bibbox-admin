/**
 * @file
 * Add time picker to screen on/off fields.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mybehavior = {
    attach: function (context, settings) {
      $('#edit-field-screen-on-0-value').timepicker({ 'timeFormat': 'H:i:s' });
      $('#edit-field-screen-off-0-value').timepicker({ 'timeFormat': 'H:i:s' });
    }
  };

})(jQuery, Drupal, drupalSettings);
