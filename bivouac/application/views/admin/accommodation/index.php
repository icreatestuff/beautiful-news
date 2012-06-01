<?php 
$data['title'] = "Manage Accommodation";
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
			<h2>Accommodation <?php echo anchor('admin/accommodation/new_accommodation/', 'Add', array('title' => 'Add New Accommodation', 'class' => 'add')); ?></h2>
			<section>
				<h1>Accommodation</h1>
				<ul class="sub-nav">
					<li><?php echo anchor('admin/accommodation/new_accommodation/', 'Add Accommodation', array('title' => 'Add Accommodation')); ?></li>
					<li><?php echo anchor('admin/accommodation/types/', 'Manage Accommodation Types', array('title' => 'Manage Accommodation Types')); ?></li>
					<li><?php echo anchor('admin/accommodation/new_accommodation_type/', 'Add Accommodation Type', array('title' => 'Add Accommodation Type')); ?></li>
				</ul>
				
				<table id="<?php echo $this->uri->segment(2); ?>">
					<tbody>
						<?php	if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->id; ?>" class="<?php if ($row->status == 'closed') { echo 'closed-entry'; } ?>">
									<td>
										<?php if (!empty($row->photo_1)): ?>
											<img src="<?php echo base_url() . 'images/accommodation/' . $row->photo_1; ?>" width="100" />
										<?php endif; ?>
									</td>
									<td><?php echo $row->unit_id; ?></td>
									<td><?php echo $row->name; ?></td>
									<td><?php echo $row->type_name; ?></td>
									<td><?php echo $row->bedrooms; ?></td>
									<td><?php echo $row->sleeps; ?></td>
									<td><?php echo $row->additional_per_night_charge; ?></td>
									<td>
										<?php echo anchor('admin/accommodation/edit_accommodation/' . $row->id, 'Edit', array('title' => 'Edit Accommodation')); ?><br />
										<?php echo anchor('admin/bookings/accommodation/' . $row->id, 'Bookings', array('title' => 'View all bookings for this accommodation')); ?><br />
										<a href="#" class="delete">Delete</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Primary Photo</th>
							<th>Unit ID</th>
							<th>Name</th>
							<th>Type</th>
							<th>Bedrooms</th>
							<th>Sleeps</th>
							<th>Additional p.n. charge (&pound;)</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");