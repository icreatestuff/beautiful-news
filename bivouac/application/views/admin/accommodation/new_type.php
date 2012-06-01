<?php 
$data['title'] = "New Accommodation Type Entry";
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
			<h2>New Accommodation Type Entry <?php echo anchor('admin/accommodation/new_accommodation/', 'Add', array('title' => 'Add New Accommodation', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/accommodation/new_accommodation_type', array('id' => 'accommodation-type-form', 'class' => 'admin-form')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
				</p>
				<p>
					<label for="base_price">High Price - per night (&pound;)</label>
					<input type="text" name="base_price" id="base_price" value="<?php echo set_value('base_price'); ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
							
				<p><?php echo anchor('admin/accommodation/types', 'View all accommodation Types', array('title' => 'View all accommodation types')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");