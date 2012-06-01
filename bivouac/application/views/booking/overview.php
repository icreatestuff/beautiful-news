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
						<a href="https://twitter.com/#!/thebivouac" title="Follow us on Twitter"><span class="twitter-icon"></span> Twitter</a>
					</li>
				</ul>
			</div>
		</div>
	
		<section>
			<ol class="booking-flow-indicators clearfix">
				<li>When &amp; where</li>
				<li>Holiday Extras</li>
				<li>Contact Details</li>
				<li class="active">Booking Overview</li>
				<li>Payment</li>
				<li>Confirmation</li>
			</ol>
			
			<h1>Booking Details Overview</h1>
			<p>Hello <b><?php echo $user['screen_name']; ?></b>.<br />Please check all the details below about your booking before proceeding to pay. Thank you.</p>

			<div class="content" id="overview">		
				<div class="booking-details overview-section">
					<?php 
						if ($booking_details->num_rows() > 0):
							$booking = $booking_details->row();
					?>
						<h3>Booking Details</h3>
						<p>
							<b>Booking Reference No.</b> <?php echo $booking->booking_ref; ?><br />
							<b>Arrival Date.</b> <?php echo date('l dS M Y', strtotime($booking->start_date)); ?><br />
							<b>Departure Date.</b> <?php echo date('l dS M Y', strtotime($booking->end_date)); ?><br />
							<b>Total Price.</b> &pound;<?php echo money_format('%i', $booking->total_price); ?><br />
							<b>Total Number of Guests.</b> <?php echo (int) $booking->adults + (int) $booking->children; ?><br />
							<b>Adults.</b> <?php echo $booking->adults; ?><br />
							<b>4 to 17s.</b> <?php echo $booking->children; ?><br />
							<b>0 to 3s.</b> <?php echo $booking->babies; ?>
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
						<p><?php echo anchor('booking/edit_contact/' . $booking->id, 'Edit booking contact details', array('title' => 'Edit booking contact details')); ?></p>
						<p>
							<b>Name.</b> <?php echo  $contact->title . " " . $contact->first_name . " " . $contact->last_name; ?><br />
							<b>Email Address.</b> <?php echo $contact->email_address; ?><br />
							<b>Daytime Contact Number.</b> <?php echo $contact->daytime_number; ?>
							<?php if (!empty($contact->mobile_number)): ?>
							<br /><b>Mobile Number.</b> <?php echo $contact->mobile_number; ?>
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
						<p>If the unit(s) selected are not available you may be allocated another unit. We will inform you of any changes that may need to be made.</p>
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
					<h3>Extras</h3>
					<p><?php echo anchor('booking/edit_extras/' . $booking->id, 'Add/Edit Extras to this booking', array('title' => 'Add/Edit Extras associated with this booking')); ?></p>
					<?php if ($extras->num_rows() > 0): ?>
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
									<td><?php echo money_format('%i', $extra->price); ?></td>
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
							
				<?php echo form_open('booking/overview/' . $booking_id, array('id' => 'overview-form', 'class' => 'booking-form')); ?>
				
				<?php
					if ($site_details->num_rows() > 0)
					{
						foreach ($site_details->result() as $site)
						{
							if ($site->id == 1)
							{
								$deposit = $site->deposit_percentage;
							}
						}
					}
				?>
				
				<?php if ($what_paying === "deposit"): ?>
				<p>
					<h1>What do you want to pay now?</h1>
					<p>If you choose to pay the <?php echo $deposit . "%"; ?> deposit now, the remaining balance will be due by <?php echo date('l, jS F Y', strtotime(' - 6 weeks', strtotime($booking->start_date))); ?>. We will email you a reminder closer to the time but please add this date to your diary!</p>
					<select name="what_paying" id="what_paying">
						<option value="full_amount">Full Amount - &pound;<?php echo $price / 100; ?></option>
						<option value="deposit">Deposit - &pound;<?php echo $deposit_amount; ?></option>
					</select>
				</p>
				<?php else: ?>
					<input type="hidden" name="what_paying" value="full_amount" />
				<?php endif; ?>		
				
				<p>
					<label for="voucher">Voucher/Promo Code</label>
					<input type="text" name="voucher" id="voucher" value="" />
				</p>
				
				<p>
					<input type="submit" id="submit" name="submit" value="Proceed to Payment" />
				</p>
						
				</form>
			</div>
		</section>
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>	
</div>
<?php $this->load->view("_includes/frontend/js"); ?>