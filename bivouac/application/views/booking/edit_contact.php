<?php 
$data['title'] = "Fill in primary booking contact details!";
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
				<li class="active">Contact Details</li>
				<li>Booking Overview</li>
				<li>Payment</li>
				<li>Confirmation</li>
			</ol>

			<h1>Primary Booking Contact Details</h1>

			<div class="content clearfix" id="edit-contact">
				<div class="col415">
					<h2>Update the details for the primary booking contact</h2>
					<?php if ($contact_details->num_rows() > 0): ?>
						<?php $contact = $contact_details->row(); ?>
					
						<?php echo form_open('booking/edit_contact/' . $booking_id, array('id' => 'edit-contact-booking-form', 'class' => 'booking-form')); ?>
							<input type="hidden" name="contact_id" id="contact_id" value="<?php echo $contact->id; ?>" />
							<input type="hidden" name="member_id" id="member_id" value="<?php echo $contact->member_id; ?>" />			
							
							<p>					
								<label for="title">Title *</label>
								<select id="title" name="title">
									<option value="--" <?php if ($contact->title == "--"){ echo "selected"; } ?>>--</option>
									<option value="Mr" <?php if ($contact->title == "Mr"){ echo "selected"; } ?>>Mr</option>
									<option value="Mrs" <?php if ($contact->title == "Mrs"){ echo "selected"; } ?>>Mrs</option>
									<option value="Miss" <?php if ($contact->title == "Miss"){ echo "selected"; } ?>>Miss</option>
									<option value="Ms" <?php if ($contact->title == "Ms"){ echo "selected"; } ?>>Ms</option>
								</select>
							</p>
							
							<p>
								<label for="first_name">First Name *</label>
								<input type="text" name="first_name" id="first_name" value="<?php echo $contact->first_name; ?>" />
							</p>
							
							<p>
								<label for="last_name">Surname *</label>
								<input type="text" name="last_name" id="last_name" value="<?php echo $contact->last_name; ?>" />
							</p>
							
							<p>
								<label for="birth_day">Date of Birth *</label>
								<select id="birth_day" name="birth_day" class="dob">
									<option value="--" <?php if ($contact->birth_day == "--"){ echo "selected"; } ?>>Day</option>
									<?php for ($i=1; $i<=31; $i++): ?>
									<option value="<?php echo $i; ?>" <?php if ($contact->birth_day == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
									<?php endfor; ?>
								</select>
								
								<select id="birth_month" name="birth_month" class="dob">
									<option value="--" <?php if ($contact->birth_month == "--"){ echo "selected"; } ?>>Month</option>
									<option value="1" <?php if ($contact->birth_month == 1){ echo "selected"; } ?>>1 - Jan</option>
									<option value="2" <?php if ($contact->birth_month == 2){ echo "selected"; } ?>>2 - Feb</option>
									<option value="3" <?php if ($contact->birth_month == 3){ echo "selected"; } ?>>3 - Mar</option>
									<option value="4" <?php if ($contact->birth_month == 4){ echo "selected"; } ?>>4 - Apr</option>
									<option value="5" <?php if ($contact->birth_month == 5){ echo "selected"; } ?>>5 - May</option>
									<option value="6" <?php if ($contact->birth_month == 6){ echo "selected"; } ?>>6 - June</option>
									<option value="7" <?php if ($contact->birth_month == 7){ echo "selected"; } ?>>7 - July</option>
									<option value="8" <?php if ($contact->birth_month == 8){ echo "selected"; } ?>>8 - Aug</option>
									<option value="9" <?php if ($contact->birth_month == 9){ echo "selected"; } ?>>9 - Sept</option>
									<option value="10" <?php if ($contact->birth_month == 10){ echo "selected"; } ?>>10 - Oct</option>
									<option value="11" <?php if ($contact->birth_month == 11){ echo "selected"; } ?>>11 - Nov</option>
									<option value="12" <?php if ($contact->birth_month == 12){ echo "selected"; } ?>>12 - Dec</option>
								</select>
								
								<select id="birth_year" name="birth_year" class="dob">
									<option value="--" <?php if ($contact->birth_year == "--"){ echo "selected"; } ?>>Year</option>
									<?php 
										$year = date('Y'); 
										for ($i=1; $i<=100; $i++): 
									?>
									<option value="<?php echo $year; ?>" <?php if ($contact->birth_year == $year){ echo "selected"; } ?>><?php echo $year; ?></option>
									<?php 
										$year--;
										endfor; 
									?>
								</select>
							</p>
							
							<p>
								<label for="house_name">House Number/Name *</label>
								<input type="text" name="house_name" id="house_name" value="<?php echo $contact->house_name; ?>" />
							</p>
							
							<p>
								<label for="address_line_1">Address Line 1 *</label>
								<input type="text" name="address_line_1" id="address_line_1" value="<?php echo $contact->address_line_1; ?>" />
							</p>
							
							<p>
								<label for="address_line_2">Address Line 2</label>
								<input type="text" name="address_line_2" id="address_line_2" value="<?php echo $contact->address_line_2; ?>" />
							</p>
							
							<p>
								<label for="city">Town/City *</label>
								<input type="text" name="city" id="city" value="<?php echo $contact->city; ?>" />
							</p>
							
							<p>
								<label for="county">County *</label>
								<input type="text" name="county" id="county" value="<?php echo $contact->county; ?>" />
							</p>
							
							<p>
								<label for="post_code">Postcode *</label>
								<input type="text" name="post_code" id="post_code" value="<?php echo $contact->post_code; ?>" />
							</p>
							
							<p>
								<label for="daytime_number">Daytime Telephone Number *</label>
								<input type="text" name="daytime_number" id="daytime_number" value="<?php echo $contact->daytime_number; ?>" />
							</p>
							
							<p>
								<label for="mobile_number">Mobile Number</label>
								<input type="text" name="mobile_number" id="mobile_number" value="<?php echo $contact->mobile_number; ?>" />
							</p>
				
							<p>
								<label for="email_address">Email Address * <span>(We will send booking confirmation to this address)</span></label>
								<input type="text" name="email_address" id="email_address" value="<?php echo $contact->email_address; ?>" />
							</p>									
				
							<ul class="errors">
								<?php echo validation_errors('<li>', '</li>'); ?>
							</ul>
							
							<input type="submit" class="edit_contact_submit" id="submit" name="submit" value="Update booking contact" />
						</form>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js");