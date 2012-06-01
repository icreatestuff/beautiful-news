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
	?>
	Dear <?php echo  $contact . " " . $contact_row->first_name . " " . $contact_row->last_name; ?>,<br /><br /> Thank you for paying the balance on your booking. Ref: <?php echo $booking->booking_ref; ?>.<br />We look forward to meeting you on arrival on <?php echo date('l dS M Y', strtotime($booking->start_date)); ?>. <br /><span style="color: #666; font-size: 12px;"><i>Access to your accommodation is from 3pm on the day of your arrival.</i></span><br /><br /><b>Enjoy your stay with us.</b><br />Best wishes, Bivouac 
		<?php endif; ?>
	<?php endif; ?>
</body>
</html>