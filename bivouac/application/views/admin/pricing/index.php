<?php 
$data['title'] = "Manage Pricing Schema";
$data['location'] = "pricing";
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
			<h2>Pricing Schema</h2>
			<section>
				<h1>Pricing Schema</h1>
				
				<?php echo form_open('admin/pricing/index', array('id' => 'pricing-form', 'class' => 'admin-form')); ?>
					<input type="hidden" name="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
					
					<table id="<?php echo $this->uri->segment(2); ?>">
						<tbody>
							<?php	if ($query->num_rows() > 0): ?>
								<?php foreach ($query->result() as $row): ?>
									<tr data-id="<?php echo $row->id; ?>">
										<td><?php echo date('d/m/Y', strtotime($row->start_date)); ?></td>
										<td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
										<td><?php echo $row->woodland_shack; ?></td>
										<td><?php echo $row->meadow_yurt; ?></td>
										<td><?php echo $row->bunk_barn; ?></td>
										<td><?php echo $row->family_lodge; ?></td>
										<td><?php echo $row->camping_pitch; ?></td>
										<td>
											<?php echo anchor('admin/pricing/edit_schema/' . $row->id, 'Edit', array('title' => 'Edit Date/Price Range')); ?><br />
											<a href="#" class="delete">Delete</a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
								
							<tr class="pricing-form-container">
								<td>
									<input type="text" name="start_date" id="start_date" class="date-input" value="<?php echo set_value('start_date'); ?>" />
								</td>
								<td>
									<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo set_value('end_date'); ?>" />
								</td>
								<td>
									<input type="text" name="woodland_shack" id="woodland_shack" value="<?php echo set_value('lodge'); ?>" />
								</td>
								<td>
									<input type="text" name="meadow_yurt" id="meadow_yurt" value="<?php echo set_value('yurt'); ?>" />
								</td>
								<td>
									<input type="text" name="bunk_barn" id="bunk_barn" value="<?php echo set_value('bunk_barn'); ?>" />
								</td>
								<td>
									<input type="text" name="family_lodge" id="family_lodge" value="<?php echo set_value('family_lodge'); ?>" />
								</td>
								<td>
									<input type="text" name="camping_pitch" id="camping_pitch" value="<?php echo set_value('camping_pitch'); ?>" />
								</td>
								<td>
									<input type="submit" name="submit" id="submit" value="Submit" />	
								</td>	
							</tr>
						</tbody>
						<thead>
							<tr>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Woodland Shack (%)</th>
								<th>Meadow Yurt (%)</th>
								<th>Bunk Barn (%)</th>
								<th>Family Lodge (%)</th>
								<th>Camping Pitch (%)</th>
								<th>Actions</th>
							</tr>
						</thead>
					</table>
				</form>
				
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>
								
				<?php echo anchor('admin/pricing/new_schema/', 'Add', array('title' => 'Add New Date/Price Range', 'class' => 'add', 'id' => 'pricing-add')); ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");