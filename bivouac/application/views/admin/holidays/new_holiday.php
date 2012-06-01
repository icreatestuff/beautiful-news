<?php 
$data['title'] = "New Date/Price Range";
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
			<h2>New Date/Price Range <?php echo anchor('admin/pricing/new_schema/', 'Add', array('title' => 'Add New Date/Price Range', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/pricing/new_schema', array('id' => 'pricing-form', 'class' => 'admin-form')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="start_date">Start Date</label>
					<input type="text" name="start_date" id="start_date" value="<?php echo set_value('start_date'); ?>" />
				</p>
				<p>
					<label for="end_date">End Date</label>
					<input type="text" name="end_date" id="end_date" value="<?php echo set_value('end_date'); ?>" />
				</p>
				<p>
					<label for="lodge">Lodge (%)</label>
					<input type="text" name="lodge" id="lodge" value="<?php echo set_value('lodge'); ?>" />
				</p>
				<p>
					<label for="yurt">Yurt (%)</label>
					<input type="text" name="yurt" id="yurt" value="<?php echo set_value('yurt'); ?>" />
				</p>
				<p>
					<label for="camping_barn">Camping Barn (%)</label>
					<input type="text" name="camping_barn" id="camping_barn" value="<?php echo set_value('camping_barn'); ?>" />
				</p>
				<p>
					<label for="family_lodge">Family Lodge (%)</label>
					<input type="text" name="family_lodge" id="family_lodge" value="<?php echo set_value('family_lodge'); ?>" />
				</p>
				<p>
					<label for="camping_pitch">Camping Pitch (%)</label>
					<input type="text" name="camping_pitch" id="camping_pitch" value="<?php echo set_value('camping_pitch'); ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
							
				<p><?php echo anchor('admin/pricing/', 'View all Pricing Date Ranges', array('title' => 'View all Pricing Date Ranges')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");