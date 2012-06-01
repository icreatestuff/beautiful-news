<?php 
$data['title'] = "Booking Payment ACS";
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
			<h1>Booking Payment ACS</h1>
			<p>Hello <b><?php echo $user['screen_name']; ?></b>.<br /> You will be taken away from the Bivouac website to authorise this payment with your bank. Once authorised, you will be redirected back to this site.</p>
		
			<div class="content" id="payment_3d_secure">
				<form name="Form" id="payment_3d_secure_form" action="<?php echo $FormAction; ?>" method="post">
					<input name="PaReq" type="hidden" value="<?php echo $PaREQ; ?>" />
					<input name="MD" type="hidden" value="<?php echo $CrossReference; ?>" />
					<input name="TermUrl" type="hidden" value="<?php echo $SiteSecureBaseURL; ?>booking/payment_3d_secure_result/<?php echo $this->session->userdata('booking_id') . '/' . $this->session->userdata('what_paying'); ?>" />
				</form>
			</div>	
		</section>
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>