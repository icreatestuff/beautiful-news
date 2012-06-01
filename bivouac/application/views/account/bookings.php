<?php 
$data['title'] = "Your bookings";
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
					<li><?php echo anchor('/account/bookings', 'Bookings', array('title' => 'View your bookings', 'class' => 'active')); ?></li>
					<li><?php echo anchor('/account/settings', 'Account Settings', array('title' => 'Update your email address and password')); ?></li>
					<li><?php echo anchor('/account/address', 'Account Address', array('title' => 'Update your address')); ?></li>
				</ul>
				
				<div class="account-section-content">
					<?php 
						$flashdata = $this->session->flashdata('member_update_message');
						if (isset($flashdata) && !empty($flashdata)): 
					?>
						<div class="user-message">
							<?php echo $flashdata; ?>
						</div>
					<?php endif; ?>
					<h2>Your Bookings</h2>
					<?php if ($bookings->num_rows() > 0): ?>
						<table class="account-table">
							<tbody>
								<?php foreach ($bookings->result() as $booking): ?>
									<tr>
										<td><?php echo $booking->booking_ref; ?></td>
										<td><?php echo date('l dS M Y', strtotime($booking->start_date)); ?></td>
										<td><?php echo date('l dS M Y', strtotime($booking->end_date)); ?></td>
										<td>&pound;<?php echo money_format('%i', $booking->total_price); ?></td>
										<td><?php echo $booking->payment_status; ?></td>
										<td>
											<?php if ($booking->payment_status === "deposit")
												{
													$remaining = $booking->total_price - $booking->amount_paid;
													echo anchor('/booking/payment/' . $booking->id, 'Pay &pound;' . $remaining . ' balance', array('title' => 'Pay booking balance', 'class' => 'pay-balance')) . "<br />";
												}
												
												echo anchor('/account/booking_overview/' . $booking->id, 'View full booking details', array('title' => 'View full booking details')); 
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
							<thead>
								<tr>
									<th>Booking Ref No.</th>
									<th>Arrival Date</th>
									<th>Departure Date</th>
									<th>Total Price</th>
									<th>Status</th>
									<th></th>
								</tr>
							</thead>
						</table>
					<?php else: ?>			
						<p>You haven't made any bookings with us yet! Go on, treat yourself to a <?php echo anchor('/booking/index', 'luxurious break', array('title' => 'Book a holiday with us')); ?>.	
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>