<?php 
$data['title'] = "Churches";
$header_data['primary'] = "churches";
$header_data['secondary'] = "all";
$this->load->view("/admin/head", $data); 
$this->load->view("/admin/header", $header_data);
?>
<div id="content" class="centre">
	<h1>Churches</h1>
	
	<!-- Quicksearch for rapid filtering down -->
	<form class="quicksearch" action="" method="post">
		<div class="form-field">
			<label for="quicksearch">Churches will filter down as you type</label>
			<input type="text" name="quicksearch" id="quicksearch" value="">
		</div>
	</form>
	
	<table>
		<tbody>
			<?php if ($churches->num_rows() > 0): ?>
				<?php foreach ($churches->result() as $church): ?>
					<tr>
						<td><?php echo $churches->fiec_filing_number; ?></td>
						<td><?php echo $churches->name; ?></td>
						<td><?php echo $churches->office_tel_number; ?></td>
						<td><a href="mailto:<?php echo $churches->office_email_address; ?>" title="Email <?php echo $churches->name; ?>"><?php echo $churches->office_email_address; ?></a></td>
						<td>
							123 Old Lane,<br>
							Colchester,<br>
							Warwickshire,<br>
							NG14 5TY<br>
						</td>
						<td>Holy People Group</td>
						<td>Midlands</td>
						<td>
							<?php echo anchor('admin/churches/church/' . $churches->id, 'View full Church details', array('title' => $churches->name . ' profile')); ?><br>
							<?php echo anchor('admin/churches/edit_church/' . $churches->id , 'Edit Church', array('title' => 'Edit ' . $churches->name)); ?><br>
							<a href="#" class="delete" title="Delete <?php echo $churches->name; ?>">Delete Record</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<thead>
			<tr>
				<th>FIEC Filing No.</th>
				<th>Name</th>
				<th>Contact No.</th>
				<th>Email Address</th>
				<th>Address</th>
				<th>Group</th>
				<th>Region</th>
				<th>Actions</th>
			</tr>
		</thead>	
	</table>
</div>
<?php
$this->load->view("/admin/footer");
?>