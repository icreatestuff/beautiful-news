<?php 
$data['title'] = "Login";
$this->load->view("_includes/admin/head", $data); 
?>
<div id="wrapper">
	<div id="login-container">
		<h2>Login</h2> 
		<?php echo form_open('admin/members/validate_user', array('id' => 'loginForm')); ?>
			<p>
				<label for="username">Username</label> 
				<input type="text" name="username" id="username" />
			</p>  
			<p>
				<label for="password">Password</label> 
				<input type="password" name="password" id="password" />
			</p> 
			<p>
				<input type="submit" name="submit" id="login-submit" value="Login" class="submit" /> 
			</p>
		</form>
	</div>
</div>
<?php $this->load->view("_includes/admin/footer"); ?>