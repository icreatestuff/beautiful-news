<?php 
$data['title'] = "Edit Extra Entry";
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
			<h2>Edit Extra Entry <?php echo anchor('admin/extras/new_extra/', 'Add', array('title' => 'Add New Extra', 'class' => 'add')); ?></h2>
			<section>
				<h1>Edit Extra Entry &mdash; <?php echo $extra->name; ?></h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/extras/edit_extra/' . $extra->id, array('id' => 'extra-form', 'class' => 'admin-form', 'enctype' => 'multipart/form-data')); ?>
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo $extra->name; ?>" />
				</p>
				<p>
					<label for="status">Status</label>
					<select name="status" id="status">
						<option value="open" <?php if ($extra->status === 'open') { echo "selected"; } ?>>Open</option>
						<option value="closed" <?php if ($extra->status === 'closed') { echo "selected"; } ?>>Closed</option>
					</select>
				</p>
				<?php if ($types->num_rows() > 0): ?>
				<p>
					<label for="extra_type">Type of extra</label>
					<select name="extra_type" id="extra_type">
						<?php foreach ($types->result() as $row): ?>
							<option value="<?php echo $row->id; ?>" <?php if ($extra->extra_type === $row->id) { echo "selected"; } ?>><?php echo $row->name; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php endif; ?>			
				<p>
					<label for="description">Description</label>
					<textarea name="description" id="description" cols="40" rows="8"><?php echo $extra->description; ?></textarea>
				</p>
				<p>
					<label for="price">Price (&pound;)</label>
					<input type="text" name="price" id="price" value="<?php echo $extra->price; ?>" />
				</p>
				<p>
					<label for="photo_1">Photo 1</label>
					<?php if (!empty($extra->photo_1)): ?>
						<img src="<?php echo base_url() . 'images/extras/' . $extra->photo_1; ?>" width="100" />
						<a href="#" data-photo="photo_1" class="remove-photo">Remove Photo</a>
					<?php endif; ?>
					<input type="file" name="photo_1" id="photo_1" <?php if (!empty($extra->photo_1)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="start_date">Start Date/Time</label>
					<input type="text" name="start_date" id="start_date" class="date-input" value="<?php if (!empty($extra->start_date) && strtotime($extra->start_date) != 0) { echo date('d-m-Y H:i', strtotime($extra->start_date)); } ?>" />
				</p>
				<p>
					<label for="end_date">End Date/Time</label>
					<input type="text" name="end_date" id="end_date" class="date-input" value="<?php if (!empty($extra->end_date) && strtotime($extra->end_date) != 0) { echo date('d-m-Y H:i', strtotime($extra->end_date)); } ?>" />
				</p>
				<p>
					<label for="cut_off_date">Booking Cut-Off Date/Time</label>
					<input type="text" name="cut_off_date" id="cut_off_date" class="date-input" value="<?php if (!empty($extra->cut_off_date) && strtotime($extra->cut_off_date) != 0) { echo date('d-m-Y H:i', strtotime($extra->cut_off_date)); } ?>" />
				</p>
				<p>
					<label for="number_available">Number Available</label>
					<input type="text" name="number_available" id="number_available" value="<?php if (!empty($extra->number_available)) { echo $extra->number_available; } ?>" />
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
					<?php echo anchor('admin/extras', 'Cancel', array('title' => 'Cancel update', 'class' => 'cancel-edit')); ?>
				</p>
				
				<p><?php echo anchor('admin/extras', 'View all extras', array('title' => 'View all extras')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");