<?php 
$data['title'] = "Edit Wedding Booking";
$data['location'] = "weddings";
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
			<h2>Edit Wedding Booking</h2>
			<section>
				<h1>Edit Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/weddings/edit_wedding/' . $this->uri->segment(4), array('id' => 'wedding-form', 'class' => 'admin-form')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				
				<?php if ($booking_data->num_rows() > 0): ?>
				<?php $booking = $booking_data->row(); ?>
				<input type="hidden" name="start_date" id="start_date" value="<?php echo $booking->start_date; ?>" />
				
				<p>Please select the weekend of the wedding (click on the friday)</p>
				<div id="calendar"></div>
				
				<p>
					<label for="notes">Wedding Package and other notes</label>
					<textarea name="notes" id="notes" cols="40" rows="6"><?php echo $booking->notes; ?></textarea>
				</p>
				
				<p>
					<label for="total_price">Total Price</label>
					<input type="text" name="total_price" id="total_price" value="<?php echo $booking->total_price; ?>" />
				</p>
				
				<p>
					<label for="amount_paid">Amount Paid (e.g. deposit)</label>
					<input type="text" name="amount_paid" id="amount_paid" value="<?php echo $booking->amount_paid; ?>" />
				</p>
				<?php endif; ?>
				
				<?php if ($contact_data->num_rows() > 0): ?>
				<?php $contact = $contact_data->row(); ?>
				<h3>Wedding Contact Details</h3>
				<p>
					<label for="title">Title</label>
					<select name="title" id="title">
						<option value="--" <?php if ($contact->title === "--") { echo "selected"; } ?>>--</option>
						<option value="Mr" <?php if ($contact->title === "Mr") { echo "selected"; } ?>>Mr</option>
						<option value="Mrs" <?php if ($contact->title === "Mrs") { echo "selected"; } ?>>Mrs</option>
						<option value="Miss" <?php if ($contact->title === "Miss") { echo "selected"; } ?>>Miss</option>
						<option value="Ms" <?php if ($contact->title === "Ms") { echo "selected"; } ?>>Ms</option>
					</select>
				</p>
				<p>
					<label for="first_name">First Name</label>
					<input type="text" name="first_name" id="first_name" value="<?php echo $contact->first_name; ?>" />
				</p>
				<p>
					<label for="last_name">Surname</label>
					<input type="text" name="last_name" id="last_name" value="<?php echo $contact->last_name; ?>" />
				</p>
				<p>
					<label for="birth_day">Date of Birth</label>
					<select id="birth_day" name="birth_day" class="dob">
						<option value="--">Day</option>
						<?php for ($i=1; $i<=31; $i++): ?>
						<option value="<?php echo $i; ?>" <?php if ($contact->birth_day == $i) { echo "selected"; } ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					
					<select id="birth_month" name="birth_month" class="dob">
						<option value="--">Month</option>
						<?php for ($i=1; $i<=12; $i++): ?>
						<option value="<?php echo $i; ?>" <?php if ($contact->birth_month == $i) { echo "selected"; } ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					
					<select id="birth_year" name="birth_year" class="dob">
						<option value="--">Year</option>
						<?php 
							$year = date('Y'); 
							for ($i=1; $i<=100; $i++): 
						?>
						<option value="<?php echo $year; ?>" <?php if ($contact->birth_year == $year) { echo "selected"; } ?>><?php echo $year; ?></option>
						<?php 
							$year--;
							endfor; 
						?>
					</select>
				</p>
				
				<p>
					<label for="house_name">House Number/Name</label>
					<input type="text" name="house_name" id="house_name" value="<?php echo $contact->house_name; ?>" />
				</p>
				
				<p>
					<label for="address_line_1">Address Line 1</label>
					<input type="text" name="address_line_1" id="address_line_1" value="<?php echo $contact->address_line_1; ?>" />
				</p>
				
				<p>
					<label for="address_line_2">Address Line 2</label>
					<input type="text" name="address_line_2" id="address_line_2" value="<?php echo $contact->address_line_2; ?>" />
				</p>
				
				<p>
					<label for="city">Town/City</label>
					<input type="text" name="city" id="city" value="<?php echo $contact->city; ?>" />
				</p>
				
				<p>
					<label for="county">County</label>
					<input type="text" name="county" id="county" value="<?php echo $contact->county; ?>" />
				</p>
				
				<p>
					<label for="post_code">Postcode</label>
					<input type="text" name="post_code" id="post_code" value="<?php echo $contact->post_code; ?>" />
				</p>
				
				<p>
					<label for="daytime_number">Daytime Telephone Number</label>
					<input type="text" name="daytime_number" id="daytime_number" value="<?php echo $contact->daytime_number; ?>" />
				</p>
				
				<p>
					<label for="mobile_number">Mobile Number</label>
					<input type="text" name="mobile_number" id="mobile_number" value="<?php echo $contact->mobile_number; ?>" />
				</p>
	
				<p>
					<label for="email_address">Email Address</label>
					<input type="text" name="email_address" id="email_address" value="<?php echo $contact->email_address; ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
				
				<p><?php echo anchor('admin/weddings', 'View all wedding bookings', array('title' => 'View all wedding bookings')); ?></p>
				
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");