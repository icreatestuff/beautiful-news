<header> 
	<h1>The Bivouac Booking System Control Panel v1.0</h1>
	<ul id="sites-list">
		<li>
			<?php foreach ($current_site->result() as $row): ?>
				<?php echo $row->name; ?>
			<?php endforeach; ?>
			<ul>
				<?php	if ($sites->num_rows() > 0): ?>
					<?php foreach ($sites->result() as $row):	?>
						<li><a href="#" data-id="<?php echo $row->id; ?>" class="change-site"><?php echo $row->name; ?></li>
					<?php endforeach; ?>
				<?php endif; ?>
				<li class="add-site"><?php echo anchor('admin/sites/new_site/', 'Add Site', array('title' => 'Add Site')); ?></li>
				<li><?php echo anchor('admin/sites/index/', 'Edit Sites', array('title' => 'Edit Site Information')); ?></li>
			</ul>
		</li>
	</ul>
	<?php echo anchor('admin/bookings/logout', 'Logout', array('title' => 'Logout of control panel', 'id' => 'logout')); ?>
</header>