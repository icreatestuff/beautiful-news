<?php 
$data['title'] = "Manage Accommodation Types";
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
			<h2>Accommodation Types <?php echo anchor('admin/accommodation/new_accommodation_type/', 'Add', array('title' => 'Add New Accommodation Type', 'class' => 'add')); ?></h2>
			<section>
				<h1>Accommodation</h1>
				<ul class="sub-nav">
					<li><?php echo anchor('admin/accommodation/', 'Manage Accommodation', array('title' => 'Manage Accommodation')); ?></li>
					<li><?php echo anchor('admin/accommodation/new_accommodation/', 'Add Accommodation', array('title' => 'Add Accommodation')); ?></li>
					<li><?php echo anchor('admin/accommodation/new_accommodation_type/', 'Add Accommodation Type', array('title' => 'Add Accommodation Type')); ?></li>
				</ul>
				
				<table id="<?php echo $this->uri->segment(1); ?>">
					<tbody>
						<?php	if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->id; ?>">
									<td><?php echo $row->name; ?></td>
									<td><?php echo $row->high_price; ?></td>
									<td>
										<?php echo anchor('admin/accommodation/edit_accommodation_type/' . $row->id, 'Edit', array('title' => 'Edit Accommodation Type')); ?><br />
										<!-- <a href="#" class="delete">Delete</a> -->
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Name</th>
							<th>High Price (per night)</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");