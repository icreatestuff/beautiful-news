<?php 
$data['title'] = "Manage Public Holidays";
$data['location'] = "holidays";
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
			<h2>Public Holidays</h2>
			<section>
				<h1>Public Holidays</h1>
				
				<?php echo form_open('admin/holidays/index', array('id' => 'holidays-form', 'class' => 'admin-form')); ?>					
					<table id="<?php echo $this->uri->segment(2); ?>">
						<tbody>
							<?php	if ($query->num_rows() > 0): ?>
								<?php foreach ($query->result() as $row): ?>
									<tr data-id="<?php echo $row->id; ?>">
										<td><?php echo $row->name; ?></td>
										<td><?php echo date('d/m/Y', strtotime($row->start_date)); ?></td>
										<td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
										<td>
											<?php echo anchor('admin/holidays/edit_holiday/' . $row->id, 'Edit', array('title' => 'Edit Public Holiday')); ?><br />
											<a href="#" class="delete">Delete</a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
								
							<tr class="holidays-form-container">
								<td>
									<input type="text" name="name" id="name" value="<?php echo set_value('name'); ?>" />
								</td>
								<td>
									<input type="text" name="start_date" id="start_date" class="date-input" value="<?php echo set_value('start_date'); ?>" />
								</td>
								<td>
									<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo set_value('end_date'); ?>" />
								</td>
								<td>
									<input type="submit" name="submit" id="submit" value="Submit" />	
								</td>	
							</tr>
						</tbody>
						<thead>
							<tr>
								<th>Holiday Name</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Actions</th>
							</tr>
						</thead>
					</table>
				</form>
				
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>
								
				<?php echo anchor('admin/holidays/new_holiday/', 'Add', array('title' => 'Add Public Holiday', 'class' => 'add', 'id' => 'holiday-add')); ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");