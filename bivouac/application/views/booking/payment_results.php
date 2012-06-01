<?php 
$data['title'] = "Booking Payment Results";
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
			
			<h1>Booking Payment Results</h1>
			<p>Hello <b><?php echo $user['screen_name']; ?></b>.</p>
		
			<div class="content" id="payment_results">
				<div class="<?php echo $this->session->flashdata('message_type'); ?>">
					<div class="TransactionResultsItem">
						<?php if ($this->session->flashdata('message_type') == "success"): ?>
							<h2>Thank you for booking with Bivouac.</h2>
							<p><?php echo $this->session->flashdata('user_message'); ?></p>
						<?php else: ?>
							<h2>There has been an error with your card payment.</h2>
							<p>Further details can be seen below:<br /><b></b></p>
							<p><?php echo $this->session->flashdata('user_message'); ?></p>
							<?php echo anchor('/booking/payment/' . $this->uri->segment(3)  , 'Return to payment page and try again', array('title' => 'Return to payment page')); ?>
						<?php endif; ?>
					</div>
				<?php
					if (isset($duplicate_transaction) != true)
					{
						$duplicate_transaction = false;							
					}
					
					if ($duplicate_transaction == true)
					{
				?>
						<p><i>A duplicate transaction means that a transaction with these details has already been processed by the payment provider.</i></p>
				<?php
					}
				?>
				</div>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>