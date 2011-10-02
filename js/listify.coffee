jQuery ->
  
  # chosen
  jQuery('.chosen').chosen()
  
  jQuery('input[name="check-all"]').change =>
    if jQuery(this + ':checked').val() is 'on'
      jQuery
      # do code that checks all the others