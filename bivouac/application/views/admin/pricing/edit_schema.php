<?php 
$data['title'] = "Edit Date/Price Range";
$data['location'] = "accommodation";
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
			<h2>Edit Date/Price Range</h2>
			<section>
				<h1>Edit Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/pricing/edit_schema/' . $pricing->id, array('id' => 'pricing-form', 'class' => 'admin-form')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="start_date">Start Date</label>
					<input type="text" name="start_date" class="date-input" id="start_date" value="<?php echo date('d-m-Y', strtotime($pricing->start_date)); ?>" />
				</p>
				<p>
					<label for="end_date">End Date</label>
					<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo date('d-m-Y', strtotime($pricing->end_date)); ?>" />
				</p>
				<p>
					<label for="shack">Shack (%)</label>
					<input type="text" name="woodland_shack" id="woodland_shack" value="<?php echo $pricing->woodland_shack; ?>" />
				</p>
				<p>
					<label for="yurt">Yurt (%)</label>
					<input type="text" name="meadow_yurt" id="meadow_yurt" value="<?php echo $pricing->meadow_yurt; ?>" />
				</p>
				<p>
					<label for="camping_barn">Bunk Barn (%)</label>
					<input type="text" name="bunk_barn" id="bunk_barn" value="<?php echo $pricing->bunk_barn; ?>" />
				</p>
				<p>
					<label for="family_lodge">Family Lodge (%)</label>
					<input type="text" name="family_lodge" id="family_lodge" value="<?php echo $pricing->family_lodge; ?>" />
				</p>
				<p>
					<label for="camping_pitch">Camping Pitch (%)</label>
					<input type="text" name="camping_pitch" id="camping_pitch" value="<?php echo $pricing->camping_pitch; ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
					<?php echo anchor('admin/pricing/', 'Cancel', array('title' => 'Cancel')); ?>
				</p>
							
				<p><?php echo anchor('admin/pricing/', 'View all Pricing Date Ranges', array('title' => 'View all Pricing Date Ranges')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");