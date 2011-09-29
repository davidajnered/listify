jQuery ->
  jQuery('input[name="check-all"]').change =>
    if jQuery(this + ':checked').val() is 'on'
      # do code that checks all the others