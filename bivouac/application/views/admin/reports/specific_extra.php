<?php 
$data['title'] = "Bookings requesting specific extra";
$data['location'] = "reports";
$this->load->view("_includes/admin/head", $data); 
?>
	<h1>Show Bookings that have requested a specific extra</h1>
	
	<?php echo form_open('admin/reports/specific_extra', array('id' => 'specific-extra-form', 'class' => 'admin-form')); ?>
		<p>
			<label for="extra">Please select an extra</label>
			<select name="extra" id="extra">
				<option value="--">Please select</option>
			
				<?php foreach ($extras->result() as $extra): ?>
				<option value="<?php echo $extra->id; ?>"><?php echo $extra->name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		
		<input type="submit" name="submit" id="submit" value="Get bookings that have requested this extra" />
	</form>
	
	<?php if (isset($bookings) && count($bookings) > 0): ?>
	<table>
		<tbody>
			<?php foreach($bookings as $booking): ?>
			<tr>
				<td><?php echo $booking['booking_ref']; ?></td>
				<td>
					<?php 
						if (count($booking['accommodation']) > 0): 
							foreach ($booking['accommodation'] as $name):
					?>
						<?php echo $name->row()->name . "<br />"; ?>
					<?php 
							endforeach;
						endif; 
					?>
				</td>
				<td><?php echo $booking['extra_name']; ?></td>
				<td><?php echo $booking['quantity']; ?></td>
				<td><?php echo date('d/m/Y', strtotime($booking['start_date'])); ?></td>
				<td><?php echo date('d/m/Y', strtotime($booking['end_date'])); ?></td>
				<td><?php echo $booking['adults']; ?></td>
				<td><?php echo $booking['children']; ?></td>
				<td><?php echo $booking['babies']; ?></td>
				<td><?php echo $booking['total_price']; ?></td>
				<td><?php echo anchor('admin/bookings/overview/' . $booking['id'], 'View Full Details', array('title' => 'View Full Booking Details')); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<thead>
			<tr>
				<th>Booking Ref</th>
				<th>Accommodation</th>
				<th>Extra Name</th>
				<th>Extra Quantity</th>
				<th>Arrival Date</th>
				<th>Departure Date</th>
				<th>Adults</th>
				<th>4-17's</th>
				<th>0-3's</th>
				<th>Price</th>
				<th>Actions</th>
			</tr>
		</thead>
	</table>
	<?php else: ?>
	<p>There are no bookings that have requested the selected extra.</p>
	<?php endif; ?>
	
	<?php if (isset($extra_id) && !empty($extra_id)): ?>
		<p><a href="#" class="export-xls" data-title="Bookings with '<?php echo $extra_name; ?>'" data-model_function="get_bookings_with_extra" data-secondary="<?php echo $extra_id; ?>">Export .xls</a>
		<div id="result"></div>
	<?php endif; ?>
	
	<p><?php echo anchor('admin/reports/index/', 'View all reports', array('title' => 'View all reports')); ?></p>
<?php $this->load->view("_includes/admin/footer") ?>