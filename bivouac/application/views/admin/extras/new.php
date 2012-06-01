<?php 
$data['title'] = "New Extra Entry";
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
			<h2>New Extra Entry <?php echo anchor('admin/extra/new_extra/', 'Add', array('title' => 'Add New Extra', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/extras/new_extra', array('id' => 'extra-form', 'class' => 'admin-form', 'enctype' => 'multipart/form-data')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
				</p>
				<p>
					<label for="status">Status</label>
					<select name="status" id="status">
						<option value="open">Open</option>
						<option value="closed">Closed</option>
					</select>
				</p>
				<?php if ($types->num_rows() > 0): ?>
				<p>
					<label for="extra_type">Type of extra</label>
					<select name="extra_type" id="extra_type">
						<?php foreach ($types->result() as $row): ?>
							<option value="<?php echo $row->id; ?>"><?php echo $row->name; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php endif; ?>			
				<p>
					<label for="description">Description</label>
					<textarea name="description" id="description" cols="40" rows="8"><?php echo set_value('description'); ?></textarea>
				</p>
				<p>
					<label for="price">Price (&pound;)</label>
					<input type="text" name="price" id="price" value="<?php echo set_value('price'); ?>" />
				</p>
				<p>
					<label for="photo_1">Photo 1</label>
					<input type="file" name="photo_1" id="photo_1" value="<?php echo set_value('photo_1'); ?>" />
				</p>
				<p>
					<label for="start_date">Start Date/Time</label>
					<input type="text" name="start_date" id="start_date" class="date-input" value="<?php echo set_value('start_date'); ?>" />
				</p>
				<p>
					<label for="end_date">End Date/Time</label>
					<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo set_value('end_date'); ?>" />
				</p>
				<p>
					<label for="cut_off_date">Booking Cut-Off Date/Time</label>
					<input type="text" name="cut_off_date" id="cut_off_date" class="date-input" value="<?php echo set_value('cut_off_date'); ?>" />
				</p>
				<p>
					<label for="number_available">Number Available</label>
					<input type="text" name="number_available" id="number_available" value="<?php echo set_value('number_available'); ?>" />
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