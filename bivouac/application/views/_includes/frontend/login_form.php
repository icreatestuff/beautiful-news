<?php echo form_open('account/validate_user', array('id' => 'login_form')); ?>
	<input type="hidden" name="return" value="<?php echo uri_string(); ?>" />
	
	<p>
		<label for="username">Username/Email Address</label>
		<input type="text" name="username" id="username" value="" />
	</p>
	
	<p>
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="" />
	</p>
	
	<input type="submit" name="submit" id="submit" value="Log in" />
	<p><a href="http://booking.thebivouac.co.uk/account/forgot_password" title="Forgot your password?">forgot your password?</a></p>

</form>