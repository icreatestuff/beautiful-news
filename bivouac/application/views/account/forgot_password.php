<?php 
$data['title'] = "Reset your password";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="container" class="clearfix">
	<div class="login-container">
		<h1>Reset your password</h1>

		<?php
			$flashdata = $this->session->flashdata('user_message');
			if (isset($flashdata) && !empty($flashdata)): 
		?>
			<div class="user-message">
				<?php echo $flashdata; ?>
			</div>
		<?php endif; ?>
	
		<?php echo form_open('account/forgot_password', array('id' => 'login_form')); ?>
			<p>
				<label for="email_address">Please enter the email address associated with your account</label>
				<input type="text" name="email_address" id="email_address" value="" />
			</p>
			
			<input type="submit" name="submit" id="submit" value="Reset Password" />	
		</form>
	</div>
</div>
<?php $this->load->view("_includes/frontend/js");