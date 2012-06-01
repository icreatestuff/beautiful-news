<?php 
$data['title'] = "Booking System Administration";
$data['location'] = "admin";
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
			<h2>Overview</h2>
			
			<section>
				<h1>Booking receipt successfully sent!</h1>
				
				<p><?php echo anchor('admin/bookings/all_bookings/', 'Back to all bookings', array('title' => 'View all Bookings')); ?></p>
				
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");