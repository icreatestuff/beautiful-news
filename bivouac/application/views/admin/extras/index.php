<?php 
$data['title'] = "Manage Extras";
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
			<h2>Extras <?php echo anchor('admin/extras/new_extra/', 'Add', array('title' => 'Add New Extra', 'class' => 'add')); ?></h2>
			<section>
				<h1>Extras</h1>
				<ul class="sub-nav">
					<li><?php echo anchor('admin/extras/new_extra/', 'Add Extra', array('title' => 'Add Extra')); ?></li>
					<li><?php echo anchor('admin/extras/new_extra_type/', 'Add Extra Type', array('title' => 'Add Extra Type')); ?></li>
				</ul>
				
				<table id="<?php echo $this->uri->segment(2); ?>">
					<tbody>
						<?php	if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->extra_id; ?>" class="<?php if ($row->status == 'closed') { echo 'closed-entry'; } ?>">
								<td>
										<?php if (!empty($row->photo_1)): ?>
											<img src="<?php echo base_url() . 'images/extras/' . $row->photo_1; ?>" width="100" />
										<?php endif; ?>
									</td>
									<td><?php echo $row->extra_name; ?></td>
									<td><?php echo $row->type_name; ?></td>
									<td><?php echo $row->description; ?></td>
									<td>£<?php echo $row->price; ?></td>
									<td>
										<?php echo anchor('admin/extras/edit_extra/' . $row->extra_id, 'Edit', array('title' => 'Edit Extra')); ?><br />
										<a href="#" class="delete">Delete</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Primary Image</th>
							<th>Name</th>
							<th>Type</th>
							<th>Description</th>
							<th>Price</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");