<?php 
$data['title'] = "Create a new Church Region | Churches";
$header_data['primary'] = "churches";
$header_data['secondary'] = "church-regions";
$this->load->view("/admin/head", $data); 
$this->load->view("/admin/header", $header_data);
?>
<div id="content" class="centre">
	<h1>Church Regions</h1>
	
	<?php echo form_open('form'); ?>
		<table>
			<tbody>
				<tr>
					<td>North England</td>
					<td>Director.... name</td>
				</tr>
				<tr>
					<td><input type="text" name="name" id="name" value="" size="50"></td>
					<td>
						<select name="director_id" id="director_id">
							<option value="0">0</option>
							<option value="1">Iron Man</option>
							<option value="2">The Incredible Hulk</option>
							<option value="3">Hawkeye</option>
							<option value="4">Captain America</option>
							<option value="5">Captain America</option>
						</select>
					</td>
				</tr>
			</tbody>
			<thead>
				<tr>
					<th>Region Name</th>
					<th>Director</th>
				</tr>
			</thead>	
		</table>
		
		<input type="submit" value="Submit" class="submit">
	</form>
	
	<?php echo validation_errors(); ?>
	
</div>
<?php $this->load->view("/admin/footer"); ?>