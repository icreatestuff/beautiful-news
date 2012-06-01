<?php 
$data['title'] = "Manage Wedding Bookings";
$data['location'] = "weddings";
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
			<h2>Weddings <?php echo anchor('admin/weddings/new_wedding/', 'Add', array('title' => 'Add Wedding', 'class' => 'add')); ?></h2>
			<section>
				<h1>Weddings</h1>	
				<ul class="sub-nav">
					<li><?php echo anchor('admin/weddings/new_wedding/', 'Add Wedding', array('title' => 'Add Wedding')); ?></li>
				</ul>
								
				<table id="<?php echo $this->uri->segment(2); ?>">
					<tbody>
						<?php if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->id; ?>">
									<td><?php echo $row->booking_ref; ?></td>
									<td><?php echo date('d/m/Y', strtotime($row->start_date)); ?></td>
									<td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
									<td><?php echo $row->total_price; ?></td>
									<td>
										<?php echo anchor('admin/weddings/edit_wedding/' . $row->id, 'Edit', array('title' => 'Edit Wedding booking')); ?><br />
										<a href="#" class="delete">Delete</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Wedding Ref No.</th>
							<th>Start Date</th>
							<th>End Date</th>
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