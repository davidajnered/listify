(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  jQuery(function() {
    jQuery('input[name="check-all-lists"]').change(__bind(function() {
      if (jQuery(this + ':checked').val() === 'on') {
        jQuery('#list-list input[type=checkbox]').attr('checked', true);
      }
      if (jQuery(this + ':checked').val() === 'comments') {
        return jQuery('#list-list input[type=checkbox]').attr('checked', false);
      }
    }, this));
    return jQuery('input[name="check-all-blogs"]').change(__bind(function() {
      console.log(jQuery(this + ':checked').val());
      if (jQuery(this + ':checked').val() === '0') {
        jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', true);
      }
      if (jQuery(this + ':checked').val() === '1') {
        return jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', false);
      }
    }, this));
  });
}).call(this);
