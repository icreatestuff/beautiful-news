<?php 
$data['title'] = "How many people are coming on your holiday?";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="container" class="clearfix">
	<?php if ($this->session->userdata('is_logged_in') === TRUE): ?>
		<p class="user-logout"> 
			Hello <?php echo $this->session->userdata('screen_name'); ?> | <?php echo anchor('account/logout', 'Log out'); ?>
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
				<li class="active">When &amp; where</li>
				<li>Holiday Extras</li>
				<li>Contact Details</li>
				<li>Booking Overview</li>
				<li>Payment</li>
				<li>Confirmation</li>
			</ol>			
			
			<h1>How many people are coming on your holiday.</h1>	
			<p>Please select how many adults, children and babies will be a part of this booking.</p>
	
			<?php echo form_open('offers/guests', array('id' => 'booking-form', 'class' => 'booking-form')); ?>
				<div class="booking-form-primary-fields clearfix">
					<div class="secondary-form-data">
						<p>
							<select name="adults" id="adults">
								<?php for ($i=1; $i<=7; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<label for="adults">Adults will be staying</label>
						</p>
						
						<p>
							<select name="children" id="children">
								<?php for ($i=0; $i<=6; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<label for="children">4-17 year olds will be staying</label>
						</p>
						
						<p>
							<select name="babies" id="babies">
								<?php for ($i=0; $i<=2; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<label for="babies">0-3 year olds will be staying</label>
						</p>	
						
						<input type="submit" name="submit" id="submit" value="Add these guests" />
						
						<ul class="errors">
							<?php echo validation_errors('<li>', '</li>'); ?>
						</ul>
					</div>
					
					<div class="booking-key-info">
						<h2>Key Information</h2>
						<ul>
							<li>Woodland Shacks sleep up to 7</li>
							<li>Meadow Yurts sleep up to 5</li>
							<li>Check-in from 2pm; your accommodation will be available at 3pm.</li>
							<li>Departure time is at 10am on the last day of your stay.</li>
							<li>Any Questions? Please call HQ on: 01765 53 50 20</li>
						</ul>
					</div>
				</div>		
				
				<p class="disclaimer"><span class="comodo-icon"></span> If any unit(s) selected become unavailable you may be allocated another unit. We will inform you of any changes that may need to be made.</p>
			</form>
		</section>	
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<div id="accommodation-lightbox"></div>
<?php $this->load->view("_includes/frontend/js"); ?>