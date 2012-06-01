var weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function get_available_accommodation() {
	var selectedDate = $('#start_date').val(),
		listContainer = $('#offers-accommodation-list');

	// Get all accommodation available between start_date and end of duration
	$.ajax({
		url: '/admin/offers/get_available_accommodation',
		dataType: 'json',
		data: {'duration': 3, 'start_date': selectedDate},
		type: 'post',
		success: function (data) {
			// Add accommodation to list
			listContainer.html(data);
			
			listContainer.find('input').click(function () {
				$('#discount_price').val($(this).parent().data('price'));
				$('#total_price').val($(this).parent().data('price'));
				$('#live-discount-price').text($(this).parent().data('price'));
			});
		}
	});
}

$(function () {
	// Admin Forms
	if ($('form.admin-form').length > 0) {
		$('a.remove-photo').click(function () {
			var $link = $(this);
			
			$link.hide();
			$link.prev().remove();
			$link.next().remove();
			$link.next().removeClass('hidden');
			
			return false;
		});
	}
	
	// Delete
	$('a.delete').click(function () {
		var deleteRow = window.confirm("Are you sure you want to delete this?"),
			area = $(this).parents('table').attr('id'),
			id = $(this).parent().parent().data('id');
		
		if (deleteRow) {
			$(this).parent().parent().fadeOut(300);
			
			$.ajax({
				type: "POST",
				url: "/admin/" + area + "/delete",
				data: "id=" + id,
				success: function (data) {
					if (data) {
						alert(data);
					}
				}
			});
		}
		return false;
	});
	
	// Change Site
	$('a.change-site').click(function () {
		var id = $(this).data("id"),
			view = location.pathname;
		
		$.ajax({
			type: "POST",
			url: "/admin/sites/change_site",
			data: {id: id, "view": view},
			success: function (data) {
				window.location.reload();
			}
		});
		
		return false;
	});
	
	// jQuery UI Datepicker
	$("form:not(#pricing-form)").find(".date-input").datepicker({ 
		dateFormat: 'dd-mm-yy 08:00'
	});
	
	// jQuery UI Datepicker
	$("#pricing-form").find('input.date-input').datepicker({ 
		dateFormat: 'dd-mm-yy',
		minDate: 0
	});
	
	// Pricing Functions
	if ($('#pricing-form').length > 0) {
		// Add Price Range
		$('a#pricing-add').click(function () {
			$('tr.pricing-form-container').show();
			return false;
		});
	
		// Check start date for duplicate
		$('input#start_date').change(function () {
			var date = $(this).val();
			
			$.ajax({
				type: 'POST',
				url: '/admin/pricing/start_date_check',
				data: {'start_date': date},
				success: function (data) {
					if (data == 'true') {
						$('ul.errors').html('<li class="start_date_error">That start date already exists!</li>');
					} else {
						$('ul.errors').find('li.start_date_error').hide();
					}
				}
			});
		});
	}
	
	// Public Holidays Functions
	if ($('#holidays-form').length > 0) {
		// Add Price Range
		$('a#holiday-add').click(function () {
			$('tr.holidays-form-container').show();
			return false;
		});
	}
	
	// Voucher Codes Functions
	if ($('#vouchers-form').length > 0) {
		// Add Price Range
		$('a#voucher-add').click(function () {
			$('tr.vouchers-form-container').show();
			return false;
		});
	}
	
	// Voucher Codes Functions
	if ($('#site-closed-form').length > 0) {
		// Add Price Range
		$('a#site-closed-add').click(function () {
			$('tr.site-closed-form-container').show();
			return false;
		});
	}
	
	// Export reports
	if ($('a.export-xls').length > 0) {
		// Add Price Range
		$('a.export-xls').click(function () {
			var $this = $(this),
				title = $this.data('title'),
				model_function = $this.data('model_function'),
				secondary = $this.data('secondary');
				
			$.ajax({
				url: '/admin/reports/export_xls',
				data: {'report_title': title, 'model_function': model_function, 'secondary_attribute': secondary},
				dataType: 'json',
				type: 'POST',
				success: function (data) {
					$('#result').html("<a href='/reports/" + data + "'>Download Report</a>");
				}
			});
			
			return false;
		});
	}
	
	// Report Quicksearching
	if ($('#quicksearch').length > 0) {
		$('#quicksearch').quicksearch('table tbody tr');
	}
	
	// Live Discount calculation
	if ($('#percentage_discount').length > 0) {
		$('#percentage_discount').on('keyup', function () {
			var discountPrice = Math.round(parseFloat(($('#total_price').val() / 100) * parseInt(100 - $(this).val(), 10)) / 5) * 5;
			
			$('#live-discount-price').text(discountPrice);
			$('#discount_price').val(discountPrice);
		});
	}
	
	// Cancel Booking
	if ($('a.cancel-booking').length > 0) {
		$('a.cancel-booking').on('click', function () {
			var confirm = window.confirm('Are you sure you want to delete this booking - ' + $(this).data('ref'));
			
			if (confirm) {
				$.ajax({
					url: '/admin/bookings/cancel_booking',
					data: {'id': $(this).data('id')},
					type: 'POST',
					success: function (data) {
						alert(data);
						// Fadeout row
						$(this).closest('tr').fadeOut(500);
					}
				});
			}
	
			return false;
		});
	}
	
	if ($('#calendar').length > 0) {
		// Get all closed dates
		var closedDates = [],
			pHolidays = [],
			siteDates = [];

		$.ajax({
			url: '/booking/get_unavailable_dates',
			dataType: 'json',
			success: function (data) {
				if (data.response == true) {
					$.each(data.dates, function (key, val) {
						var date = val.split("-"),
							dateArray = [date[0], date[1], date[2]];

						closedDates.push(dateArray);
					});
				}
			}
		});

		// Process all public holiday dates
		// Get Public Holiday dates
		$.ajax({
			url: '/accommodation/get_public_holidays',
			dataType: 'json',
			type: 'post',
			success: function (data) {
				$.each(data, function (key, val) {
					var pHoliday = val['start_date'].split("-"),
						pHolidayArray = [pHoliday[0], pHoliday[1], pHoliday[2]];

					pHolidays.push(pHolidayArray);
				});
				
				if ($('#offers').length > 0) {
					$('#calendar').datepicker({ 
						firstDay: 1,
						//minDate: 0,
						minDate: "04/01/2012",
						beforeShowDay: function (date) {
							var day = date.getDay(),
								month = date.getMonth(),
								year = date.getFullYear(),
								thisDate = date.getDate(),
								i;
			
							// Process closed dates
							for (i = 0; i < closedDates.length; i++) {
								if (month == closedDates[i][1] - 1 && thisDate == closedDates[i][0] && year == closedDates[i][2]) {
									return [false, 'unavailable'];
								}
							}
			
							// Proces Public holidays
							for (i = 0; i < pHolidays.length; i++) {
								var holDate = pHolidays[i][0],
									holMonth = pHolidays[i][1] - 1,
									holYear = pHolidays[i][2],
									nextDay = parseInt(pHolidays[i][0], 10) + 1,
									nextDate = new Date(holYear, holMonth, nextDay);
												
								if (month == holMonth && thisDate == holDate && year == holYear) {
									if (weekdays[day - 1] == 'Friday') {
										return [true, 'book-in-date available'];
									} else {
										return false;
									}
								} else if (month == nextDate.getMonth() && thisDate == nextDate.getDate() && year == nextDate.getFullYear()) {
									if (weekdays[day - 1] === 'Tuesday') {
										return false;
									}
								}
							}	
									
							if (weekdays[day - 1] === 'Friday') {
								return [true, 'book-in-date available'];
							} else {
								return [false, 'available'];
							}
						},
						onSelect: function (dateText, inst) {
							var selectedDate = inst.selectedDay + "-" + (inst.selectedMonth + 1) + "-" + inst.selectedYear,
								d = new Date(dateText),
								nextMonday = d.setDate(d.getDate() + 3),
								mon = new Date(nextMonday);
								monDay = mon.getDate(),
								monMonth = mon.getMonth(),
								monYear = mon.getFullYear();
		
							// Put selected date into form field
							$('input#start_date').val(selectedDate);
							$('input#end_date').val(monDay + "-" + (monMonth + 1) + "-" + monYear);
							
							get_available_accommodation();
						}
					});
				}
			}
		});
		
		if ($('#weddings').length > 0) {
			// Process available weekends (i.e. all accommodation available)
			$.ajax({
				url: '/accommodation/get_full_site_availability',
				dataType: 'json',
				type: 'post',
				success: function (data) {
					$.each(data, function (key, val) {
						var siteDate = val.split("-"),
							siteDatesArray = [siteDate[0], siteDate[1], siteDate[2]];
	
						siteDates.push(siteDatesArray);
					});
					
					$('#calendar').datepicker({ 
						firstDay: 1,
						//minDate: 0,
						minDate: "04/01/2012",
						beforeShowDay: function (date) {
							var day = date.getDay(),
								month = date.getMonth(),
								year = date.getFullYear(),
								thisDate = date.getDate(),
								i;
			
							if (weekdays[day - 1] === 'Friday' || weekdays[day - 1] === 'Monday') {
								// Process closed dates
								for (i = 0; i < closedDates.length; i++) {
									if (month == closedDates[i][1] - 1 && thisDate == closedDates[i][0] && year == closedDates[i][2]) {
										return [false, 'unavailable'];
									}
								}
				
								// Process full site accommodation unavailable dates
								for (i = 0; i < siteDates.length; i++) {
									if (month == siteDates[i][1] - 1 && thisDate == siteDates[i][0] && year == siteDates[i][2]) {
										return [false, 'unavailable'];
									}
								}										
							
								return [true, 'book-in-date available'];
							} else {
								return [false, 'available'];
							}
						},
						onSelect: function (dateText, inst) {
							var selectedDate = inst.selectedDay + "-" + (inst.selectedMonth + 1) + "-" + inst.selectedYear;
			
							// Put selected date into form field
							$('input#start_date').val(selectedDate);
						}
					});
				}
			});
		}
	}

});