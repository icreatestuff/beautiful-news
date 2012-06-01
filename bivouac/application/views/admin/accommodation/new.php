<?php 
$data['title'] = "New Accommodation Entry";
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
			<h2>New Accommodation Entry <?php echo anchor('admin/accommodation/new_accommodation/', 'Add', array('title' => 'Add New Accommodation', 'class' => 'add')); ?></h2>
			<section>
				<h1>Add New Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
					<?php if (isset($upload_error)) { echo "<li>" . $upload_error . "</li>"; } ?>
				</ul>

				<?php echo form_open('admin/accommodation/new_accommodation', array('id' => 'accommodation-form', 'class' => 'admin-form', 'enctype' => 'multipart/form-data')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
				</p>
				<p>
					<label for="unit_id">Unit ID (e.g. L01)</label>
					<input type="text" name="unit_id" id="unit_id" value="<?php echo set_value('unit_id'); ?>" />
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
					<label for="type">Type of accommodation</label>
					<select name="type" id="type">
						<?php foreach ($types->result() as $row): ?>
							<option value="<?php echo $row->id; ?>"><?php echo $row->name; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php endif; ?>
				<p>
					<label for="bedrooms">Number of bedrooms</label>
					<select name="bedrooms" id="bedrooms">
						<?php for($i=0; $i<11; $i++): ?>
						<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</p>
				<p>
					<label for="sleeps">How many does it sleep?</label>
					<select name="sleeps" id="sleeps">
						<?php for($i=0; $i<21; $i++): ?>
						<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</p>
				
				<p>
					<label for="dogs_allowed">Are dogs allowed?</label>
					<select name="dogs_allowed" id="dogs_allowed">
						<option value="no">No</option>
						<option value="yes">Yes</option>
					</select>
				</p>
							
				<p>
					<label for="description">Description</label>
					<textarea name="description" id="description" cols="40" rows="8"><?php echo set_value('description'); ?></textarea>
				</p>
				<p>
					<label for="amenities">Amenities</label>
					<textarea name="amenities" id="amenities" cols="40" rows="8"><?php echo set_value('amenities'); ?></textarea>
				</p>
				<p>
					<label for="additional_per_night_charge">Additional Per Night Charge (£)</label>
					<input type="text" name="additional_per_night_charge" id="additional_per_night_charge" value="<?php echo set_value('additional_per_night_charge'); ?>" />
				</p>
				<p>
					<label for="photo_1">Photo 1</label>
					<input type="file" name="photo_1" id="photo_1" value="<?php echo set_value('photo_1'); ?>" />
				</p>
				<p>
					<label for="photo_2">Photo 2</label>
					<input type="file" name="photo_2" id="photo_2" value="<?php echo set_value('photo_2'); ?>" />
				</p>
				<p>
					<label for="photo_3">Photo 3</label>
					<input type="file" name="photo_3" id="photo_3" value="" />
				</p>
				<p>
					<label for="photo_4">Photo 4</label>
					<input type="file" name="photo_4" id="photo_4" value="" />
				</p>
				<p>
					<label for="photo_5">Photo 5</label>
					<input type="file" name="photo_5" id="photo_5" value="" />
				</p>
				<p>
					<label for="photo_6">Photo 6</label>
					<input type="file" name="photo_6" id="photo_6" value="" />
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
				</p>
				
				<p><?php echo anchor('admin/accommodation', 'View all accommodation', array('title' => 'View all accommodation')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");