jQuery ->

  jQuery('input[name="check-all-lists"]').change =>
    if jQuery(this + ':checked').val() is '0'
      jQuery('#list-list input[type=checkbox]').attr('checked', true)
    if jQuery(this + ':checked').val() != '0'
      jQuery('#list-list input[type=checkbox]').attr('checked', false)
  
  jQuery('input[name="check-all-blogs"]').change =>
    if jQuery(this + ':checked').val() is '0'
      jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', true)
    if jQuery(this + ':checked').val() != '0'
      jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', false)