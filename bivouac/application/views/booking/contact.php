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

			<div class="content clearfix" id="contact">
				<?php 
					// Is user logged in already?
					$is_logged_in = $this->session->userdata('is_logged_in');
					if (isset($is_logged_in) && $is_logged_in === TRUE):
				?>
		
				<div class="col415">
					<h2>Use saved details</h2>
					<p>If you would like to use your saved contact details just tick the box and click 'continue', else fill in the contact form on the right.</p>
					
					<?php echo form_open('booking/use_saved_contact'); ?>
						<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id; ?>" />
						<p>
							<label for="use_saved_details">Use saved contact details?</label> 
							<input type="checkbox" name="use_saved_details" id="use_saved_details" value="y" />
						</p>  
						<p>
							<input type="submit" name="submit" id="login-submit" value="Continue to booking Overview" class="submit" /> 
						</p>
					</form>		
				</div>
				
				<?php else: ?>
					<div class="col415">
						<h2>Sign in to use information attached to your account</h2>
						<p>If you have made a booking in the past, you can log in and use the contact information we have saved for you.</p>
						
						<?php echo form_open('booking/contact_login', array('id' => 'login_form')); ?>
							<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id; ?>" />
							<p>
								<label for="username">Username/Email Address</label> 
								<input type="text" name="username" id="username" />
							</p>  
							<p>
								<label for="password">Password</label> 
								<input type="password" name="password" id="password" />
							</p> 
							<p>
								<input type="submit" name="submit" id="login-submit" value="Login" class="submit" /> 
							</p>
							<p><a href="#" title="Forgot your password?" id="forgot_password_link">Forgot your password?</a></p>
							
							<?php 
								$flashdata = $this->session->flashdata('user_message');
								if (isset($flashdata) && !empty($flashdata)): 
							?>
							<div class="user-message <?php echo $this->session->flashdata('message_type'); ?>">
								<?php echo $flashdata; ?>
							</div>
							<?php endif; ?>
						</form>
						
						<?php echo form_open('booking/forgot_password', array('id' => 'forgot_password_form')); ?>
							<p>
								<label for="email_address">Please enter the email address associated with your account</label>
								<input type="text" name="email_address" id="email_address" value="" />
							</p>
							
							<input type="submit" name="submit" id="submit" value="Reset Password" />
						</form>
					</div>
				<?php endif; ?>
				
				<div class="col415">
					<h2>Please fill in the details for the primary booking contact</h2>
				
					<?php echo form_open('booking/contact/' . $booking_id, array('id' => 'contact-booking-form', 'class' => 'booking-form')); ?>
						<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id; ?>" />			
						
						<p>					
							<label for="title">Title *</label>
							<select id="title" name="title">
								<option value="--">--</option>
								<option value="Mr">Mr</option>
								<option value="Mrs">Mrs</option>
								<option value="Miss">Miss</option>
								<option value="Ms">Ms</option>
							</select>
						</p>
						
						<p>
							<label for="first_name">First Name *</label>
							<input type="text" name="first_name" id="first_name" value="<?php echo set_value('first_name'); ?>" />
						</p>
						
						<p>
							<label for="last_name">Surname *</label>
							<input type="text" name="last_name" id="last_name" value="<?php echo set_value('last_name'); ?>" />
						</p>
						
						<p>
							<label for="birth_day">Date of Birth *</label>
							<select id="birth_day" name="birth_day" class="dob">
								<option value="--">Day</option>
								<?php for ($i=1; $i<=31; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							
							<select id="birth_month" name="birth_month" class="dob">
								<option value="--">Month</option>
								<option value="1">1 - Jan</option>
								<option value="2">2 - Feb</option>
								<option value="3">3 - Mar</option>
								<option value="4">4 - Apr</option>
								<option value="5">5 - May</option>
								<option value="6">6 - June</option>
								<option value="7">7 - July</option>
								<option value="8">8 - Aug</option>
								<option value="9">9 - Sept</option>
								<option value="10">10 - Oct</option>
								<option value="11">11 - Nov</option>
								<option value="12">12 - Dec</option>
							</select>
							
							<select id="birth_year" name="birth_year" class="dob">
								<option value="--">Year</option>
								<?php 
									$year = date('Y'); 
									for ($i=1; $i<=100; $i++): 
								?>
								<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
								<?php 
									$year--;
									endfor; 
								?>
							</select>
						</p>
						
						<p>
							<label for="house_name">House Number/Name *</label>
							<input type="text" name="house_name" id="house_name" value="<?php echo set_value('house_name'); ?>" />
						</p>
						
						<p>
							<label for="address_line_1">Address Line 1 *</label>
							<input type="text" name="address_line_1" id="address_line_1" value="<?php echo set_value('address_line_1'); ?>" />
						</p>
						
						<p>
							<label for="address_line_2">Address Line 2</label>
							<input type="text" name="address_line_2" id="address_line_2" value="<?php echo set_value('address_line_2'); ?>" />
						</p>
						
						<p>
							<label for="city">Town/City *</label>
							<input type="text" name="city" id="city" value="<?php echo set_value('city'); ?>" />
						</p>
						
						<p>
							<label for="county">County *</label>
							<input type="text" name="county" id="county" value="<?php echo set_value('county'); ?>" />
						</p>
						
						<p>
							<label for="post_code">Postcode *</label>
							<input type="text" name="post_code" id="post_code" value="<?php echo set_value('post_code'); ?>" />
						</p>
						
						<p>
							<label for="daytime_number">Daytime Telephone Number *</label>
							<input type="text" name="daytime_number" id="daytime_number" value="<?php echo set_value('daytime_number'); ?>" />
						</p>
						
						<p>
							<label for="mobile_number">Mobile Number</label>
							<input type="text" name="mobile_number" id="mobile_number" value="<?php echo set_value('mobile_number'); ?>" />
						</p>
			
						<p>
							<label for="email_address">Email Address * <span>(We will send booking confirmation to this address)</span></label>
							<input type="text" name="email_address" id="email_address" value="<?php echo set_value('email_address'); ?>" />
						</p>	
						
						<p>
							<label for="newsletter_registration">Would you like to receive our email newsletters?</label>
							<input type="checkbox" name="newsletter_registration" id="newsletter_registration" value="y" />
						</p>
						
						<p>
							<label for="terms_and_conditions">I have read and agree to the <a href="#terms-lightbox" id="terms">terms and conditions</a><span>You must accept the terms and conditions to proceed</span></label>
							<input type="checkbox" name="terms_and_conditions" id="terms_and_conditions" value="y" />
						</p>	
						
						<hr />
						<p>To be able to manage and refer back to your booking in the future you will need to enter a password.</p>
						<p>
							<label for="password">Password</label>
							<input type="password" name="password" id="password" value="" />
						</p>
						
						<p>
							<label for="password_confirmation">Password confirmation (please re-enter password)</label>
							<input type="password" name="password_confirmation" id="password_confirmation" value="" />
						</p>								
			
						<ul class="errors">
							<?php echo validation_errors('<li>', '</li>'); ?>
						</ul>
						
						<input type="submit" id="submit" name="submit" value="Continue to booking overview" />
					</form>
				</div>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<div id="terms-lightbox">
	<h2>Terms &amp; Conditions</h2>
	<p>Bivouac would like to invite all their guests to enjoy a family friendly experience with us and to this end we ask guests to respect the site, the surrounding countryside and each other whilst staying with us.</p>
	<ol>
		<li><b>1. Site</b>
			<ol>
				<li>Your booking at Bivouac includes use of your nominated accommodation along with use of the public areas, woodland walks and parking areas. Please see site map for further details. Please do not cross the site boundary unless you know you have permission to do so.  Some parts of the site have footpaths over the boundary, however others adjoin private land which accommodates vulnerable crops, livestock and protected rare species of wildlife.</li>
			</ol>
		</li>
	
		<li><b>2. Booking</b>
			<ol>
				<li>All Bookings are confirmed only after the booking form has been returned and the deposit paid. Bivouac reserves the right to cancel your booking if payment conditions are not upheld. Receipt of your booking deposit confirms your acceptance of all terms and conditions.</li>
		
				<li>Your accommodation will be ready on the day of arrival by 3pm, departure time is by 10am on your day of departure.</li>
		 				
				<li>Groups of 6 people can be booked online for beds in the Bunk Barn. For groups of more than 6 people in the Bunk Barn please telephone Bivouac staff directly.</li>
				
				<li>Only three Woodland Shacks or Meadow Yurts can be booked online in one single booking. For a booking of 4 or more Woodland Shacks or Meadow Yurts please telephone Bivouac staff directly.</li>
				
				<li>When booking Woodland Shacks and Meadow Yurts: A 30% non-refundable deposit is payable to secure the booking. The final balance is payable 6 weeks prior to arrival at the latest.</li>
				
				<li>When booking beds in the Bunk Barn: Payment is required in full for all beds for the duration of the stay at the time of booking. Receipt of payment in full secures the booking.</li>
			</ol>
		</li>
					
		<li><b>3. Cancellation</b>
			<ol>		
				<li>When cancelling bookings in the Woodland Shacks or Meadow Yurts, the deposit of 30% is not returnable. Any cancellation within the last 2-6 weeks prior to the date of stay will incur a charge of 75% of the total cost. Any cancellation within 2 weeks prior to the date of stay will incur a charge of 100% of total cost.</li>
				
				<li>Cancellations must be sent in writing. Should Bivouac not be informed of your cancellation in writing, Bivouac will look to recover any loss of income in advance of your stay.</li>
				
				<li>When cancelling bookings in the Bunk Barn, the advance payment is fully refundable if more than two weeks notice of the cancellation is given. No refund will be given if bookings in the Bunk Barn are cancelled within two weeks of the commencement of the stay.</li>
			</ol>
		</li>
		
		<li><b>4. Conduct</b>
			<ol>
				<li>The named person(s) booking the accommodation is responsible for the behaviour and actions of their guests while at The Bivouac.</li>
				
				<li>Guests may be asked to leave the premises if their behaviour is threatening, aggressive, anti-social, or damaging in any way to the other residents or to the premises. In this circumstance your full cooperation is expected.</li>
			</ol>
		</li>
		
		<li><b>5. Vehicles</b>
			<ol>
				<li>Visitors staying in a Woodland Shack are required to park in the top woodland car park. Meadow Yurt guests and Bunk Barn guests are required to park in the main car park at the farm. </li>
				
				<li>Day visitors to the site (including those visiting accommodation guests) are required to park at the main car park next to the farm.</li>
				
				<li>There is a strict 5 mph speed limit whilst on site. Please observe this at all times.</li>
				
				<li>For the comfort of all guests Bivouac ask that the site be kept quiet after 10.30pm.</li>
			</ol>
		</li>
		
		<li><b>6. Occupancy</b>
			<ol>
				<li>The number of people staying in any accommodation must at no time exceed the number stated and agreed in the booking .The accommodation must not be sub-let.</li>
				
				<li>The owners of Bivouac reserve the right to enter the accommodation at any reasonable time, and with reasonable prior notice if any urgent need were to arise (e.g. for health and safety reasons or urgent repair).</li>
			</ol>
		</li>
		
		<li><b>7. Care for the Accommodation</b>
			<ol>
				<li>Your accommodation will be prepared for your arrival and an inventory of all supplied items and equipment will be provided. Bivouac expect everything to be left as you found it.</li>
				
				<li>In the case of any accidents on the premises these must be reported immediately to a member of Bivouac staff at the main reception desk.</li>
				
				<li>The named person(s) booking the accommodation will be held responsible for paying for any loss or damage to any part of the Bivouac premises, or to any fixtures, fittings and equipment which are caused by any member of the party.</li>
				
				<li>If an exceptional level of cleaning is required after use of any of these facilities the cost will be charged to the named person.</li>
				
				<li>We ask that you do not bring muddy boots into the accommodation. Welly tree’s are provided outside your accommodation for your use.</li>
				
				<li>A deposit swipe of a credit card may be taken as insurance against any damages. This will only be processed if Bivouac needs to claim back costs against damages. This will be completed within 14 days of your stay.</li>
				
				<li>All bed linen and tea towels are provided. These items must remain inside your accommodation at all times.</li>
				
				<li>It is the responsibility of all guests to return any items removed from any accommodation unit to its original place by the time of departure (this includes cutlery, crockery, utensils etc.)</li>
				
				<li>Guests must return all furniture within the accommodation to its original position by the time of departure.</li>
				
				<li>Please ensure that all waste is taken to the designated area for disposal and please use the recycling facilities. Bin liners will be provided but please ask if more are required.</li>
			</ol>
		</li>
		
		<li><b>8. Personal Belongings</b>
			<ol>
				<li>Bivouac will not be held responsible for damage to any personal property brought onto the premises.</li>
				
				<li>Lockers are provided in the Bunk Barn.  These require a padlock; guests are welcome to use their own padlock or purchase one from the Bivouac shop.  Bivouac reserve the right to forcibly remove and dispose of any padlocks that are left on lockers after guests have departed.</li>
			</ol>
		</li>
		
		<li><b>9. Fires &amp; Smoking</b>
			<ol>
				<li><b>Bivouac take a firm view on managing fires and smoking and ask that guests are particularly vigilant in this area.  The site contains a lot of trees, hand crafted wooden and canvas accommodation and we are alongside open moorland covered in peat and heather. Even the smallest of informal fires can quickly get out of hand.</b></li>
				
				<li><b>There will be no fires or barbecues other than in the designated areas and with the equipment provided, failure to observe these requirements may result in your party being asked to vacate the site immediately.</b></li>
				
				<li>Only logs supplied by Bivouac may be used in the stoves and fire pits.</li>
				
				<li>Stoves provided in the accommodation are suitable for burning wood fuel only.  Coal and other processed fuel types may result in irreparable damage to the stove and accommodation unit.</li>
				
				<li>All guests that light fires are responsible for managing them safely and ensuring that they are extinguished after use.</li>
				
				<li>Smoking is not permitted anywhere within Bivouac buildings and is only permitted in the designated fire pit areas and agreed smoking spots.</li>
				
				<li>For fire regulations we are required to keep a log of all guests who will be residing at the Bivouac. The named person(s) must be given to a member of Bivouac staff on arrival.</li>
			</ol>
		</li>
		
		<li><b>10. Dogs</b>
			<ol>
				<li>Dogs must be booked in advance and will only be permitted into specific dog-friendly Woodland Shacks and Meadow Yurts.  Dogs that have not been booked into these units will not be permitted to stay on site.  This is taken extremely seriously as some accommodation units are guaranteed to have not accommodated pets and are available for guests with allergies and asthma etc.</li>
				
				<li>One well behaved dog is permitted in designated Woodland Shacks and Meadow Yurts but we regret that dogs are not  allowed in the camping barns. A second dog might be permitted with permission of Bivouac; permission must be sought in advance of your stay.</li>
				
				<li>Dogs must be kept on a lead whilst on site.</li>
				
				<li>Please do not allow dogs to foul the site; all dog faeces must be cleaned up and deposited in the bins provided.</li>
				
				<li>Dogs must be quiet and not cause disturbance to others. Failure to adhere to these terms and conditions will result in the guest being asked to leave the site.</li>
			</ol>
		</li>
		
		<li><b>11. Due Care and Attention</b>
			<ol>
				<li>Whilst we encourage guests to enjoy the public areas and woodlands, please note that care must be taken; walkways and stairs maybe slippery particularly when wet, footpaths and tracks maybe uneven with tree roots and rocks.</li>
				
				<li>Children at The Bivouac  must be supervised by a responsible adult at all times.</li>
				
				<li>Some areas of the Bivouac will carry signs detailing rules specific to their location; for example the woods, the accommodation and play areas.  Guests must observe all site notices regarding the use of the Meadow Yurts, Woodland Shacks, the public areas within  Bivouac and also all rules regarding the enjoyment of the Druids Temple and the woodland surrounding it.</li>
			</ol>
		</li>
		
		<li><b>12. Lost Property</b>
			<ol>
				<li>We have a 7 day lost and found policy which means we can only keep an item found for that amount of time.</li>
				
				<li>It is the guests’ responsibility to check for all personal belongings before departure from the Bivouac.</li>
				
				<li>Any items found after guests have departed can to be sent to the guest at their expense. Any items sent by post or courier must be paid for in advance and it is the responsibility of the guest to select a suitable carrier and appropriate insurance.</li>
			</ol>
		</li>
	</ol>
	
	<p>Bivouac will not be responsible for the failure to provide facilities contracted for in the event of it being prevented from doing so as a result of "Force Majeure" or any other cause beyond its control.  This includes industrial disputes, orders or regulations issued by Central Government, Riots, Floods or Fire Epidemics. The Bivouac will not be responsible for any loss or damage or costs as a result. </p>
</div>
<?php $this->load->view("_includes/frontend/js");