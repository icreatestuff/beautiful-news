<?php 
$data['title'] = "Edit Booking";
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
			<h2>Bookings</h2>
			
			<section>
				<?php if (isset($voucher_message) && !empty($voucher_message)): ?>
					<h3 class="error"><?php echo $voucher_message; ?></h3>
				<?php endif; ?>
				<?php if ($bookings->num_rows > 0): ?>	
					<?php foreach($bookings->result() as $booking): ?>	
					<h1>Booking - <?php echo $booking->booking_ref; ?></h1>
					
					<div class="current-booking-details">
						<p>
							<b>Payment Status.</b> <?php echo $booking->payment_status; ?><br />
							<b>Total Price.</b>	&pound;<?php echo $booking->total_price; ?><br />
							<b>Amount Paid.</b> &pound;<?php echo $booking->amount_paid; ?>
						</p>
						
						<p>
							<b>Accommodation.</b>
							<?php 
								foreach ($accommodation_details as $accommodation): 
									if ($accommodation->num_rows() > 0):
										$accommodation_row = $accommodation->row();
							?>
							<?php echo "<br />" . $accommodation_row->name; ?>	
							<?php
									endif;
								endforeach;
							?>
						</p>
					</div>
						
					<?php echo form_open('admin/bookings/edit_booking/' . $booking->id, array('id' => 'booking-form', 'class' => 'admin-form')); ?>
						<input type="hidden" name="id" value="<?php echo $booking->id; ?>" />
					
						<p>If you are making a telephone booking then you will also need to process the customers<br /> 
						card payment through the Cardsave website. Go here <a href="https://mms.cardsaveonlinepayments.com/">https://mms.cardsaveonlinepayments.com/</a><br /> 
						and login using <b>Username:</b> merchant6912362 and <b>Password: </b> B!v0u4cBookings </p>
						<p>Once logged in go to "Payments" at the top and to "Transaction by card payment". Fill in the form.</p>
					
						<p>
							<label for="notes">Additional Notes for booking</label>
							<textarea name="notes" rows="4" cols="40" id="notes"><?php echo $booking->notes; ?></textarea>
						</p>
						
						<p>
							<label for="phone_booking">Is this a telephone booking?</label>
							<input type="checkbox" name="is_telephone_booking" id="phone_booking" value="true" <?php if ($booking->is_telephone_booking === "true") { echo "checked"; } ?>>
						</p>
					
						<p>
							<label for="payment_status">Change Booking Payment Status<br /><span>You should only need to change this if you have taken a telephone booking</span></label>
							<select name="payment_status" id="payment_status">
								<option value="" selected>--</option>
								<option value="unpaid">Unpaid</option>
								<option value="deposit">Deposit</option>
								<option value="fully paid">Fully Paid</option>
							</select>
						</p>			
					
						<label for="payment_status">Change accommodation on this booking by selecting below. <br /><span>If you select any accommodation from below and submit the form it will replace <br />all accommodation already on this booking and update the total price of the booking.<br />You have been warned.</span></label>
						<ul id="accommodation" class="accommodation-list">
							<?php echo $accommodation_list; ?>
						</ul>
						
						<p>
							<input type="submit" name="submit" value="Update Booking" id="submit">
							<?php echo anchor('admin/bookings/all_bookings/', 'Cancel', array('title' => 'View all Bookings')); ?>
						</p>
					</form>
					
					<ul class="errors">
						<?php echo validation_errors('<li>', '</li>'); ?>
					</ul>
					<?php endforeach; ?>
				<?php endif; ?>
				<p><?php echo anchor('admin/bookings/all_bookings/', 'View all bookings', array('title' => 'View all Bookings')); ?></p>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");