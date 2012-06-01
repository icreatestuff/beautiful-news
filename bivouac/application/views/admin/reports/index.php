<?php 
$data['title'] = "Booking System Reports";
$data['location'] = "reports";
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
				<h1>Reports</h1>
				<ul class="reports-list">
					<li><?php echo anchor('admin/reports/future_bookings', 'All future bookings', array('title' => 'All future bookings')); ?></li>
					<li><?php echo anchor('admin/reports/outstanding_payment', 'Outstanding payment bookings', array('title' => 'Outstanding Payment Bookings')); ?></li>
					<li><?php echo anchor('admin/reports/leaving_date', 'Leaving on given date', array('title' => 'Leaving on given date')); ?></li>
					<li><?php echo anchor('admin/reports/arrival_date', 'Arriving on given date', array('title' => 'Arriving on given date')); ?></li>
					<li><?php echo anchor('admin/reports/specific_extra', 'Bookings with specific extra requested', array('title' => 'Bookings with specific extra requested')); ?></li>
					<li><?php echo anchor('admin/reports/hot_tubs_booked', 'All future Hot Tub bookings', array('title' => 'All future Hot Tub bookings')); ?></li>
				</ul>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");