<?php 
$data['title'] = "Manage Last Minute Offers";
$data['location'] = "offers";
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
			<h2>Offers <?php echo anchor('admin/offers/new_offer/', 'Add', array('title' => 'Add new offer', 'class' => 'add')); ?></h2>
			<section>
				<h1>Offers</h1>	
				<ul class="sub-nav">
					<li><?php echo anchor('admin/offers/new_offer/', 'Add Offer', array('title' => 'Add new Offer')); ?></li>
				</ul>
								
				<table id="<?php echo $this->uri->segment(2); ?>">
					<tbody>
						<?php if ($query->num_rows() > 0): ?>
							<?php foreach ($query->result() as $row): ?>
								<tr data-id="<?php echo $row->id; ?>" class="<?php echo $row->status; ?>">
									<td><?php echo $row->name; ?></td>
									<td><?php echo date('d/m/Y', strtotime($row->start_date)); ?></td>
									<td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
									<td><?php echo $row->total_price; ?></td>
									<td><?php echo $row->discount_price; ?></td>
									<td><?php echo $row->percentage_discount; ?></td>
									<td>
										<?php echo anchor('admin/offers/edit/' . $row->id, 'Edit', array('title' => 'Edit Offer')); ?><br />
										<a href="#" class="delete">Delete</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<thead>
						<tr>
							<th>Accommodation Unit</th>
							<th>Start Date</th>
							<th>End Date</th>
							<th>Original Price</th>
							<th>Discount Price</th>
							<th>Discount Percentage</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");