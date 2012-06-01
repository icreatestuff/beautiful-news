<?php 
$data['title'] = "New Site Entry";
$data['location'] = "sites";
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
			<h2>New Site Entry <?php echo anchor('admin/sites/new_site/', 'Add', array('title' => 'Add New Site', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/sites/edit_site/' . $site->id, array('id' => 'site-form', 'class' => 'admin-form')); ?>
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo $site->name; ?>" />
				</p>		
				<p>
					<label for="address_line_1">Address Line 1</label>
					<input type="text" name="address_line_1" id="address_line_1" value="<?php echo $site->address_line_1; ?>">
				</p>
				<p>
					<label for="address_line_2">Address Line 2</label>
					<input type="text" name="address_line_2" id="address_line_2" value="<?php if (!empty($site->address_line_2)) { echo $site->address_line_2; } ?>">
				</p>
				<p>
					<label for="city">Town/City</label>
					<input type="text" name="city" id="city" value="<?php echo $site->city; ?>">
				</p>
				<p>
					<label for="county">County</label>
					<input type="text" name="county" id="county" value="<?php echo $site->county; ?>">
				</p>
				<p>
					<label for="postcode">Postcode</label>
					<input type="text" name="postcode" id="postcode" value="<?php echo $site->postcode; ?>">
				</p>
				<p>
					<label for="deposit_percentage">Deposit %</label>
					<input type="text" name="deposit_percentage" id="deposit_percentage" value="<?php echo $site->deposit_percentage; ?>">
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
				
				<p><?php echo anchor('admin/sites', 'View all sites', array('title' => 'View all sites')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");