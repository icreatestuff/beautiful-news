<?php 
$data['title'] = "Edit Accommodation Entry";
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
			<h2>Edit Accommodation Entry <?php echo anchor('admin/accommodation/new_accommodation/', 'Add', array('title' => 'Add New Accommodation', 'class' => 'add')); ?></h2>
			<section>
				<h1>Edit Accommodation Entry &mdash; <?php echo $accommodation->name; ?></h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
					<?php if (isset($upload_error)) { echo "<li>" . $upload_error . "</li>"; } ?>
				</ul>

				<?php echo form_open('admin/accommodation/edit_accommodation/' . $accommodation->id, array('id' => 'accommodation-form', 'class' => 'admin-form', 'enctype' => 'multipart/form-data')); ?>
				<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
				<p>
					<label for="name">Name</label>
					<input type="text" name="name" id="name" value="<?php echo $accommodation->name; ?>" />
				</p>
				<p>
					<label for="unit_id">Unit ID (e.g. L01)</label>
					<input type="text" name="unit_id" id="unit_id" value="<?php echo $accommodation->unit_id; ?>" />
				</p>
				<p>
					<label for="status">Status</label>
					<select name="status" id="status">
						<option value="open" <?php if ($accommodation->status === 'open') { echo "selected"; } ?>>Open</option>
						<option value="closed" <?php if ($accommodation->status === 'closed') { echo "selected"; } ?>>Closed</option>
					</select>
				</p>
				<?php if ($types->num_rows() > 0): ?>
				<p>
					<label for="type">Type of accommodation</label>
					<select name="type" id="type">
						<?php foreach ($types->result() as $row): ?>
							<option value="<?php echo $row->id; ?>" <?php if ($accommodation->type === $row->id) { echo "selected"; } ?>><?php echo $row->name; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php endif; ?>
				<p>
					<label for="bedrooms">Number of bedrooms</label>
					<select name="bedrooms" id="bedrooms">
						<?php for($i=0; $i<11; $i++): ?>
						<option value="<?php echo $i; ?>" <?php if ($accommodation->bedrooms == $i) { echo "selected='selected'"; } ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</p>
				<p>
					<label for="sleeps">How many does it sleep?</label>
					<select name="sleeps" id="sleeps">
						<?php for($i=0; $i<21; $i++): ?>
						<option value="<?php echo $i; ?>" <?php if ($accommodation->sleeps == $i) { echo "selected='selected'"; } ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</p>
				
				<p>
					<label for="dogs_allowed">Are dogs allowed?</label>
					<select name="dogs_allowed" id="dogs_allowed">
						<option value="no" <?php if ($accommodation->dogs_allowed == 'no') { echo "selected='selected'"; } ?>>No</option>
						<option value="yes" <?php if ($accommodation->dogs_allowed == 'yes') { echo "selected='selected'"; } ?>>Yes</option>
					</select>
				</p>
							
				<p>
					<label for="description">Description</label>
					<textarea name="description" id="description" cols="40" rows="8"><?php echo $accommodation->description; ?></textarea>
				</p>
				<p>
					<label for="amenities">Amenities</label>
					<textarea name="amenities" id="amenities" cols="40" rows="8"><?php echo $accommodation->amenities; ?></textarea>
				</p>
				<p>
					<label for="additional_per_night_charge">Additional Per Night Charge (£)</label>
					<input type="text" name="additional_per_night_charge" id="additional_per_night_charge" value="<?php echo $accommodation->additional_per_night_charge; ?>" />
				</p>
				<p>
					<label for="photo_1">Photo 1</label>
					<?php if (!empty($accommodation->photo_1)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_1; ?>" width="100" />
						<a href="#" data-photo="photo_1" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_1" id="photo_1" value="<?php echo $accommodation->photo_1; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_1" <?php if (!empty($accommodation->photo_1)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="photo_2">Photo 2</label>
					<?php if (!empty($accommodation->photo_2)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_2; ?>" width="100" />
						<a href="#" data-photo="photo_2" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_2" id="photo_2" value="<?php echo $accommodation->photo_2; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_2" <?php if (!empty($accommodation->photo_2)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="photo_3">Photo 3</label>
					<?php if (!empty($accommodation->photo_3)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_3; ?>" width="100" />
						<a href="#" data-photo="photo_3" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_3" value="<?php echo $accommodation->photo_3; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_3" id="photo_3" <?php if (!empty($accommodation->photo_3)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="photo_4">Photo 4</label>
					<?php if (!empty($accommodation->photo_4)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_4; ?>" width="100" />				
						<a href="#" data-photo="photo_4" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_4" value="<?php echo $accommodation->photo_4; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_4" id="photo_4" <?php if (!empty($accommodation->photo_4)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="photo_5">Photo 5</label>
					<?php if (!empty($accommodation->photo_5)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_5; ?>" width="100" />
						<a href="#" data-photo="photo_5" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_5" value="<?php echo $accommodation->photo_5; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_5" id="photo_5" <?php if (!empty($accommodation->photo_5)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<label for="photo_6">Photo 6</label>
					<?php if (!empty($accommodation->photo_6)): ?>
						<img src="<?php echo base_url() . 'images/accommodation/' . $accommodation->photo_6; ?>" width="100" />
						<a href="#" data-photo="photo_6" class="remove-photo">Remove Photo</a>
						<input type="hidden" name="photo_6" value="<?php echo $accommodation->photo_6; ?>" />
					<?php endif; ?>
					<input type="file" name="photo_6" id="photo_6" <?php if (!empty($accommodation->photo_6)) { echo 'class="hidden"'; } ?> value="" />
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
					<?php echo anchor('admin/accommodation', 'Cancel', array('title' => 'Cancel update', 'class' => 'cancel-edit')); ?>
				</p>
				
				<p><?php echo anchor('admin/accommodation', 'View all accommodation', array('title' => 'View all accommodation')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");