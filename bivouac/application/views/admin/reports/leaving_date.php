<?php 
$data['title'] = "Leaving on given date";
$data['location'] = "reports";
$this->load->view("_includes/admin/head", $data); 
?>
	<h1>Show Bookings who are leaving on a given date</h1>
	
	<?php echo form_open('admin/reports/leaving_date', array('id' => 'leaving-date-form', 'class' => 'admin-form')); ?>
		<p>
			<label for="end_date">Select a leaving date (Monday or Friday)</label>
			<input type="text" name="end_date" id="end_date" class="date-input" value="" />
		</p>
		
		<input type="submit" name="submit" id="submit" value="Get bookings leaving on this date" />
	</form>
	
	<?php if (isset($bookings) && $bookings->num_rows > 0): ?>
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
				<td><?php echo $booking->total_price; ?></td>
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
				<th>Adult Guests</th>
				<th>4-17's</th>
				<th>0-3's</th>
				<th>Contact Name</th>
				<th>Price</th>
				<th>Actions</th>
			</tr>
		</thead>
	</table>
	<?php else: ?>
	<p>There are no bookings leaving on the date you chose.</p>
	<?php endif; ?>
	
	<?php if (isset($end_date) && !empty($end_date)): ?>
		<p><a href="#" class="export-xls" data-title="Bookings ending on <?php echo $end_date; ?>" data-model_function="get_bookings_from_end_date" data-secondary="<?php echo $end_date; ?>">Export .xls</a>
		<div id="result"></div>
	<?php endif; ?>
	
	<p><?php echo anchor('admin/reports/index/', 'View all reports', array('title' => 'View all reports')); ?></p>
<?php $this->load->view("_includes/admin/footer") ?>