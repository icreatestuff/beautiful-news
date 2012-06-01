<?php 
$data['title'] = "Edit Site Closed Dates";
$data['location'] = "closed";
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
			<h2>Edit Closed Dates</h2>
			<section>
				<h1>Edit Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/site_closed/edit_closed_dates/' . $closed->id, array('id' => 'site-closed-form', 'class' => 'admin-form')); ?>
				<p>
					<label for="start_date">Start Date</label>
					<input type="text" name="start_date" class="date-input" id="start_date" value="<?php echo date('d-m-Y', strtotime($closed->start_date)); ?>" />
				</p>
				<p>
					<label for="end_date">End Date</label>
					<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo date('d-m-Y', strtotime($closed->end_date)); ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
					<?php echo anchor('admin/site_closed/', 'Cancel', array('title' => 'Cancel')); ?>
				</p>
							
				<p><?php echo anchor('admin/site_closed/', 'View all site closed dates', array('title' => 'View all site closed dates')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");