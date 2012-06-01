<?php 
$data['title'] = "See our accommodation";
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
	
		<div class="breadcrumbs clearfix">
			<a href="https://booking.thebivouac.co.uk" title="Book your holiday!">Booking</a> / Accommodation
		</div>
		
		<section>
			<h1>Our Accommodation</h1>
		
			<div id="body">
				<p>Listed below is all of our accommodation. Find the perfect space for your holiday or short break.</p>
			</div>
			
			<ul class="accommodation-list">
				<?php if ($query->num_rows() > 0): ?>
					<?php foreach ($query->result() as $row): ?>
						<li class="clearfix">
							<h3><a href="<?php echo base_url(); ?>accommodation/unit/<?php echo $row->id ?>" title="View full accommodation detail"><?php echo $row->name; ?></a></h3>
							<div class="accommodation-info">
							<?php if (!empty($row->photo_1)): ?>
								<?php echo size(base_url() . 'images/accommodation/' . $row->photo_1, 100); ?>
							<?php endif; ?>
								<p class="accommodation-description"><?php echo $row->description; ?><br /><br /><b>Sleeping Areas: </b><?php echo $row->bedrooms; ?> | <b>Sleeps: </b><?php echo $row->sleeps; ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</section>
	</div>	
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>