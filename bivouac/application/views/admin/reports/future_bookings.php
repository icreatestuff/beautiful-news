<?php 
$data['title'] = "Future Bookings";
$data['location'] = "reports";
$this->load->view("_includes/admin/head", $data); 
?>
	<h1>All future bookings!</h1>
	
	<?php if ($bookings->num_rows > 0): ?>
	<table>
		<tbody>
			<?php foreach($bookings->result() as $booking): ?>
			<?php
				if ($booking->payment_status === "deposit")
				{
					if (strtotime("now") > strtotime(' - 6 weeks', strtotime($booking->start_date)))
					{
						$status = "payment-warning";
					}
					else if (strtotime("now") > strtotime(' - 7 weeks', strtotime($booking->start_date)))
					{
						$status = "payment-overdue";
					}
					else
					{
						$status = "payment-in-date";
					}					
				}
				else if ($booking->payment_status === "cancelled")
				{
					$status = "cancelled-booking";
				}
				else
				{
					$status = "payment-in-date";
				}
			?>
			
			<tr class="<?php echo $status; ?>">
				<td><?php if ($booking->is_telephone_booking === "true") { echo "&#10004;"; } ?></td>
				<td><?php echo $booking->booking_ref; ?></td>
				<td><?php echo $booking->type; ?></td>
				<td>
					<?php
						$accommodation_ids = explode("|", $booking->accommodation_ids);	
						
						foreach ($accommodation_ids as $accommodation)
						{
							if (is_numeric($accommodation))
							{
								echo $this->report_model->get_accommodation_name($accommodation)->row()->name . "<br />";
							}
						}
					?>
				</td>
				<td><?php echo date('d/m/Y', strtotime($booking->start_date)); ?></td>
				<td><?php echo date('d/m/Y', strtotime($booking->end_date)); ?></td>
				<td><?php echo $booking->adults; ?></td>
				<td><?php echo $booking->children; ?></td>
				<td><?php echo $booking->babies; ?></td>
				<td><?php echo $booking->first_name . " " . $booking->last_name; ?></td>
				<td>
					<?php echo $booking->house_name; ?><br />
					<?php echo $booking->address_line_1; ?><br />
					<?php if (!empty($booking->address_line_2)) { echo $booking->address_line_2 . "<br />"; } ?>
					<?php echo $booking->city; ?><br />
					<?php echo $booking->county; ?><br />
					<?php echo $booking->post_code; ?>
				</td>
				<td><?php echo $booking->total_price; ?></td>
				<td><?php echo $booking->payment_status; ?></td>
				<td>
					<?php echo anchor('admin/bookings/overview/' . $booking->id, 'View Full Details', array('title' => 'View Full Booking Details')); ?><br />
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<thead>
			<tr>
				<th>Tel Booking?</th>
				<th>Booking Ref</th>
				<th>Booking Type</th>
				<th>Accommodation</th>
				<th>Arrival Date</th>
				<th>Departure Date</th>
				<th>Adult Guests</th>
				<th>4 to 17s</th>
				<th>0 to 3s</th>
				<th>Contact Name</th>
				<th>Contact Address</th>
				<th>Price</th>
				<th>Payment Status</th>
				<th>Actions</th>
			</tr>
		</thead>
	</table>
	<?php else: ?>
	<p>There are no future bookings</p>
	<?php endif; ?>
	
	<p><a href="#" class="export-xls" data-title="Future Bookings" data-model_function="get_future_bookings" data-secondary="<?php echo date('Y-m-d'); ?>">Export .xls</a>
	<div id="result"></div>
	
	<p><?php echo anchor('admin/reports/index/', 'View all reports', array('title' => 'View all reports')); ?></p>
<?php $this->load->view("_includes/admin/footer") ?>