<?php 
$data['title'] = "Book a holiday with us!";
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
			
			<h1>Tell us when you would like your holiday to start and for how long.</h1>	
			<p>If you need to book more people than available through the form please make your booking over the phone by calling us on 01765 53 50 20</p>
	
			<?php echo form_open('booking/index', array('id' => 'booking-form', 'class' => 'booking-form')); ?>
				<input type="hidden" name="site_id" id="site_id" value="1" />
				<input type="hidden" name="start_date" id="start_date" value="" />
				<input type="hidden" name="total_price" id="total_price" value="" />
			
				<div class="booking-form-primary-fields clearfix">
					<div class="col217">
						<div class="calendar-container">
							<div id="calendar"></div>
							
							<div class="calendar-key">
								<p><span class="key blue"></span> Public Holidays</p>
							</div>
						</div>
						<h3>Once you have made your selections, accommodation will automatically load below. Please scroll down.</h3>
					</div>
					
					
					<div class="secondary-form-data">
						<p id="duration-container">
							<label for="duration">How many nights do you wish to stay?</label>
							<select name="duration" id="duration">
								<option value="">Please select an arrival date from the calendar</option>
							</select>
						</p>
						
						<p>
							<select name="adults" id="adults">
								<?php for ($i=1; $i<=10; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
								
								<?php
									// If user logged in is an admin then show more adults
									if ($is_admin)
									{
										for ($i=11; $i<=80; $i++):
								?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php
										endfor;
									}
								?>
							</select>
							<label for="adults">Adults will be staying</label>
						</p>
						
						<p>
							<select name="children" id="children">
								<?php for ($i=0; $i<=6; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
								
								<?php
									// If user logged in is an admin then show more adults
									if ($is_admin)
									{
										for ($i=7; $i<=80; $i++):
								?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php
										endfor;
									}
								?>
							</select>
							<label for="children">4-17 year olds will be staying</label>
						</p>
						
						<p>
							<select name="babies" id="babies">
								<?php for ($i=0; $i<=4; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
								
								<?php
									// If user logged in is an admin then show more adults
									if ($is_admin)
									{
										for ($i=5; $i<=80; $i++):
								?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php
										endfor;
									}
								?>
							</select>
							<label for="babies">0-3 year olds will be staying</label>
						</p>
						
						<input type="hidden" name="dogs" value="0">
						<!--
						<p>
							<select name="dogs" id="dogs">
								<?php for ($i=0; $i<=2; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<label for="dogs">Dogs coming</label>
						</p>
						-->
						
						<p>
							<label for="multiple-units">Do you want to book multiple accommodation units?</label>
							<select name="multiple-units" id="multiple-units">
								<option value="no">No</option>
								<option value="yes">Yes</option>
							</select>
						</p>
					</div>
					
					<div class="booking-key-info">
						<h2>Key Information</h2>
						<ul>
							<li>Book single nights in the bunk barn <?php echo anchor('/accommodation/unit/11', 'from here', array('title' => 'Bunk Barn detail page')); ?></li>
							<li>Woodland Shacks sleep up to 7</li>
							<li>Meadow Yurts sleep up to 5</li>
							<li>Check-in from 2pm; your accommodation will be available at 3pm.</li>
							<li>Departure time is at 10am on the last day of your stay.</li>
							<li><a href="#accessibility-lightbox" id="accessibility" title="Bivouac Site Accessibility"><span class="accessibility-icon"></span> Site accessibility</a></li>
							<li>Any Questions? Please call HQ on: 01765 53 50 20</li>
						</ul>
					</div>
				</div>					
					
				<div class="price-container">
					<p class="guests-warning"><strong>You need to select more accommodation to cover the number of guests coming</strong></p>
					<p><strong>Total Booking Price:</strong> &pound;<span class="total-price">0</span></p>
				</div>
				
				<ul id="accommodation" class="accommodation-list clearfix"></ul>
				
				<div id="no-units">
					<h2>Sorry we do not have any accommodation available for the dates and options you have selected.</h2>
				</div>
				
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>
				
				<div class="price-container">
					<p class="guests-warning"><strong>You need to select more accommodation to cover the number of guests coming</strong></p>
					<p><strong>Total Booking Price:</strong> &pound;<span class="total-price">0</span></p>
				</div>
				
				<p>
					<input type="submit" name="booking_submit" id="booking_submit" value="Book these dates" />
				</p>
				
				<p class="disclaimer"><span class="comodo-icon"></span> If any unit(s) selected become unavailable you may be allocated another unit. We will inform you of any changes that may need to be made.</p>
			</form>
		</section>	
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<div id="accommodation-lightbox"></div>
<div id="accessibility-lightbox">
	<h2>Site Accessibilty</h2>
	<ol>
		<li><b>1. The community space</b>
			<ol>
				<li>We have given a great deal of thought to disabled visitors when designing the communal areas.</li>
				<li>Disabled parking spaces</li>
				<li>Compliant ramps to allow wheelchair access to the main entrance, shop, cafe, seating terrace and fully equipped WC with assistance alarm. All doorways between these areas have flat sills for wheelchairs to roll-over.  Hopefully wheelchair users will be able to freely enjoy easy access to all the main areas (and therefore events) in the community space. I hope we’ll pleasantly surprise visitors with how wheelchair friendly we are. – especially given our location.</li>
				<li>One shower is a fully disabled equipped/compliant wet room.</li>
				<li>The whole communal area (except camping loft) is ambulant accessible. This means that people with some mobility on foot will be fine, i.e. for people with a physical impairment in their legs but are not limited to just a wheelchair for mobility.  In practice this means that where steps are the only option for access, each step is not higher than 160mm. (where normal steps can be up to 210mm).</li>
			</ol>
		</li>
		
		<li><b>2. Accommodation</b>
			<ol>
				<li>  It would be difficult for a wheelchair user to get to a yurt; the levels are probably OK but guests are required to cross sections of field.</li>
				<li>Some Yurts are ambulant accessible, but guests should be aware that the yurts are approx 150m from disabled car parking spaces.</li>
				<li>The shacks have not been designed with disabled users in mind so access depends on the type of disability. We may build one next to the car park one day with some improvements for disabled users, but nothing on the cards just yet.</li>
	</ol>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>