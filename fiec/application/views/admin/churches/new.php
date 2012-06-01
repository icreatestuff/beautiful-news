<?php 
$data['title'] = "Create a new Church | Churches";
$header_data['primary'] = "churches";
$header_data['secondary'] = "add-church";
$this->load->view("/admin/head", $data); 
$this->load->view("/admin/header", $header_data);
?>
<div id="content" class="centre">
	<h1>Add a new Church</h1>
	
	<?php echo validation_errors(); ?>

	<?php echo form_open('form'); ?>
	
		<!-- Basic Church Info Fields -->
		<fieldset>
			<legend>Basic Information:</legend>
			
			<div class="form-field">
				<label for="fiec_filing_number">Filing Number</label>
				<input type="text" name="fiec_filing_number" id="fiec_filing_number" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="charity_registration_number">Charity Registration Number</label>
				<input type="text" name="charity_registration_number" id="charity_registration_number" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="year_of_formation">Year of Formation</label>
				<input type="text" name="year_of_formation" id="year_of_formation" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="description">Description</label>
				<textarea name="description" id="description" cols="50" rows="7"></textarea>
			</div>
			
			<div class="form-field">
				<label for="additional_info">Additional Information</label>
				<textarea name="additional_info" id="additional_info" cols="50" rows="7"></textarea>
			</div>
			
			<div class="form-field">
				<label for="total_members">Total Members</label>
				<input type="text" name="total_members" id="total_members" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="membership_category_id">Membership Category</label>
				<select name="membership_category_id" id="membership_category_id">
					<option value="0">0</option>
					<option value="1">1 &ndash; 50</option>
					<option value="2">51 &ndash; 100</option>
					<option value="3">101 &ndash; 150</option>
					<option value="4">151 &ndash; 200</option>
					<option value="5">201 &ndash; 250</option>
				</select>
			</div>
			
			<div class="form-field">
				<label for="region_id">Region</label>
				<select name="region_id" id="region_id">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</div>
			
			<div class="form-field">
				<label for="group_id">Church Group</label>
				<select name="group_id" id="group_id">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select>
			</div>
		</fieldset>	
	
		<!-- Administrative Fields -->
		<fieldset>
			<legend>Administrative Information:</legend>
			
			<div class="form-field">
				<label for="year_of_affiliation">Year of affiliation</label>
				<input type="text" name="year_of_affiliation" id="year_of_affiliation" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="employment_protection_scheme">Employment Protection Scheme</label>
				<input type="checkbox" name="employment_protection_scheme" id="employment_protection_scheme" value="y" size="50"> Yes
				<input type="checkbox" name="employment_protection_scheme" id="employment_protection_scheme" value="n" size="50"> No
			</div>
			
			<div class="form-field">
				<label for="trust_holding">Trust Holding</label>
				<input type="checkbox" name="trust_holding" id="trust_holding" value="y" size="50"> Yes
				<input type="checkbox" name="trust_holding" id="trust_holding" value="n" size="50"> No
			</div>
			
			<div class="form-field">
				<label for="declaration_last_signed">Declaration Last Signed</label>
				<input type="text" name="declaration_last_signed" id="declaration_last_signed" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="annual_donation_year">Annual Donation Year</label>
				<input type="text" name="annual_donation_year" id="annual_donation_year" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="annual_donation_by_standing_order">Annual Donation by standing order</label>
				<input type="checkbox" name="annual_donation_by_standing_order" id="annual_donation_by_standing_order" value="y" size="50"> Yes
				<input type="checkbox" name="annual_donation_by_standing_order" id="annual_donation_by_standing_order" value="n" size="50"> No
			</div>
			
			<div class="form-field">
				<label for="general_comments">General Comments</label>
				<textarea name="general_comments" id="general_comments" cols="50" rows="7"></textarea>
			</div>
			
			<div class="form-field">
				<label for="contact_comments">Contact Comments</label>
				<textarea name="contact_comments" id="contact_comments" cols="50" rows="7"></textarea>
			</div>
		</fieldset>
		
	
		<!-- Contact Info Fields -->
		<fieldset>
			<legend>Church Contact Information:</legend>
			
			<div class="form-field">
				<label for="office_tel_number">Telephone Number</label>
				<input type="text" name="office_tel_number" id="office_tel_number" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="office_email_address">Email Address</label>
				<input type="text" name="office_email_address" id="office_email_address" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="website_url">Website URL</label>
				<input type="text" name="website_url" id="website_url" value="" size="50">
			</div>
		</fieldset>
		
		<!-- Image Fields -->
		<fieldset>
			<legend>Church Images:</legend>
			
			<div class="form-field">
				<label for="logo">Logo</label>
				<input type="file" name="logo" id="logo" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="photo_1">Photo 1</label>
				<input type="file" name="photo_1" id="photo_1" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="photo_2">Photo 2</label>
				<input type="file" name="photo_2" id="photo_2" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="photo_3">Photo 3</label>
				<input type="file" name="photo_3" id="photo_3" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="photo_4">Photo 4</label>
				<input type="file" name="photo_4" id="photo_4" value="" size="50">
			</div>
		</fieldset>
	
		<!-- Social Media Fields -->
		<fieldset>
			<legend>Social Media:</legend>
	
			<div class="form-field">
				<label for="facebook_page_url">Facebook Page URL</label>
				<input type="text" name="facebook_page_url" id="facebook_page_url" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="twitter_profile_url">Twitter Profile URL</label>
				<input type="text" name="twitter_profile_url" id="twitter_profile_url" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="linkedin_profile_url">LinkedIn Profile URL</label>
				<input type="text" name="linkedin_profile_url" id="linkedin_profile_url" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="vimeo_url">Vimeo URL</label>
				<input type="text" name="vimeo_url" id="vimeo_url" value="" size="50">
			</div>
			
			<div class="form-field">
				<label for="youtube_url">Youtube URL</label>
				<input type="text" name="youtube_url" id="youtube_url" value="" size="50">
			</div>
		</fieldset>
	
		<input type="submit" value="Submit" class="submit">
	
	</form>
	
</div>
<?php
$this->load->view("/admin/footer");
?>