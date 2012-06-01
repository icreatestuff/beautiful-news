<?php 
$data['title'] = "Accommodation available for the arrival date and duration selected!";
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
	
		<section id="results">
			<h1>Accommodation available</h1>
			
			<?php echo form_open('results/process', array('id' => 'results-form', 'class' => 'results-form')); ?>
				<input type="hidden" name="site_id" id="site_id" value="<?php echo $site_id; ?>" />
				<input type="hidden" name="start_date" id="start_date" value="<?php echo $start_date; ?>" />
				<input type="hidden" name="duration" id="duration" value="<?php echo $duration; ?>" />
				<input type="hidden" name="adults" id="adults" value="<?php echo $adults; ?>" />
				<input type="hidden" name="children" id="children" value="<?php echo $children; ?>" />
				<input type="hidden" name="babies" id="babies" value="<?php echo $babies; ?>" />
				
				<input type="hidden" name="total_price" id="total_price" value="" />
				
				<p>
					<label for="multiple-units">Would you like to book multiple accommodation units?</label>
					<select name="multiple-units" id="multiple-units">
						<option value="no">No</option>
						<option value="yes">Yes</option>
					</select>
				</p>
			
				<div class="price-container">
					<p class="guests-warning"><strong>You need to select more accommodation to cover the number of guests coming</strong></p>
					<p><strong>Total Booking Price:</strong> &pound;<span class="total-price">0</span></p>
				</div>	
				<ul id="accommodation" class="accommodation-list">
					<?php echo $accommodation_list; ?>
				</ul>
				
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>
				<div class="price-container">
					<p class="guests-warning"><strong>You need to select more accommodation to cover the number of guests coming</strong></p>
					<p><strong>Total Booking Price:</strong> &pound;<span class="total-price">0</span></p>
				</div>
				<p>
					<input type="submit" name="results_submit" id="results_submit" value="Book your holiday!" />
				</p>
				
				<p>
					<?php echo anchor('/booking', 'Search again', array('title' => 'Search again', 'id' => 'search-again')); ?>
				</p>
				
				<p>If any unit(s) selected become unavailable you may be allocated another unit. We will inform you of any changes that may need to be made.</p>
			</form>
		</section>
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<div id="accommodation-lightbox"></div>
<?php $this->load->view("_includes/frontend/js"); ?>