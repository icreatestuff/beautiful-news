<?php 
$data['title'] = "Booking Overview";
$data['location'] = "admin";
$this->load->view("_includes/admin/head", $data); 
?>
<div id="container">
	<?php 
		$header_data['sites'] = $sites;
		$header_data['current_site'] = $current_site;
		$this->load->view("_includes/admin/header", $header_data);
	?>
	
	<div id="main" class="clearfix">
		<?php $this->load->view("_includes/admin/nav"); ?>		
		<div id="content">
			<h2>Overview</h2>
			<section>
				<h1>Booking Overview</h1>
				
				<div class="booking-details overview-section">
				<?php 
					if ($booking_details->num_rows() > 0):
						$booking = $booking_details->row();
						$total_guests = (int) $booking->adults + (int) $booking->children;
				?>
					<h3>Booking Details</h3>
					<p>
						<b>Booking Reference No.</b> <?php echo $booking->booking_ref; ?><br />
						<b>Type of booking.</b> <?php echo $booking->type; ?><br />
						<b>Arrival Date.</b> <?php echo date('l dS M Y', strtotime($booking->start_date)); ?><br />
						<b>Departure Date.</b> <?php echo date('l dS M Y', strtotime($booking->end_date)); ?><br />
						<b>Total Number of Guests.</b> <?php echo $total_guests; ?> (Adults: <i><?php echo $booking->adults; ?></i>,  4 - 17s: <i><?php echo $booking->children; ?></i>, 0 - 3s: <i><?php echo $booking->babies; ?></i>)
					</p>
					<p>
						<b>Total Price.</b> &pound;<?php echo $booking->total_price; ?><br />
						<b>Amount Paid.</b> &pound;<?php echo $booking->amount_paid; ?><br />
						<b>Payment Status.</b> <?php echo $booking->payment_status; ?>
					</p>	
					
					<h3>Additional Notes for this booking</h3>	
					<p><?php if (!empty($booking->notes)) { echo $booking->notes; } else { echo "--"; } ?></p>
				<?php
					endif;
				?>
				</div>
				
				<div class="contact-details overview-section">
				<?php 
					if ($contact_details->num_rows() > 0):
						$contact = $contact_details->row();
				?>
					<h3>Contact Details</h3>
					<p>
						<b>Name.</b> <?php echo  $contact->title . " " . $contact->first_name . " " . $contact->last_name; ?><br />
						<b>Email Address.</b> <?php echo $contact->email_address; ?><br />
						<b>Daytime Contact Number.</b> <?php echo $contact->daytime_number; ?>
						<?php if (!empty($contact->mobile_number)): ?>
						<b>Mobile Number.</b> <?php echo $contact->mobile_number; ?>
						<?php endif; ?>
					</p>
					
					<p>
						<b>Address.</b><br />
						<?php echo $contact->house_name; ?><br />
						<?php echo $contact->address_line_1; ?><br />
						<?php if (!empty($contact->address_line_2)) { echo $contact->address_line_2 . "<br />"; } ?>
						<?php echo $contact->city; ?><br />
						<?php echo $contact->county; ?><br />
						<?php echo $contact->post_code; ?>
					</p>	
				<?php
					endif;
				?>
				</div>
				
				<div class="accommodation-details overview-section">
					<h3>Accommodation Details</h3>
					<p>
				<?php 
					foreach ($accommodation_details as $accommodation):
						if ($accommodation->num_rows() > 0):
							$accommodation_row = $accommodation->row();
				?>
					
						<b>Accommodation Unit.</b> <?php echo $accommodation_row->name; ?><br />	
				<?php
						endif;
					endforeach;
				?>
					</p>
				</div>
				
				<div class="extras-details overview-section">
					
					
				<?php if ($extras->num_rows() > 0): ?>
					<h3>Extras</h3>
					<table>
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
							<tr>
								<td><?php echo $extra->name; ?></td>
								<td><?php echo $extra->quantity; ?></td>
								<td><?php echo $nights; ?></td>
								<td><?php echo $extra->price; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<thead>
							<tr>
								<th>Name</th>
								<th>Quantity</th>
								<th>Days/Nights</th>
								<th>Price (&pound;)</th>
							</tr>
						</thead>
					</table>
				
				<?php endif; ?>
						
				</div>
				
				<?php echo anchor('admin/bookings/all_bookings', 'Back to bookings list', array('title' => 'Back to bookings list')); ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");