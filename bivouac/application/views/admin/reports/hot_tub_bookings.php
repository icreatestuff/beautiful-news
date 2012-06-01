<?php 
$data['title'] = "Hot Tubs booked in the future";
$data['location'] = "reports";
$this->load->view("_includes/admin/head", $data); 
?>
	<h1>All Hot Tubs booked in the future</h1>
	<?php if ($bookings->num_rows > 0): ?>
	<table>
		<tbody>
			<?php foreach($bookings->result() as $booking): ?>
			<tr>
				<td><?php echo $booking->booking_ref; ?></td>
				<td>
					<?php
						$accommodation_ids = explode("|", $booking->accommodation_ids);	
						
						foreach ($accommodation_ids as $accommodation)
						{
							echo $this->report_model->get_accommodation_name($accommodation)->row()->name . "<br />";
						}
					?>
				</td>
				<td><?php echo date('d/m/Y', strtotime($booking->date)); ?></td>
				<td><?php echo $booking->first_name . " " . $booking->last_name; ?></td>
				<td><?php echo anchor('admin/bookings/overview/' . $booking->id, 'View Full Details', array('title' => 'View Full Booking Details')); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<thead>
			<tr>
				<th>Booking Ref</th>
				<th>Accommodation</th>
				<th>Booked Date</th>
				<th>Contact Name</th>
				<th>Actions</th>
			</tr>
		</thead>
	</table>
	<?php else: ?>
	<p>There are no hot tubs booked in the future</p>
	<?php endif; ?>

	<p><a href="#" class="export-xls" data-title="Future Hot Tub Bookings" data-model_function="get_hot_tub_bookings">Export .xls</a>
	<div id="result"></div>
	
	<p><?php echo anchor('admin/reports/index/', 'View all reports', array('title' => 'View all reports')); ?></p>
<?php $this->load->view("_includes/admin/footer") ?>