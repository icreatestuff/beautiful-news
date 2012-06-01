<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<style type="text/css">
		.ReadMsgBody { width: 100%;}
		.ExternalClass {width: 100%;}
	</style>
</head>
<body style="background-color: #e4ddce; background-image: url(http://www.thebivouac.co.uk/images/biv-bg.jpg); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; margin: 0; padding: 0; outline: 0; color: #333333;">
	<table cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #e4ddce; background-image: url(http://www.thebivouac.co.uk/images/biv-bg.jpg); margin: 0; padding: 0; outline: 0; border-collapse: collapse; border-spacing: 0; width: 100%">
		<tr style="margin: 0; padding: 0; outline: 0; border: 0;">
			<td align="center" style="margin: 0; padding-top: 6px; padding-bottom: 6px; outline: 0; vertical-align: top;">
				<!-- Header -->
				<table cellspacing="0" cellpadding="0" border="0" style="margin: 0; padding: 0; outline: 0; border-collapse: collapse; border-spacing: 0; width: 520px;">
					<tr>
						<td style="padding-top: 30px; padding-bottom: 30px;">
							<img src="http://www.thebivouac.co.uk/images/biv-logo.png" width="261" height="64" style="margin: 0; padding-left: 5px; line-height: 0;">
						</td>
					</tr>
				</table>
				
				<!-- Content -->
				<table style="background: #ffffff; padding-top: 20px; padding-right: 20px; padding-left: 20px; width: 500px; font-size: 14px; line-height: 21px;">
				<?php 
					if ($booking_details->num_rows() > 0):
						$booking = $booking_details->row();
				?>
					<!-- Booking Details -->
					<tr style="margin: 0; padding: 0; outline: 0; border: 0;">						
						<td style="margin: 0; padding-bottom: 20px; outline: 0; border: 0; border-bottom: 1px dotted #aaaaaa;">
							<?php
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
							
							Dear <?php echo  $contact . " " . $contact_row->first_name . " " . $contact_row->last_name; ?>,<br /><br />
							<?php endif; ?>
							Thanks for booking your break at Bivouac. We're pleased to confirm your reservation. This is a receipt of your online booking. If you have any questions, please feel free to call us on 01765 535 020 or email us at <a href="mailto:bookings@thebivouac.co.uk" style="color: #77b5b5;">bookings@thebivouac.co.uk</a><br />
							<span style="font-size: 12px; line-height: 18px; color: #666666;"><i>Please have your booking reference number to hand when contacting us so we can help you as quickly as possible.</i></span>
							<h3 style="font-size: 20px; line-height: 24px; font-weight: bold; padding-top: 30px; margin: 0; margin-bottom: 10px; color: #333333 !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Booking Details</h3>

							<span class="color: #3b86bb;"><b>Booking Reference No.</b> <?php echo $booking->booking_ref; ?></span><br />
							<b>Arrival Date.</b> <?php echo date('l dS M Y', strtotime($booking->start_date)); ?><br />
							<b>Departure Date.</b> <?php echo date('l dS M Y', strtotime($booking->end_date)); ?><br />
							<b>Total Number of Guests.</b> <?php echo (int) $booking->adults + (int) $booking->children + (int) $booking->babies; ?> (<?php echo $booking->adults; ?> - Adult<?php if ($booking->adults > 1) { echo "s"; } ?>, <?php echo $booking->children; ?> - 4 to 17<?php if ($booking->children > 1) { echo "s"; } ?>, <?php echo $booking->babies; ?> - 0 to 3<?php if ($booking->babies > 1) { echo "s"; } ?>)<br />
							
							<?php 
								foreach ($accommodation_details as $accommodation): 
									if ($accommodation->num_rows() > 0):
										$accommodation_row = $accommodation->row();
							?>
							<b>Accommodation Unit.</b> <?php echo $accommodation_row->name; ?> (<?php echo $accommodation_row->type_name; ?>)<br />
							<?php
									endif;
								endforeach;
							?>
							<br /><br />
							
							<span style="font-size: 12px; line-height: 18px; color: #666666;"><i>Access to your accommodation is from 3pm on the day of your arrival. You must vacate your accommodation by 10am on the day of your departure.</i></span>
							
						</td>
					</tr>	
				<?php
					endif;
				?>
				
					<!-- Extras -->
					<tr>
						<td style="margin: 0; padding-top: 20px; padding-bottom: 20px; outline: 0; border-bottom: 1px dotted #aaaaaa;">
							<h3 style="font-size: 20px; line-height: 24px; font-weight: bold; margin: 0; margin-bottom: 10px; padding: 0; color: #333333 !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Extras</h3>
							<?php if ($extras->num_rows() > 0): ?>
							<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0; padding: 0; outline: 0; border-collapse: collapse; border-spacing: 0; width: 100%">
								<tbody>
									<?php foreach ($extras->result() as $extra): ?>
									<?php
										if (!empty($extra->nights)) { 
											$nights = $extra->nights; 
										} 
										else 
										{ 
											if (!empty($extra->date))
											{
												$nights = date('l jS M Y', strtotime($extra->date));
											}
											else
											{
												if ($extra->extra_type == 4)
												{
													$nights = "N/A"; 
												}
												else
												{
													$nights = "Delivered on your arrival date"; 
												}
											}
										}
									?>
									<tr style="border-top: 1px solid #bbb;">
										<td style="font-size: 12px; line-height: 18px; color: #666; text-align: left; padding-top: 2px padding-bottom: 2px;"><?php echo $extra->name; ?></td>
										<td style="font-size: 12px; line-height: 18px; color: #666; text-align: left; padding-top: 2px padding-bottom: 2px;"><?php echo $extra->quantity; ?></td>
										<td style="font-size: 12px; line-height: 18px; color: #666; text-align: left; padding-top: 2px padding-bottom: 2px;"><?php echo $nights; ?></td>
										<td style="font-size: 12px; line-height: 18px; color: #666; text-align: left; padding-top: 2px padding-bottom: 2px;"><?php echo $extra->price; ?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<thead>
									<tr style="border-top: 2px solid #bbb;">
										<th style="font-size: 12px; line-height: 18px; color: #333; text-align: left; padding-top: 2px padding-bottom: 2px;">Name</th>
										<th style="font-size: 12px; line-height: 18px; color: #333; text-align: left; padding-top: 2px padding-bottom: 2px;">Quantity</th>
										<th style="font-size: 12px; line-height: 18px; color: #333; text-align: left; padding-top: 2px padding-bottom: 2px;">Days/Nights</th>
										<th style="font-size: 12px; line-height: 18px; color: #333; text-align: left; padding-top: 2px padding-bottom: 2px;">Price (&pound;)</th>
									</tr>
								</thead>
							</table>
							<?php else: ?>
								You did not book any extras.	
							<?php endif; ?>
						</td>
					</tr>
					
					<!-- Total Price -->
					<tr>
						<td style="margin: 0; padding-top: 20px; padding-bottom: 20px; outline: 0; border: 0;">
							<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0; padding: 0; outline: 0; border-collapse: collapse; border-spacing: 0; width: 100%; font-size: 14px; line-height: 21px;">
								<tr>
									<td>
										<h3 style="font-size: 20px; line-height: 24px; font-weight: bold; margin: 0; margin-bottom: 10px; padding: 0; color: #333333 !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Total Amount</h3>
										<b>Total Price.</b> &pound;<?php echo $booking->total_price; ?><br />
										<b>Amount Paid.</b> &pound;<?php echo $booking->amount_paid; ?><br />
										<?php 
											$balance = (float) $booking->total_price - (float) $booking->amount_paid; 
											$balance = money_format('%i', $balance);
										?>
										<b>Balance Remaining.</b> &pound;<?php echo $balance; ?> <?php if ($balance > 0): ?><span style="font-size: 12px; color: #666666">(to be paid in full by <?php echo date('l, jS F Y', strtotime(' - 6 weeks', strtotime($booking->start_date))); ?>)</span><?php endif; ?><br /><br />
										
										<span style="font-size: 12px; line-height: 18px; color: #666666;"><b>How to pay.</b><i> Balancing payments can be made <a href="http://www.thebivouac.co.uk" style="color: #77b5b5;">online now</a> by logging into your account and completing the booking.</i></span><br /><br />
										
										<b>Enjoy your stay with us.</b><br />Best wishes, Bivouac 
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				
				<!-- Footer -->
				<table cellspacing="0" cellpadding="0" border="0" style="padding: 0; width: 520px;">
					<tr>
						<td style="padding: 0; margin: 0;">
							<img src="http://www.thebivouac.co.uk/images/email-bottom.png" width="520" height="38" style="margin: 0; padding: 0; line-height: 0">
							<p style="font-size: 11px; line-height: 16px; color: #333333; padding-right: 30px; padding-left: 30px;">In confirming your booking you have agreed to our <a href="http://www.thebivouac.co.uk/terms-and-conditions" style="color: #5d9595;">terms & conditions</a><br /><br />Bivouac Swinton Limited<br />Registered in England No. 07203550<br />High Knowle, Knowle Lane, Ilton, Masham, North Yorkshire, HG4 4JZ<br /><a href="http://www.thebivouac.co.uk" style="color: #5d9595;">thebivouac.co.uk</a><br />Tel. (0) 1765 53 50 20<br /><br />Please consider the environment before printing this email</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>