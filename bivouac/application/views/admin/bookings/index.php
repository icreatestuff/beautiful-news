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
				<h1>Most recent bookings</h1>
				
				<ul class="sub-nav">
					<li><?php echo anchor('admin/bookings/all_bookings/', 'View all bookings', array('title' => 'View all bookings')); ?></li>
				</ul>
				
				<ul class="booking-key">
					<li><span class='overdue'></span> Payment Overdue</li>
					<li><span class='warning'></span> Payment Warning</li>
				</ul>
				
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
							<td><?php echo $booking->booking_ref; ?></td>
							<td><?php echo $booking->name; ?></td>
							<td><?php echo date('d/m/Y', strtotime($booking->start_date)); ?></td>
							<td><?php echo date('d/m/Y', strtotime($booking->end_date)); ?></td>
							<td><?php echo (int) $booking->adults + (int) $booking->children; ?></td>
							<td><?php echo $booking->first_name . " " . $booking->last_name; ?></td>
							<td><?php echo $booking->total_price; ?></td>
							<td><?php echo $booking->amount_paid; ?></td>
							<td>
								<?php echo anchor('admin/bookings/overview/' . $booking->id, 'View Full Details', array('title' => 'View Full Booking Details')); ?><br />
								<?php if (strtotime("now") < strtotime($booking->start_date) && $booking->payment_status !== "cancelled"): ?>
								<?php echo anchor('admin/bookings/edit_booking/' . $booking->id, 'Edit this booking', array('title' => 'Edit booking')); ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					<thead>
						<tr>
							<th>Booking Ref</th>
							<th>Accommodation</th>
							<th>Arrival Date</th>
							<th>Departure Date</th>
							<th>Total Guests</th>
							<th>Contact Name</th>
							<th>Price</th>
							<th>Amount Paid</th>
							<th>Actions</th>
						</tr>
					</thead>
				</table>
				<?php else: ?>
				<p>There are no bookings</p>
				<?php endif; ?>
				<p><?php echo anchor('admin/bookings/all_bookings/', 'View all bookings', array('title' => 'View all Bookings')); ?></p>
			</section>
		</div>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer");