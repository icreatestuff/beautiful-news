<?php 
$data['title'] = "Booking Payment";
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
				<li>Booking Overview</li>
				<li class="active">Payment</li>
				<li>Confirmation</li>
			</ol>
	
			<h1>Booking Payment</h1>
			<p>Hello <b><?php echo $user['screen_name']; ?></b>.<br />Please enter your payment details on our secure server to complete your booking. You will receive an email confirming your booking with us.</p>
	
			<?php if (isset($voucher_message) && !empty($voucher_message)): ?>
				<h2 class="error"><?php echo $voucher_message; ?></h2>
			<?php endif; ?>
			
			<h2>You are paying this much <b>&pound;<?php echo money_format('%i', ($price / 100)); ?></b></h2>
		
			<div class="content clearfix" id="payment">
				<?php echo form_open('booking/payment/' . $booking_id, array('id' => 'payment-booking-form', 'class' => 'booking-form')); ?>
		
					<?php
						$user_message = $this->session->flashdata('user_message');
						
						if (isset($user_message) && !empty($user_message))
						{
					?>
							<div class="user_message <?php echo $this->session->flashdata('message_type'); ?>">
								<?php echo $user_message; ?>	
							</div>
					<?php
						}
					?>
		
					<input type="hidden" name="FormMode" value="PAYMENT_FORM" />
					<input type="hidden" name="what_paying" value="<?php echo $what_paying; ?>" /> <!-- What the user selects to pay e.g. deposit, full amount or balance -->
					<input type="hidden" name="Amount" value="<?php echo $price; ?>" /> <!-- Amount in pence of booking, eigther full or deposit amount -->
					<input type="hidden" name="CurrencyISOCode" value="826" /> <!-- 826 is for GBP -->
					<input type="hidden" name="OrderID" value="<?php echo $booking_ref; ?>" /> <!-- This should be booking_ref -->
					<input type="hidden" name="OrderDescription" value="<?php echo $booking_description; ?>" /> <!-- e.g. Depost/Remaining/Full Payment for Booking Ref: booking_ref -->
					
					<!-- We wont use Address 3 or Address 4 so set them as empty and hidden -->
					<input type="hidden" name="Address3" value="" />
					<input type="hidden" name="Address4" value="" />
					
					<div class="col415 first-column">
						<h2>Address Card Registered at</h2>
					    <p>
							<label for="address1">Address Line 1 *</label>
							<input type="text" name="Address1" value="<?php echo set_value('Address1'); ?>" id="address1" />
					    </p>
					    <p>
							<label for="address2">Address Line 2</label>
							<input type="text" name="Address2" value="<?php echo set_value('Address2'); ?>" id="address2" />
					    </p>
					    <p>
							<label for="city">Town/City *</label>
							<input type="text" name="City" value="<?php echo set_value('City'); ?>" id="city" />
					    </p>
					    <p>
							<label for="state">State/County *</label>
							<input type="text" name="State" value="<?php echo set_value('State'); ?>" id="state" />
					    </p>
					    <p>
							<label for="postcode">Post Code *</label>
							<input type="text" name="PostCode" value="<?php echo set_value('PostCode'); ?>" id="postcode" />
					    </p>
					    
						<p>
							<label for="country">Country</label>
						     <select name="CountryISOCode" id="country">
								<option value="-1"></option>
								<?php
									$FirstZeroPriorityGroup = true;
									for ($i = 0; $i < $iclISOCountryList->getCount()-1; $i++):
										if ($iclISOCountryList->getAt($i)->getListPriority() == 0 && $FirstZeroPriorityGroup == true):
								?>
								<option value="-1">--------------------</option>
								<?php
											$FirstZeroPriorityGroup = false;
										endif;
								?>
								
								<option value="<?php echo $iclISOCountryList->getAt($i)->getISOCode(); ?>"><?php echo $iclISOCountryList->getAt($i)->getCountryName(); ?></option>
								<?php endfor; ?>
							</select>
						</p>
						
						<img src="/images/comodo-icon.png" width="111" height="57" alt="Comodo e-commerce certification" class="comodo-icon-payment">
					</div>		
		
					<div class="col415">
				    	<h2>Card Details</h2>
				    	
				    	<p>We accept the following cards</p>
				    	<ul class="cards-list clearfix">
				    		<li class="visa">Visa</li>
				    		<li class="visa-electron">Visa Electron</li>
				    		<li class="maestro">Maestro</li>
				    		<li class="solo">Solo</li>
				    		<li class="switch">Switch</li>
				    		<li class="mastercard">Mastercard</li>
				    	</ul>
				    	
					    <p>
							<label for="name_card">Name On Card *</label>
							<input type="text" name="CardName" value="<?php echo set_value('CardName'); ?>" id="name_card" />
					    </p>
					    <p>
							<label for="card_number">Card Number *</label>       
							<input type="text" name="CardNumber" value="<?php echo set_value('CardNumber'); ?>" id="card_number" />
					    </p>
						   
						 <?php
							$ThisYear = date("Y");
							$ThisYearPlusTen = $ThisYear + 10;
						?>   
					    <p>
							<label for="start_date">Start Date <br /><span>Not required if not shown on card</span></label>    
							<select name="StartDateMonth" id="start_date">
								<option></option>
							<?php
							for ($i = 1; $i <= 12; $i++)
							{
								$DisplayMonth = $i;
								if ($i < 10)
								{
									$DisplayMonth = "0" . $i;
								}
							?>
								<option><?php echo $DisplayMonth; ?></option>
							<?php
							}
							?>
							</select>
							/
							<select name="StartDateYear">
								<option></option>
							<?php
							for ($i = 2000; $i <= $ThisYear; $i++)
					   		{
					   			$ShortYear = substr($i, strlen($i)-2, 2);
							?>
								<option value="<?php echo $ShortYear; ?>"><?php echo $i; ?></option>
							<?php
							}
							?>
							</select>
						</p>
						   						
						<p>
							<label for="expiry_date">Expiry Date *</label>
							<select name="ExpiryDateMonth" id="expiry_date">
								<option></option>
							<?php
							for ($i = 1; $i <= 12; $i++)
							{
								$DisplayMonth = $i;
								if ($i < 10)
								{
									$DisplayMonth = "0" . $i;
								}
							?>
								<option><?php echo $DisplayMonth; ?></option>
							<?php
							}
							?>
							</select>
							/
							<select name="ExpiryDateYear">
								<option></option>
							<?php
							for ($i = $ThisYear; $i <= $ThisYearPlusTen; $i++)
							{
								$ShortYear = substr($i, strlen($i)-2, 2);
							?>
								<option value="<?php echo $ShortYear; ?>"><?php echo $i; ?></option>
							<?php
							}
							?>
							</select>
						</p>
						
					    <p>
							<label for="issue-number">Issue Number <br /><span>Not required if not shown on card</span></label>
							<input type="text" name="IssueNumber" value="" id="issue-number" />
					    </p>
					    <p>
							<label for="cv2">Security Code (CV2) *<br /><span>The last 3 digits on the reverse of the card</span></label>
							<input type="text" name="CV2" value="" id="cv2" />
					    </p>
					    
					    <p>
							<input type="submit" id="submit" value="Make Payment" />
						</p>
					</div>
		
					<ul class="errors">
						<?php echo validation_errors('<li>', '</li>'); ?>
					</ul>
			    </form>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>