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
					<li><?php echo anchor('/account/settings', 'Account Settings', array('title' => 'Update your email address and password', 'class' => 'active')); ?></li>
					<li><?php echo anchor('/account/address', 'Account Address', array('title' => 'Update your address')); ?></li>
				</ul>
				
				<div class="account-section-content">
					<h2>Account Settings</h2>
					
					<?php echo form_open('account/settings', array('account-settings-form')); ?>
							<p>
								<label for="email_address">Email Address *</label>
								<input type="text" name="email_address" id="email_address" value="<?php echo $member->email_address; ?>" />
							</p>
							
							<p>
								<label for="password">New Password</label>
								<input type="password" name="password" id="password" value="" />
							</p>
							
							<p>
								<label for="password_confirmation">Password Confirmation</label>
								<input type="password" name="password_confirmation" id="password_confirmation" value="" />
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