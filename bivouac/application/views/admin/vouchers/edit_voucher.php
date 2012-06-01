<?php 
$data['title'] = "Edit Voucher Code";
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
			<h2>Edit Voucher Code</h2>
			<section>
				<h1>Edit Entry</h1>
				<ul class="errors">
					<?php echo validation_errors('<li>', '</li>'); ?>
				</ul>

				<?php echo form_open('admin/vouchers/edit_voucher/' . $voucher->id, array('id' => 'vouchers-form', 'class' => 'admin-form')); ?>
				<p>
					<label for="name">Voucher Name - <span>This is what the use will enter when booking</span></label>
					<input type="text" name="name" id="name" value="<?php echo $voucher->name; ?>" />
				</p>
				<p>
					<label for="start_date">Start Date</label>
					<input type="text" name="start_date" class="date-input" id="start_date" value="<?php echo date('d-m-Y', strtotime($voucher->start_date)); ?>" />
				</p>
				<p>
					<label for="end_date">End Date</label>
					<input type="text" name="end_date" id="end_date" class="date-input" value="<?php echo date('d-m-Y', strtotime($voucher->end_date)); ?>" />
				</p>
				<p>
					<label for="discount_price">Discount Price (&pound;) - <span>Use either discount Price <b><u>or</u></b> Discount Percentage. Never Both</span></label>
					<input type="text" name="discount_price" id="discount_price" value="<?php echo $voucher->discount_price; ?>" />
				</p>
				<p>
					<label for="discount_percentage">Discount Percentage</label>
					<input type="text" name="discount_percentage" id="discount_percentage" value="<?php echo $voucher->discount_percentage; ?>" />
				</p>
				<p>
					<label for="valid_from">Valid From Date</label>
					<input type="text" class="date-input" name="valid_from" id="valid_from" value="<?php echo date('d-m-Y', strtotime($voucher->valid_from)); ?>" />
				</p>
				<p>
					<label for="valid_to">Valid To Date</label>
					<input type="text" class="date-input" name="valid_to" id="valid_to" value="<?php echo date('d-m-Y', strtotime($voucher->valid_to)); ?>" />
				</p>
				
				<p>
					<input type="submit" name="submit" id="submit" value="Submit" />
					<?php echo anchor('admin/vouchers/', 'Cancel', array('title' => 'Cancel')); ?>
				</p>
							
				<p><?php echo anchor('admin/vouchers/', 'View all Voucher codes', array('title' => 'View all Voucher Codes')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");