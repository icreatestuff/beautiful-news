<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<style type="text/css">
		.ReadMsgBody { width: 100%;}
		.ExternalClass {width: 100%;}
	</style>
</head>
<body style="background-color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; margin: 0; padding: 10px; outline: 0; color: #333333;">
	<?php 
		if ($booking_details->num_rows() > 0):
			$booking = $booking_details->row();
			
			if ($contact_details->num_rows() > 0):
				$contact_row = $contact_details->row();	
	?>
	
	<?php
		if ($contact_row->title == "--")
		{
			$contact = "";
		}
		else
		{
			$contact = $contact_row->title;
		}
		
		$balance = (float) $booking->total_price - (float) $booking->amount_paid;
		$balance = money_format('%i', $balance);
	?>
	Dear <?php echo  $contact . " " . $contact_row->first_name . " " . $contact_row->last_name; ?>,<br /><br /><b>Booking Ref:</b> <?php echo $booking->booking_ref; ?>.<br /><b>Arrival Date:</b> <?php echo date('d/m/Y', strtotime($booking->start_date)); ?><br /><br />The date of your holiday is approaching and the final balance of &pound;<?php echo $balance; ?> is now due. <br /><br />Payment can be made by debit or credit card online at <a href="https://booking.thebivouac.co.uk/account">booking.thebivouac.co.uk/account</a> or by contacting us on 01765 53 50 20. Lines are open seven days a week 9am-9pm.<br /><br />If you have recently paid your balance then please ignore this email reminder.<br /><br />We look forward to welcoming you to Bivouac soon.<br /><br />Best wishes, Bivouac 
		<?php endif; ?>
	<?php endif; ?>
</body>
</html>