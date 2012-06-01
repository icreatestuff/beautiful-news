<?php 
$data['title'] = "Booking complete";
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
			<ol class="booking-flow-indicators clearfix">
				<li>When &amp; where</li>
				<li>Holiday Extras</li>
				<li>Contact Details</li>
				<li>Booking Overview</li>
				<li>Payment</li>
				<li class="active">Confirmation</li>
			</ol>
			
			<h1>Booking Complete!</h1>
			<p>Hello <b><?php echo $user['screen_name']; ?></b>.</p>
		
			<div class="content" id="payment_results">
				<p>This booking has been completed and fully paid for.<br />If you would like to view the booking details you can do so at any time by logging into <?php echo anchor('/account/bookings', 'your account', array('title' => 'Login to your account')); ?></p>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>