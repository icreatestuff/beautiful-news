<?php 
$data['title'] = "Manage Sites";
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
			<h2>Sites <?php echo anchor('admin/sites/new_site/', 'Add', array('title' => 'Add New Site', 'class' => 'add')); ?></h2>
			<section>
				<h1>Sites</h1>
				<ul class="sub-nav">
					<li><?php echo anchor('admin/sites/new_site/', 'Add Site', array('title' => 'Add Site')); ?></li>
				</ul>
				
				<table id="<?php echo $this->uri->segment(1); ?>">
					<tbody>
						<?php	if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->id; ?>">
									<td><?php echo $row->name; ?></td>
									<td>
										<?php if (!empty($row->address_line_1)){ echo $row->address_line_1; } ?>
										<?php if (!empty($row->address_line_2)){ echo "<br />" . $row->address_line_2; } ?>
										<?php if (!empty($row->city)){ echo "<br />" . $row->city; } ?>
										<?php if (!empty($row->county)){ echo "<br />" . $row->county; } ?>
										<?php if (!empty($row->postcode)){ echo "<br />" . $row->postcode; } ?>
									</td>
									<td><?php echo $row->deposit_percentage; ?></td>
									<td>
										<?php echo anchor('admin/sites/edit_site/' . $row->id, 'Edit', array('title' => 'Edit Accommodation')); ?><br />
										<a href="#" class="delete">Delete</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Name</th>
							<th>Address</th>
							<th>Deposit %</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");