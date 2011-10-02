jQuery ->

  # varför on, comments, 0 och 1?

  jQuery('input[name="check-all-lists"]').change =>
    if jQuery(this + ':checked').val() is 'on'
      jQuery('#list-list input[type=checkbox]').attr('checked', true)
    if jQuery(this + ':checked').val() is 'comments'
      jQuery('#list-list input[type=checkbox]').attr('checked', false)
  
  jQuery('input[name="check-all-blogs"]').change =>
    console.log jQuery(this + ':checked').val()
    if jQuery(this + ':checked').val() is '0'
      jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', true)
    if jQuery(this + ':checked').val() is '1'
      jQuery('.blogs-wrapper input[type=checkbox]').attr('checked', false)