(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  jQuery(function() {
    return jQuery('input[name="check-all"]').change(__bind(function() {
      console.log(jQuery(this + ':checked').val());
      if (jQuery(this + ':checked').val() === 'on') {
        return alert('checked');
      }
    }, this));
  });
}).call(this);
