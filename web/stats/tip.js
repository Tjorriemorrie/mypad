var tipTimeout;
$(function() {
	getTip();

	// set albums slots
	$('.statSlots').live('click', function() {
		$('#dialog').dialog({
			width		: 330,
			buttons		: {
				Submit	: function() {
					var entityId = $('#slots').attr('entityId');
					var inputValue = $('#slots').val();
					$.ajax({
						url			: '/setslots/' + entityId + '/' + inputValue,
						dataType	: 'html',
						cache		: false,
						beforeSend	: function() {
						},
						error		: function(jqXHR, textStatus, errorThrown) {
							$('#tip').html(textStatus + ': ' + errorThrown);
						},
						success		: function(data) {
							$('#tip').html(data);
						},
						complete	: function() {
							//$('#dialog').hide();
						}
					});
					$(this).dialog('destroy');
				}
			}
		});
	});

	// set album name
	$('.statAlbum').click(function() {
		$('#dialog').dialog( {
			width		: 350,
			buttons		: {
				Submit	: function() {
					var entityId = $('#slots').attr('entityId');
					var url = '/namealbum/' + entityId + '/' + $('#slots').val();
					$.get(url, function(data) {
						$('#tip').html(data);
					});
					$(this).dialog('destroy');
				}
			}
		});
	});
	
	// set artist wait
	$('.artistWait').live('click', function() {
		var artistId = $(this).attr('artist');
		$.get('/artist/wait/' + artistId, function(data) {
			$('#tip').html(data);
			tipTimeout = setTimeout(function() {
				getTip();
			}, (5 * 1000));
		});
	});
});


function getTip() {
	$.ajax({
		url			: '/tip',
		dataType	: 'html',
		error		: function(jqXHR, textStatus, errorThrown) {
			$('#tip').html(textStatus + ': ' + errorThrown);
		},
		success		: function(data) {
			$('#tip').html(data);
		},
		complete	: function() {
			$('#dialog').hide();
			tipTimeout = setTimeout(function() {
				getTip();
			}, (60 * 1000));
		}
	});
}