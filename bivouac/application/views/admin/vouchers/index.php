<?php 
$data['title'] = "Manage Voucher Codes";
$data['location'] = "vouchers";
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
			<h2>Voucher Codes</h2>
			<section>
				<h1>Voucher Codes</h1>
				
				<?php echo form_open('admin/vouchers/index', array('id' => 'vouchers-form', 'class' => 'admin-form')); ?>					
					<input type="hidden" name="site_id" id="site_id" value="<?php echo $this->session->userdata('site_id'); ?>" />
									
					<table id="<?php echo $this->uri->segment(2); ?>">
						<tbody>
							<?php	if ($query->num_rows() > 0): ?>
								<?php foreach ($query->result() as $row): ?>
									<tr data-id="<?php echo $row->id; ?>">
										<td><?php echo $row->name; ?></td>
										<td><?php echo date('d/m/Y', strtotime($row->start_date)); ?></td>
										<td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
										<td><?php echo $row->discount_price; ?></td>
										<td><?php echo $row->discount_percentage; ?></td>
										<td><?php if (!empty($row->valid_from)) { echo date('d/m/Y', strtotime($row->valid_from)); } ?></td>
										<td><?php if (!empty($row->valid_to)) { echo date('d/m/Y', strtotime($row->valid_to)); } ?></td>
										<td>
											<?php echo anchor('admin/vouchers/edit_voucher/' . $row->id, 'Edit', array('title' => 'Edit Voucher Code')); ?><br />
											<a href="#" class="delete">Delete</a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
								
							<tr class="vouchers-form-container">
								<td>
									<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
									<p><b>Note:</b> This is what the user will enter when making a booking</p>
								</td>
								<td>
									<input type="text" name="start_date" id="start_date" class="date-input" value="<?php echo set_value('start_date'); ?>" />
								</td>
								<td>
									<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo set_value('end_date'); ?>" />
								</td>
								<td>
									<input type="text" name="discount_price" id="discount_price" value="<?php echo set_value('discount_price'); ?>" />
									<p><b>Note:</b> Use either Discount Price <b><u>or</u></b> Discount Percentage. Never both. Thanks</p>
								</td>
								<td>
									<input type="text" name="discount_percentage" id="discount_percentage" value="<?php echo set_value('discount_percentage'); ?>" />
								</td>
								<td>
									<input type="text" name="valid_from" id="valid_from" class="date-input" value="<?php echo set_value('valid_from'); ?>" />
								</td>
								<td>
									<input type="text" name="valid_to" id="valid_to" class="date-input" value="<?php echo set_value('valid_to'); ?>" />
								</td>
								<td>
									<input type="submit" name="submit" id="submit" value="Submit" />	
								</td>	
							</tr>
						</tbody>
						<thead>
							<tr>
								<th>Voucher Name</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Discount Price (&pound;)</th>
								<th>Discount (%)</th>
								<th>Valid From</th>
								<th>Valid To</th>
								<th>Actions</th>
							</tr>
						</thead>
					</table>
				</form>
				
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>
								
				<?php echo anchor('admin/vouchers/new_voucher/', 'Add', array('title' => 'Add Voucher Code', 'class' => 'add', 'id' => 'voucher-add')); ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");