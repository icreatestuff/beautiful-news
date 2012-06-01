<?php 
$data['title'] = "All Bookings";
$data['location'] = "admin";
$this->load->view("_includes/admin/head", $data); 
?>
<div id="container" class="all-bookings-container">
	<?php 
		$header_data['sites'] = $sites;
		$header_data['current_site'] = $current_site;
		$this->load->view("_includes/admin/header", $header_data);
	?>
	
	<div id="main" class="clearfix">
		<?php $this->load->view("_includes/admin/nav"); ?>		
		<div id="content">
			<h2>Bookings</h2>
			
			<section>
				<h1>All Bookings</h1>
				<ul class="booking-key">
					<li><span class='overdue'></span> Payment Overdue</li>
					<li><span class='warning'></span> Payment Warning</li>
				</ul>
				<p>
					<label for="quicksearch">Quicksearch (table will filter as you type)</label>
					<input type="text" id="quicksearch" name="quickksearch" value="" />
				</p>
				
				<?php 
					$flashdata = $this->session->flashdata('user_message');
					if (isset($flashdata) && !empty($flashdata)): 
				?>
					<div class="user-message">
						<?php echo $flashdata; ?>
					</div>
				<?php endif; ?>

				
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
							<td><?php echo $booking->name; ?></td>
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
							<td><?php echo $booking->amount_paid; ?></td>
							<td><?php echo $booking->payment_status; ?></td>
							<td class="actions-col">
								<?php echo anchor('admin/bookings/overview/' . $booking->id, 'View Full Details', array('title' => 'View Full Booking Details')); ?><br />
								<?php if (strtotime("now") < strtotime($booking->start_date) && $booking->payment_status !== "cancelled"): ?>
								<?php echo anchor('admin/bookings/send_receipt/' . $booking->id, 'Send Booking Receipt', array('title' => 'Send Booking Receipt')); ?><br />
								<?php echo anchor('admin/bookings/edit_booking/' . $booking->id, 'Edit this booking', array('title' => 'Edit booking')); ?><br />
								<a href="#" title="Cancel Booking" data-ref="<?php echo $booking->booking_ref; ?>" data-id="<?php echo $booking->id; ?>" class="cancel-booking">Cancel Booking</a>
								<?php endif; ?>
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
							<th>Amount Paid</th>
							<th>Payment Status</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
				<?php else: ?>
				<p>There are no future bookings</p>
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");