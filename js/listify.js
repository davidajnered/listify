(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  jQuery(function() {
    jQuery('.chosen').chosen();
    return jQuery('input[name="check-all"]').change(__bind(function() {
      if (jQuery(this + ':checked').val() === 'on') {
        return jQuery;
      }
    }, this));
  });
}).call(this);
