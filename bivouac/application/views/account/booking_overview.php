<?php 
$data['title'] = "Booking Overview";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="container" class="clearfix">
	<?php if ($this->session->userdata('is_logged_in') === TRUE): ?>
		<p class="user-logout"> 
			Hello <b><?php echo $this->session->userdata('screen_name'); ?></b> | <?php echo anchor('account/logout', 'Log out'); ?>
		</p>
	<?php endif; ?>
	<?php echo anchor('/account/bookings', 'My Account', array('title' => 'Login to your account', 'class' =>'login-tab')); ?>
	
	<?php $this->load->view("_includes/frontend/header"); ?>
	
	<div id="main" role="main">
		<!-- Site Details -->
		<div class="site-details clearfix">
			<div class="telephone-number">
				<h1><span class="telephone-icon"></span>01765 53 50 20</h1>
			</div>
			<div class="social-media">
				<ul>
					<li>
						<a href="http://www.facebook.com/wearethebivouac" title="See what we're up to on Facebook"><span class="facebook-icon"></span> Facebook</a>
					</li>
					<li>
						<a href="https://twitter.com/#!/thebivouac" title="Folllow us on Twitter"><span class="twitter-icon"></span> Twitter</a>
					</li>
				</ul>
			</div>
		</div>
	
		<section>
			<h1>My Account</h1>
		
			<div class="account-panel clearfix">
				<ul class="account-nav">
					<li><?php echo anchor('/account/bookings', 'Bookings', array('title' => 'View your bookings', 'class' => 'active')); ?></li>
					<li><?php echo anchor('/account/settings', 'Account Settings', array('title' => 'Update your email address and password')); ?></li>
					<li><?php echo anchor('/account/address', 'Account Address', array('title' => 'Update your address')); ?></li>
				</ul>
				
				<div class="account-section-content">
					<h2>Booking Details</h2>
					<p><?php echo anchor('account/bookings', 'Back to all bookings', array('title' => 'Show show booking history')); ?></p>
				
					<div class="booking-details overview-section">
						<?php 
							if ($booking_details->num_rows() > 0):
								$booking = $booking_details->row();
								$total_guests = (int)$booking->adults + (int)$booking->children + (int) $booking->babies;
						?>
							<h3>Booking Details</h3>
							<p>
								<b>Booking Reference No.</b> <?php echo $booking->booking_ref; ?><br />
								<b>Arrival Date.</b> <?php echo date('l dS M Y', strtotime($booking->start_date)); ?><br />
								<b>Departure Date.</b> <?php echo date('l dS M Y', strtotime($booking->end_date)); ?><br />
								<b>Total Price.</b> &pound;<?php echo $booking->total_price; ?><br />
								<b>Total number of Guests.</b> <?php echo $total_guests; ?>
							</p>		
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
							<?php 
								foreach ($accommodation_details as $accommodation): 
									if ($accommodation->num_rows() > 0):
										$accommodation_row = $accommodation->row();
							?>
								<p>
									<b>Accommodation Unit.</b> <?php echo $accommodation_row->name; ?> (<?php echo $accommodation_row->type_name; ?>)<br />
								</p>	
							<?php
									endif;
								endforeach;
							?>
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
				</div>
			</div>	
		</section>
	</div>

	<?php $this->load->view("_includes/frontend/footer"); ?>	
</div>
<?php $this->load->view("_includes/frontend/js"); ?>