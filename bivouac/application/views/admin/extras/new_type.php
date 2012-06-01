<?php 
$data['title'] = "New Extra Type Entry";
$data['location'] = "extras";
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
			<h2>New Extra Type Entry <?php echo anchor('admin/extras/new_extra/', 'Add', array('title' => 'Add New Extra', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/extras/new_extra_type', array('id' => 'extra-type-form', 'class' => 'admin-form')); ?>
				
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
							
				<p><?php echo anchor('admin/extras', 'View all extras', array('title' => 'View all extras')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");