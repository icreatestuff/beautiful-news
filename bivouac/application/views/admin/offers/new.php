<?php 
$data['title'] = "New last minute offer";
$data['location'] = "offers";
$this->load->view("_includes/admin/head", $data); 
?>
<div id="container">
	<?php 
		$header_data['sites'] = $sites;
		$header_data['current_site'] = $current_site;
		$this->load->view("_includes/admin/header", $header_data); 
	?>
	
	<div id="main" class="clearfix">
		<?php $this->load->view("_includes/admin/nav"); ?>		
		<div id="content">
			<h2>New last minute offer</h2>
			<section id="<?php echo $this->uri->segment(2); ?>">
				<h1>Add new offer</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/offers/new_offer', array('id' => 'offer-form', 'class' => 'admin-form')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<input type="hidden" name="total_price" id="total_price" values="" />
				<input type="hidden" name="discount_price" id="discount_price" value="" />
				
				<p>Please select a weekend to view all available accommodation (click on the friday)</p>
				<div id="calendar"></div>
				
				<ul id="offers-accommodation-list"></ul>
				
				<p>
					<label for="start_date">Offer Start Date</label>
					<input type="text" name="start_date" id="start_date" class="datepicker" value="" />
				</p>
				<p>
					<label for="end_date">Offer End Date</label>
					<input type="text" name="end_date" id="end_date" class="datepicker" value="" />
				</p>
				
				<p>
					<label for="status">Status</label>
					<select id="status" name="status">
						<option value="open">Open</option>
						<option value="closed">Closed</option>
					</select>
				</p>
	
				<p>
					<label for="percentage_discount">Discount Percentage <br /><span>If you have selected more than 1 unit from above the discount will be applied to all</span></label>
					<input type="text" name="percentage_discount" id="percentage_discount" value="" />
					<span>Discount price will be &pound;<span id="live-discount-price">0</span></span>
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
				
				<p><?php echo anchor('admin/weddings', 'View all wedding bookings', array('title' => 'View all wedding bookings')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");