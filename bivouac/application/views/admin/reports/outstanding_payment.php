<?php 
$data['title'] = "Bookings not fully paid";
$data['location'] = "reports";
$this->load->view("_includes/admin/head", $data); 
?>
	<h1>All bookings that have yet to be paid in full</h1>
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
				<td><?php echo date('d/m/Y', strtotime($booking->start_date)); ?></td>
				<td><?php echo date('d/m/Y', strtotime($booking->end_date)); ?></td>
				<td><?php echo $booking->adults; ?></td>
				<td><?php echo $booking->children; ?></td>
				<td><?php echo $booking->babies; ?></td>
				<td><?php echo $booking->first_name . " " . $booking->last_name; ?></td>
				<td><a href="mailto:<?php echo $booking->email_address; ?>"><?php echo $booking->email_address; ?></a></td>
				<td><?php echo $booking->daytime_number; ?></td>
				<td><?php echo $booking->total_price; ?></td>
				<td><?php echo $booking->amount_paid; ?></td>
				<td><?php echo ((float) $booking->total_price - (float) $booking->amount_paid); ?></td>
				<td><?php echo anchor('admin/bookings/overview/' . $booking->id, 'View Full Details', array('title' => 'View Full Booking Details')); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<thead>
			<tr>
				<th>Booking Ref</th>
				<th>Accommodation</th>
				<th>Arrival Date</th>
				<th>Departure Date</th>
				<th>Adults</th>
				<th>4-17's</th>
				<th>0-3's</th>
				<th>Contact Name</th>
				<th>Email Address</th>
				<th>Tel Number</th>
				<th>Price</th>
				<th>Amount Paid</th>
				<th>Outstanding</th>
				<th>Actions</th>
			</tr>
		</thead>
	</table>
	<?php else: ?>
	<p>There are no bookings with outstanding payment</p>
	<?php endif; ?>
	
	<p><a href="#" class="export-xls" data-title="Outstanding Payment" data-model_function="get_outstanding_payment_bookings">Export .xls</a>
	<div id="result"></div>
	
	<p><?php echo anchor('admin/reports/index/', 'View all reports', array('title' => 'View all reports')); ?></p>
<?php $this->load->view("_includes/admin/footer") ?>