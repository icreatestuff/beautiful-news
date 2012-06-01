<?php 
$data['title'] = "Login to your account";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="container" class="clearfix">
	<div class="login-container">
		<h1>Login to your account</h1>

		<?php
			$flashdata = $this->session->flashdata('user_message');
			if (isset($flashdata) && !empty($flashdata)): 
		?>
			<div class="user-message">
				<?php echo $flashdata; ?>
			</div>
		<?php endif; ?>
	
		<?php $this->load->view("_includes/frontend/login_form"); ?>
	</div>
</div>
<?php $this->load->view("_includes/frontend/js");