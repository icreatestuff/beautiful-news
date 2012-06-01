<?php 
$data['title'] = "Account Settings";
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
					<li><?php echo anchor('/account/bookings', 'Bookings', array('title' => 'View your bookings')); ?></li>
					<li><?php echo anchor('/account/settings', 'Account Settings', array('title' => 'Update your email address and password')); ?></li>
					<li><?php echo anchor('/account/address', 'Account Address', array('title' => 'Update your address', 'class' => 'active')); ?></li>
				</ul>
				
				<div class="account-section-content">
					<h2>Address Details</h2>
					
					<?php echo form_open('account/address', array('account-address-form')); ?>
							<p>
								<label for="house_name">House Number/Name *</label>
								<input type="text" name="house_name" id="house_name" value="<?php echo $address->house_name; ?>" />
							</p>
							
							<p>
								<label for="address_line_1">Address Line 1 *</label>
								<input type="text" name="address_line_1" id="address_line_1" value="<?php echo $address->address_line_1; ?>" />
							</p>
							
							<p>
								<label for="address_line_2">Address Line 2</label>
								<input type="text" name="address_line_2" id="address_line_2" value="<?php echo $address->address_line_2; ?>" />
							</p>
							
							<p>
								<label for="city">Town/City *</label>
								<input type="text" name="city" id="city" value="<?php echo $address->city; ?>" />
							</p>
							
							<p>
								<label for="county">County *</label>
								<input type="text" name="county" id="county" value="<?php echo $address->county; ?>" />
							</p>
							
							<p>
								<label for="post_code">Postcode *</label>
								<input type="text" name="post_code" id="post_code" value="<?php echo $address->post_code; ?>" />
							</p>
							
							<ul class="errors">
								<?php echo validation_errors('<li>', '</li>'); ?>
							</ul>
							
							<input type="submit" name="submit" id="submit" value="Update settings" />
						
					<?php echo form_close(); ?>
				</div>
			</div>
		</section>
	</div>		
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>