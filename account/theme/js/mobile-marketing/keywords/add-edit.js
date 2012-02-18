head.js( 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js', '/js2/?f=charCount', function() {
    $('#taResponse').charCount({
        css : 'counter bold'
        , cssExceeded : 'error'
        , counterText : 'Characters Left: '
    });

    // Date Picker
	$('#tDateStarted').datepicker({
		minDate: 0,
		dateFormat: 'mm/dd/yy'
	});

    // Check availibity
    $('#aCheckKeywordAvailability').click( function() {
        $.post( '/ajax/mobile-marketing/keywords/check-availability/', { _nonce : $('#_ajax_check_availability').val(), 'k' : $('#tKeyword').val() }, ajaxResponse, 'json' );
    })
});