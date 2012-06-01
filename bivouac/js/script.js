var weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

Date.prototype.age = function (at) {
	"use strict";
	
    var value = new Date(this.getTime()),
		age = at.getFullYear() - value.getFullYear();

    value = value.setFullYear(at.getFullYear());

    if (at < value) { age = age - 1; }

    return age;
};

function submit3DSecure() {
	"use strict";
	
	$('#payment_3d_secure_form').submit();
}

function remaining_accommodation_count() {
	"use strict";
	if ($('#accommodation li:visible').length > 0) {
		$('#no-units').hide();
	} else {
		$('#no-units').show();
	}	
}


function check_for_dogs() {
	"use strict";
	
	// See if #dogs is greater than 0.
	// If yes, filter down accommodation to show only this with data-dog = yes
	if ($('#dogs').val() > 0) {
		$('#accommodation').find('li').each(function () {
			if ($(this).data('dogs') === "yes") {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
	else
	{
		$('#accommodation').find('li').show();
	}
	
	remaining_accommodation_count();
}

function babyCheck() {
	"use strict";
	
	// If there are babies we need to hide the bunk barns and 
	// set the select to 0
	if ($('#babies').val() > 0) {
		$('#accommodation').find('li').each(function () {
			if ($(this).data('type') === "Bunk Barn") {
				$(this).hide();
				$(this).find('select').val(0);
			}
		});
		
	} else {
		$('#accommodation').find('li').each(function () {
			if ($(this).data('type') === "Bunk Barn") {
				$(this).show();
			}
		});
	}
}

function check_guests_against_chosen_accom() {
	"use strict";
	
	var adults = parseInt($('#adults').val(), 10),
		children = parseInt($('#children').val(), 10),
		guests = adults + children,
		total_beds = 0,
		select;
		
	// Get sleep capactiy for all checked accommodation
	$('#accommodation').find('input.accommodation-checkbox').each(function () {
		if ($(this).is(':checked')) {
			total_beds += parseInt($(this).closest('li').data('sleeps'), 10);
		}
	});
	
	// Get number of beds selected for all bunk barn dropdowns
	$('#accommodation').find('select').each(function () {
		select = $(this);
		total_beds += parseInt(select.val(), 10);
		
		// Set hidden accommodation_id value to accommodation id if val > 0
		if (parseInt(select.val(), 10) > 0) {
			select.closest('li').find('input.hidden_accommodation_id').val(select.closest('li').data('id'));
		} else {
			select.closest('li').find('input.hidden_accommodation_id').val("");
		}
	});
	
	// Compare total_beds against guests. If guests is higher show warning message
	if ($('#multiple-units').val() === "yes") {
		if (guests > total_beds) {
			$('p.guests-warning').show();
			$('input[type=submit]').hide();
		} else {
			$('p.guests-warning').hide();
			$('input[type=submit]').show();
		}
	}
}

function check_occupancy_levels() {
	"use strict";
	
	var adults = parseInt($('#adults').val(), 10),
		children = parseInt($('#children').val(), 10),
		guests = adults + children;
	
	if ($('#multiple-units').val() === "no") {	
		$('#accommodation').find('li').each(function () {
			if (parseInt($(this).data('sleeps'), 10) < guests) {
				$(this).find('a.quick-book-link').hide();
				$(this).find('a.need-multiple-units').show();
			} else {
				$(this).find('a.quick-book-link').show();
				$(this).find('a.need-multiple-units').hide();
			}
		});
	}
	
	// Update bunk barn quick book link text with guests number
	$('#accommodation').find("li[data-type='Bunk Barn']").each(function () {
		$(this).find('a.quick-book-link').text('Book ' + guests + ' beds in this bunk barn');
	});
}

function get_available_accommodation() {
	"use strict";
	
	var selectedDate = $('#start_date').val(),
		duration = $('#duration').val();

	// Get all accommodation available between start_date and end of duration
	$.ajax({
		url: '/bivouac/index.php/booking/get_available_accommodation',
		dataType: 'json',
		data: {'duration': duration, 'start_date': selectedDate},
		type: 'post',
		success: function (data) {
			// Add accommodation to list
			$('#accommodation').html(data);
			
			babyCheck();
			
			check_for_dogs();
			
			// If user clicks a quick book link
			$('a.quick-book-link').on('click', function (e) {		
				e.preventDefault();
					
				// Create a hidden accomodation id field with id
				var li = $(this).closest('li'),
					id = li.data('id'),
					price = parseFloat(li.data('price')),
					input = "<input type='hidden' name='accommodation_ids[]' value='" + id + "' />";
				
				// Check if this is a bunk barn! total guests if it is!
				if (li.data('type') === "Bunk Barn") {
					price = price * (parseInt($('#adults').val(), 10) + parseInt($('#children').val(), 10));
				}
				
				$('#total_price').val(price);
				$('#booking-form').append(input).submit();
			});
			
			// View full accommodation information lightbox
			$('a.lightbox-full-accommodation').click(function () {
				var id = $(this).closest('li').data('id');
				
				$('#accommodation-lightbox').load('https://booking.thebivouac.co.uk/accommodation/unit_lightbox/' + id + ' #unit-info', function () {
					
					$.fancybox('#accommodation-lightbox');
				});
				
				return false;
			});
			
			// When multiple accommodation units selected calculate if there are enough beds for guests
			// Also calculate running total booking price
			$('#accommodation input, #accommodation select').change(function () {
				// Set #total_price of all selected accommodation units
				var newPrice = 0;
				
				check_guests_against_chosen_accom();
				
				// Get sleep capactiy for all checked accommodation
				$('#accommodation').find('input.accommodation-checkbox').each(function () {
					if ($(this).is(':checked')) {
						newPrice += parseFloat($(this).closest('li').data('price'));
					}
				});
				
				// Get number of beds selected for all bunk barn dropdowns
				$('#accommodation').find('select').each(function () {
					newPrice += parseInt($(this).val(), 10) * parseFloat($(this).closest('li').data('price'));
				});
	
				$('#total_price').val(newPrice);
				$('span.total-price').text(newPrice);
			});
		
			
			// Determine to hide checkbox or quick book links
			if ($('#multiple-units').val() === "no") {
				$('div.accommodation-checkbox-container').hide();
				$('div.accommodation-beds-container').hide();
				$('a.quick-book-link').show();
			} else {
				$('div.accommodation-checkbox-container').show();
				$('div.accommodation-beds-container').show();
				$('a.quick-book-link').hide();
				$('a.need-multiple-units').hide();
				$('div.price-container').show();
			}
			
			check_occupancy_levels();
			
			// Change to book multiple accommodation
			$('a.need-multiple-units').on('click', function () {
				$('#multiple-units').val('yes').change();
				
				return false;
			});
		}
	});
}

function update_prices(quantity, nights, price) {
	"use strict";
	
	var extrasPrice = $('span.extras-price').eq(0),
		totalPrice = $('span.total-price').eq(0),
		itemPrice;

	if (nights) {
		itemPrice = Math.round(quantity * nights * price);
	} else {
		itemPrice = Math.round(quantity * price);
	}

	// Add itemPrice to current extra dna total prices
	$('span.extras-price').text(itemPrice + parseFloat(extrasPrice.text()));
	$('span.total-price').text(itemPrice + parseFloat(totalPrice.text()));

	// Add total_price to form
	$('input#price').val(totalPrice.text());
}

function bed_tooltip() {
	"use strict";
	
	$('#accommodation-calendar').on('mouseover', 'td.available', function (event) {
		var beds = $(this).attr('title');

		if (typeof beds !== 'undefined' && beds !== false) {				
			if (event.pageX) {
				$('#tooltip_hint').css({
					top: event.pageY + 15,
					left: event.pageX - 30
				}).html("There are <strong>" + beds + "</strong> beds available on this day.").show();
			} else {
				$('#tooltip_hint').hide();
			}
		}

		return false;
	});

	$('#accommodation-calendar').on('mouseout', 'table.ui-datepicker-calendar', function (event) {
		$('#tooltip_hint').hide();
	});
}

function accom_calendar(id, unavDates, bedDates, totalBeds, isBunkBarn) {
	"use strict";
	
	var url = '/bivouac/index.php/accommodation/get_durations',
		pHolidays = [];

	if (isBunkBarn) {
		url = '/bivouac/index.php/accommodation/get_bunk_barn_durations';
	}

	// Get Public Holiday dates
	$.ajax({
		url: '/bivouac/index.php/accommodation/get_public_holidays',
		dataType: 'json',
		type: 'post',
		success: function (data) {
			$.each(data, function (key, val) {
				var pHoliday = val.start_date.split("-"),
					pHolidayArray = [pHoliday[0], pHoliday[1], pHoliday[2]];

				pHolidays.push(pHolidayArray);
			});
			
			// Initialize Calendar
			$('#accommodation-calendar').datepicker({
				firstDay: 1,
				//minDate: 0,
				minDate: "04/01/2012",
				maxDate: "12/31/2012",
				onSelect: function (dateText, inst) {
					var selectedDate = inst.selectedDay + "-" + (inst.selectedMonth + 1) + "-" + inst.selectedYear;

					// Put selected date into form field
					$('input#start_date').val(selectedDate);

					// Get all duration options and prices based on selected arrival date
					$.ajax({
						url: url,
						dataType: 'json',
						data: {id: id, 'start_date': selectedDate},
						type: 'post',
						success: function (data) {
							var options = "<option value=''>Please select how long you wish to stay</option>";
							
							$.each(data.durations, function (key, val) {
								var nights = val[0],
									price = val[1],
									start = val[2],
									end = val[3];

								if (isBunkBarn) {
									if (nights === 1) {
										options += "<option value=" + nights + " data-price=" + price + ">" + nights + " night - £" + price + "pp (" + start + " - " + end + ")</option>";
									} else {
										options += "<option value=" + nights + " data-price=" + price + ">" + nights + " nights - £" + price + "pp (" + start + " - " + end + ")</option>";
									}
								} else {
									if (nights === 1) {
										options += "<option value=" + nights + " data-price=" + price + ">" + nights + " night - £" + price + " (" + start + " - " + end + ")</option>";
									} else {
										options += "<option value=" + nights + " data-price=" + price + ">" + nights + " nights - £" + price + " (" + start + " - " + end + ")</option>";
									}
								}
							});

							$('#duration').html(options);
							
							// If bunk barn we need to calculate price based on number of guests
							if (isBunkBarn) {
								$('#adults, #children').change(function () {
									var price = parseInt($('#duration option:selected').data('price'), 10);
									
									price = price * (parseInt($('#adults').val(), 10) + parseInt($('#children').val(), 10));
									$('#total_price').val(price);
								});	
							}
						}
					});
				},
				beforeShowDay: function (date) {
					var day = date.getDay(),
						month = date.getMonth(),
						year = date.getFullYear(),
						thisDate = date.getDate(),
						today = new Date(),
						currentDate = new Date(),
						i;

					// Set dates in the past to unavailable
					if (currentDate < today) {
						return [false, 'unavailable'];
					}

					// Process unavailable/already booked dates	
					for (i = 0; i < unavDates.length; i++) {
						if (month == unavDates[i][1] - 1 && thisDate == unavDates[i][0] && year == unavDates[i][2]) {
							return [false, 'unavailable'];
						}
					}

				    // Process number of beds tooltip - For bunk barns only
				    if (isBunkBarn) {
						for (i = 0; i < bedDates.length; i++) {
							if (month == bedDates[i][0][1] - 1 && thisDate == bedDates[i][0][0] && year == bedDates[i][0][2]) {
								return [true, 'book-in-date available', bedDates[i][1]];
							}
						}

						return [true, 'book-in-date available', totalBeds];
					}
					
				    if (!isBunkBarn) {
						// Run through public holiday dates. Add class if current date is in holidays array.
						for (i = 0; i < pHolidays.length; i++) {
							var holDate = pHolidays[i][0],
								holMonth = pHolidays[i][1] - 1,
								holYear = pHolidays[i][2],
								nextDay = parseInt(pHolidays[i][0], 10) + 1,
								nextDate = new Date(holYear, holMonth, nextDay);

							if (month == holMonth && thisDate == holDate && year == holYear) {
								if (weekdays[day - 1] == 'Friday') {
									return [true, 'book-in-date available'];
								} 
								
								if (weekdays[day - 1] == 'Monday') {
									return [false, 'available public_holiday'];
								} 

								return false;
								
							} 
							
							if (month == nextDate.getMonth() && thisDate == nextDate.getDate() && year == nextDate.getFullYear()) {
								if (weekdays[day - 1] === 'Tuesday') {
									return [true, 'book-in-date available'];
								}
							}
						}

						if (weekdays[day - 1] === 'Monday' || weekdays[day - 1] === 'Friday') {
							return [true, 'book-in-date available'];
						}

						return [false, 'available'];
				    }
				}
			});
		}
	});
}

$(function () {
	"use strict";

	// 3D Secure Page
	if ($('#payment_3d_secure_form').length > 0) {
		setTimeout(submit3DSecure, 8000);
	}	
	
	if ($('#accessibility').length > 0) {
		$('#accessibility').fancybox();
	}

	// General Calendar
	if ($('#calendar').length > 0) {
		// Get all closed dates
		var closedDates = [],
			pHolidays = [];

		$.ajax({
			url: '/bivouac/index.php/booking/get_unavailable_dates',
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
			url: '/bivouac/index.php/accommodation/get_public_holidays',
			dataType: 'json',
			type: 'post',
			success: function (data) {
				$.each(data, function (key, val) {
					var pHoliday = val.start_date.split("-"),
						pHolidayArray = [pHoliday[0], pHoliday[1], pHoliday[2]];

					pHolidays.push(pHolidayArray);
				});
				
				$('#calendar').datepicker({
					firstDay: 1,
					//minDate: 0,
					minDate: "04/01/2012",
					maxDate: "12/31/2012",
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
								} 
								
								if (weekdays[day - 1] == 'Monday') {
									return [false, 'available public_holiday'];
								}
								
								return false;
							} 
							
							if (month == nextDate.getMonth() && thisDate == nextDate.getDate() && year == nextDate.getFullYear()) {
								if (weekdays[day - 1] === 'Tuesday') {
									return [true, 'book-in-date available'];
								}
							}
						}
		
						if (weekdays[day - 1] === 'Monday' || weekdays[day - 1] === 'Friday') {
							return [true, 'book-in-date available'];
						}
						
						return [false, 'available'];
					},
					onSelect: function (dateText, inst) {
						var selectedDate = inst.selectedDay + "-" + (inst.selectedMonth + 1) + "-" + inst.selectedYear,
							d = new Date(dateText),
							options;
							
						// Remove all accommodation options when start_date changed
						$('#accommodation').html("");
		
						// Put selected date into form field
						$('input#start_date').val(selectedDate);
		
						// Get all duration options based on selected arrival date
						$.ajax({
							url: '/bivouac/index.php/booking/get_durations',
							dataType: 'json',
							data: {'start_date': selectedDate},
							type: 'post',
							success: function (data) {
								var options = "<option value=''>Please select how long you wish to stay</option>";
		
								$.each(data.durations, function (key, val) {
									var nights = val[0],
										start = val[1],
										end = val[2];
		
									if (nights === 1) {
										options += "<option value=" + nights + ">" + nights + " nights (" + start + " - " + end + ")</option>";
									} else {
										options += "<option value=" + nights + ">" + nights + " nights (" + start + " - " + end + ")</option>";
									}
								});
		
								$('#duration').html(options);
								$('#duration-container').css("background-color", '#99CCCC').stop(true).animate({"background-color" : "#f3f1ed"}, 5000);
							}
						});
					}
				});
			}
		});

		// When a duration is selected, find what accommodation is available.
		$('#duration').change(function () {
			var multiple = $('#multiple-units').val();
		
			get_available_accommodation();

			$('input[type=submit]').hide();
			
			if (multiple === "no"){
				$('div.price-container').hide();
			} else {
				$('div.price-container').show();
			}
			
			check_guests_against_chosen_accom();
		});
		
		// If user changes 'book multiple accommodation units' select
		$('#multiple-units').change(function () {
			var duration = $('#duration').val();
			
			if ($(this).val() === "no") {
				$('div.accommodation-checkbox-container').hide();
				$('input.accommodation-checkbox').attr('checked', false);
				$('div.accommodation-beds-container').hide();
				$('div.accommodation-beds-container select').val(0);
				
				check_occupancy_levels();
			} else {
				$('div.accommodation-checkbox-container').show();
				$('div.accommodation-beds-container').show();
				$('a.need-multiple-units').hide();
				$('a.quick-book-link').hide();
			}
			
			if ($(this).val() === "no" && duration !== "") {
				$('input[type=submit]').hide();
				$('div.price-container').hide();
			} 
			
			if ($(this).val() === "yes" && duration !== "") {
				$('input[type=submit]').show();
				$('div.price-container').show();
			}
			
			check_guests_against_chosen_accom();
		});
		
		// When adults or children is changed check against total occupancy of each unit
		$('#adults, #children').change(function () {
			check_occupancy_levels();
			check_guests_against_chosen_accom();
		});
		
		$('#babies').change(function () {
			babyCheck();
		});
		
		$('#dogs').change(function () {
			check_for_dogs();
		});
	}
	
	// Results Page
	if ($('#results-form').length > 0) {
		// If there are babies we need to hide the bunk barns
		babyCheck();
	
		// If user clicks a quick book link
		$('a.quick-book-link').on('click', function (e) {		
			e.preventDefault();
				
			// Create a hidden accomodation id field with id
			var li = $(this).closest('li'),
				id = li.data('id'),
				price = parseFloat(li.data('price')),
				input = "<input type='hidden' name='accommodation_ids[]' value='" + id + "' />";
			
			// Check if this is a bunk barn! total guests if it is!
			if (li.data('type') === "Bunk Barn") {
				price = price * (parseInt($('#adults').val(), 10) + parseInt($('#children').val(), 10));
			}
			
			$('#total_price').val(price);
			$('#results-form').append(input).submit();
		});
		
		// View full accommodation information lightbox
		$('a.lightbox-full-accommodation').click(function () {
			var id = $(this).closest('li').data('id');
			
			$('#accommodation-lightbox').load('https://booking.thebivouac.co.uk/accommodation/unit/' + id + ' #full-unit-information', function () {
				// Show lightbox now...
				$.fancybox('#accommodation-lightbox');
			});
			
			return false;
		});
		
		// Change to book multiple accommodation
		$('a.need-multiple-units').on('click', function () {
			$('#multiple-units').val('yes').change();
			
			return false;
		});
		
		// When multiple accommodation units selected calculate if there are enough beds for guests
		// Also calculate running total booking price
		$('#accommodation input, #accommodation select').change(function () {
			// Set #total_price of all selected accommodation units
			var newPrice = 0;
			
			check_guests_against_chosen_accom();
			
			// Get sleep capactiy for all checked accommodation
			$('#accommodation').find('input.accommodation-checkbox').each(function () {
				if ($(this).is(':checked')) {
					newPrice += parseFloat($(this).closest('li').data('price'));
				}
			});
			
			// Get number of beds selected for all bunk barn dropdowns
			$('#accommodation').find('select').each(function () {
				newPrice += parseInt($(this).val(), 10) * parseFloat($(this).closest('li').data('price'));
			});

			$('#total_price').val(newPrice);
			$('span.total-price').text(newPrice);
		});

		
		// If user changes 'book multiple accommodation units' select
		$('#multiple-units').change(function () {
			if ($(this).val() === "no") {
				$('div.accommodation-checkbox-container').hide();
				$('input.accommodation-checkbox').attr('checked', false);
				$('div.accommodation-beds-container').hide();
				$('div.accommodation-beds-container select').val(0);
				$('div.price-container').hide();
				$('input[type=submit]').hide();
			} else {
				$('a.quick-book-link').hide();
				$('a.need-multiple-units').hide();
				$('div.accommodation-checkbox-container').show();
				$('div.accommodation-beds-container').show();
				$('div.price-container').show();
			}
			
			check_occupancy_levels();
			check_guests_against_chosen_accom();
		}).change();
	}

	// Accommodation Unit specific calendar
	if ($('#accommodation-calendar').length > 0) {
		// Get all unavailable dates for this accommodation
		var id = $('#accommodation-calendar').data('accom-id'),
			unavDates = [],
			bedDates = [],
			url = '/accommodation/get_booked_dates',
			isBunkBarn = false,
			totalBeds = 0;

		// Set option if accommodation is a bunk barn	
		if ($('#accommodation-calendar').data('type') === 'Bunk Barn') {
			isBunkBarn = true;
		}

		// Process all unavailable dates and those with remaining beds
		$.ajax({
			url: url,
			dataType: 'json',
			data: {id: id, 'bunk_barn': isBunkBarn},
			type: 'post',
			success: function (data) {
				totalBeds = data.beds;

				if (data.response == true) {
					var date, dateArray, remainingBeds;
					
					$.each(data.dates, function (key, val) {
						if (isBunkBarn) {
							date = val[0].split("-");
							dateArray = [date[0], date[1], date[2]];
							remainingBeds = [dateArray, val[1]];

							bedDates.push(remainingBeds);

							if (remainingBeds[1] === 0) {
								unavDates.push(dateArray);
							}
						} else {
							date = val[0].split("-");
							dateArray = [date[0], date[1], date[2]];

							unavDates.push(dateArray);
						}
					});
				}

				accom_calendar(id, unavDates, bedDates, totalBeds, isBunkBarn);
				
				if (isBunkBarn) {
					bed_tooltip();
			    }
			}
		});

		// Add current total price into form field
		$('select#duration').bind('blur change', function () {
			$('input#total_price').val($(this).find('option:selected').data('price'));
		});
		
		// When adults or children changed, calcualte total guests and check against 
		// how many the unit sleeps. Show message if guests are over.
		$('#adults, #children').change(function () {
			var adults = $('#adults').val(),
				children = $('#children').val(),
				guests = parseInt(adults, 10) + parseInt(children, 10),
				sleeps = $('#accommodation-calendar').data('sleeps');
				
			if (guests > sleeps) {
				$('p.user-message').show();
				$('input[type=submit]').hide();
			} else {
				$('p.user-message').hide();
				$('input[type=submit]').show();
			}
		});
	}

	// Process live Price -> Extras Page
	if ($('#extras').length > 0) {
		$('input[type=checkbox], select').change(function () {
			var extra = $(this).closest('div.extra-preferences'),
				quantity = extra.find('.extra_quantity').val(),
				price = extra.find('.extra_price').val(),
				nights = extra.find('.extra_nights').val(),
				dates = extra.find('.date-option').length,
				prev;

			// Does this extra work on number of dates?
			if (dates > 0) {
				if ($(this).is(':checked')) {
					// Checkbox that is checked
					update_prices(1, 0, price);
				} else {
					update_prices(1, 0, -price);
				}
			} else {
				if (extra.find('.extra_quantity').is('input:checkbox')) {
					if (extra.find('.extra_quantity').is(':checked')) {
						// Checkbox that is checked
						update_prices(quantity, 0, price);
					} else {
						update_prices(quantity, 0, -price);
					}
				} else {
					// prevval data attribute exists and is greater than 0
					if ($(this).data('prevval') > 0) {
						prev = $(this).data('prevval');
	
						if ($(this).hasClass('extra_quantity')) {
							quantity = -prev;
						} else {
							nights = -prev;
						}
	
						update_prices(quantity, nights, price);
	
						// If current val == 0 then just remove from total
						// else remove and then add new quantity to price
						if ($(this).val() != 0) {
							if ($(this).hasClass('extra_quantity')) {
								quantity = extra.find('.extra_quantity').val();
							} else {
								nights = extra.find('.extra_nights').val();
							}
	
							update_prices(quantity, nights, price);
						}
					} else {
						update_prices(quantity, nights, price);
					}
	
					// Set previous value to data
					$(this).data('prevval', $(this).val());
				}
			}
			
			// Change value of submit button
			$('#submit').val('Add these extras to your booking');
		});
	}

	// Process Contact Page
	if ($('#contact').length > 0) {
		var dob,
			dobValid = true,
			dobDay,
			dobMonth,
			dobYear,
			dobString = "";

		// Calculate age from DOB dropdown options
		$('select.dob').change(function () {
			// Only calculate age if there are no '--' values in dob options
			$('select.dob').each(function (index) {
				if ($(this).val() === "--") {
					dobValid = false;
					return false;
				}
				
				// Create date string
				if (index === 0) {
					dobDay = $(this).val();
				} else if (index === 1) {
					dobMonth = $(this).val();
				} else {
					dobYear = $(this).val();
				}				
			});

			if (dobDay && dobMonth && dobYear) {
				dobString = dobMonth + "/" + dobDay + "/" + dobYear;
			}

			if (dobValid) {
				dob = new Date(Date.parse(dobString));

				if (dob.age(new Date()) < 18) {
				    alert("Sorry you are not old enough to make a booking at the Bivouac. You must be 18 years or older.");

				    // Hide submit button if it is visible
					if ($('#submit').is(":visible")) {
						$('#submit').hide();
				    }
				}
			}	
		});

		// T & C's must be checked in order to continue.
		$('input#terms_and_conditions').change(function () {
			if ($(this).is(':checked')) {
				$('#contact #submit').show();
			} else {
				$('#contact #submit').hide();
			}
		});
		
		// Forgot Password Link and form processing
		$('#forgot_password_link').click(function () {
			// Hide login form and show password reset form
			$('#login_form').hide();
			$('#forgot_password_form').show();
			
			return false;
		});
		
		$('#forgot_password_form').on('submit', function () {
			$.ajax({
				url: '/bivouac/index.php/booking/forgot_password',
				data: $(this).serialize(),
				dataType: 'json',
				type: 'POST',
				success: function (data) {
					alert(data.message);
					
					if (data.message_type == "success") {
						$('#forgot_password_form').hide();
						$('#login_form').show();
					}
				}
			});
			
			return false;
		});
		
		// Check email address against existing members
		$('#contact-booking-form').find('#email_address').on('keyup', function () {
			$.ajax({
				url: '/bivouac/index.php/booking/check_email_address',
				data: {'email_address': $(this).val()},
				type: 'POST',
				dataType: 'json',
				success: function (data) {
					if (data.status == "error") {
						alert(data.message);
						$('#contact-booking-form').find('#submit').hide();
					}
				}
			});
		});
		
		// Terms and conditions lightbox
		$('#terms').fancybox();
	}
	
	// Newsletter lightbox
	$('#newsletter').fancybox({
		fitToView	: false,
		autoSize	: true,
		closeClick	: false,
		closeBtn	: true,
		openEffect	: 'none',
		closeEffect	: 'none',
		title		: true
	});
	
	// Accommodation Image Gallery
	if ($('ul.accommodation-image-slider').length > 0) {
		var galleryNav = $('ul.accommodation-image-thumbs');
	
		$('ul.accommodation-image-slider').cycle({
			speed: 1000,
			timeout: 0
		});
		
		// Make first slider nav link active
		galleryNav.find('li').eq(0).children('a').addClass('active');
		
		// When sub section nav link clicked, show appropriate content/slide
		galleryNav.find('a').on('click', function () {
			var li = $(this).parent(),
				slide = galleryNav.find('li').index(li);
			
			galleryNav.find('a.active').removeClass('active');
			$(this).addClass('active');
			
			$('ul.accommodation-image-slider').cycle(slide);
			
			return false;
		});
	}
});