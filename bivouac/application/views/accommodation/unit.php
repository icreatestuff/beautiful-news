<?php 
$data['title'] = $accommodation->name . " | Bivouac Accommodation";
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
	
		<div class="breadcrumbs clearfix">
			<a href="https://booking.thebivouac.co.uk" title="Book your holiday!">Booking</a> / <a href="https://booking.thebivouac.co.uk/accommodation" title="View all our accommodation">Accommodation</a> / <?php echo $accommodation->name; ?>
		</div>
	
		<section>
			<h1><?php echo $accommodation->name; ?></h1>
			
			<div class="unit_info clearfix">
				<div id="full-unit-information">
					<ul class="accommodation-image-slider">
					<?php if (!empty($accommodation->photo_1)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_1, 620, 465); ?></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_2)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_2, 620, 465); ?></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_3)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_3, 620, 465); ?></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_4)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_4, 620, 465); ?></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_5)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_5, 620, 465); ?></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_6)): ?>
						<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_6, 620, 465); ?></li>
					<?php endif; ?>
					</ul>
					
					<ul class="accommodation-image-thumbs clearfix">
					<?php if (!empty($accommodation->photo_1)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_1, 100, 75); ?></a></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_2)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_2, 100, 75); ?></a></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_3)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_3, 100, 75); ?></a></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_4)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_4, 100, 75); ?></a></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_5)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_5, 100, 75); ?></a></li>
					<?php endif; ?>
					<?php if (!empty($accommodation->photo_6)): ?>
						<li><a href="#" class="image-thumb-nav"><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_6, 100, 75); ?></a></li>
					<?php endif; ?>
					</ul>
			
					<h2><?php echo nl2br($accommodation->description); ?></h2>
					
					<p><b>Sleeps: </b><?php echo $accommodation->sleeps; ?></p>
					
					<ul>
						<?php 
							$amenities = explode("\n", $accommodation->amenities); 
							foreach ($amenities as $item):
						?>
							<li><?php echo $item; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
									
				<?php echo form_open('accommodation/unit/' . $accommodation->id, array('id' => 'accommodation-booking-form', 'class' => 'booking-form')); ?>
					<input type="hidden" name="site_id" id="site_id" value="<?php echo $accommodation->site_id; ?>" />
					<input type="hidden" name="accommodation_ids" id="accommodation_id" value="<?php echo $accommodation->id; ?>" />
					<input type="hidden" name="start_date" id="start_date" value="" />
					<input type="hidden" name="total_price" id="total_price" value="" />
				
					<h1>Book '<?php echo $accommodation->name; ?>'</h1>
				
					<div id="accommodation-calendar" data-accom-id="<?php echo $accommodation->id; ?>" data-type="<?php echo $accommodation->type_name; ?>" data-sleeps="<?php echo $accommodation->sleeps; ?>"></div>
					<?php if ($accommodation->type_name !== "Bunk Barn"): ?>
					<p><span class="key blue"></span> Public Holidays</p>
					<?php endif; ?>
					
					<p>
						<label for="duration">How many nights do you wish to stay?</label>
						<select name="duration" id="duration">
							<option value="">Please select an arrival date from the calendar</option>
						</select>
					</p>
				
					<p>
						<label for="adults">How many adults will be staying?</label>
						<select name="adults" id="adults">
							<?php for ($i=1; $i<=$accommodation->sleeps; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</p>
					
					<p>
						<label for="children">How many 4 to 17s will be staying?</label>
						<select name="children" id="children">
							<?php for ($i=0; $i<=$accommodation->sleeps; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</p>
					
					<?php if ($accommodation->type_name === "Bunk Barn"): ?>
					<p class="baby-message user-message">Unfortunately 0 to 3's are not able to stay in the bunk barn, please <a href="http://booking.thebivouac.co.uk/accommodation" title="Accommodation list">Choose a Woodland Shack or a Meadow Yurt</a></p>
					<?php else: ?>
					<p>
						<label for="babies">How many 0 to 3s will be staying?</label>
						<select name="babies" id="babies">
							<?php for ($i=0; $i<=$accommodation->sleeps; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</p>
					<?php endif; ?>
					
					<input type="hidden" name="dogs" value="0">
					<!--
					<?php if ($accommodation->dogs_allowed === "no"): ?>
					<p class="baby-message user-message">Unfortunately dogs are not able to stay at '<?php echo $accommodation->name; ?>'.</p>
					<?php else: ?>
					<p>
						<label for="dogs">How many dogs will be staying?</label>
						<select name="dogs" id="dogs">
							<?php for ($i=0; $i<=2; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
					</p>
					<?php endif; ?>
					-->
					<p class="user-message">Sorry this <?php echo $accommodation->type_name; ?> will only accommodate up to <?php echo $accommodation->sleeps; ?> people aged 4+.</p>
					
					<ul class="errors">
						<?php echo validation_errors('<li>', '</li>'); ?>
					</ul>
					
					<p>
						<input type="submit" name="submit" id="submit" value="Book these dates" />
					</p>
				</form>
			</div>
		</section>
	</div>
		
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php if ($accommodation->type_name === "Bunk Barn"): ?>
<div id="tooltip_hint"></div>
<?php endif; ?>
<?php $this->load->view("_includes/frontend/js"); ?>