<header class="centre clearfix">
	<h1>FIEC 'Solid Rock'</h1>
	
	<nav>
		<ul class="clearfix">
			<li>
				<?php 
					if ($primary === "people")
					{
						echo anchor('admin/people', 'People', array('title' => 'Manage People', 'class' => 'active')); 
					}
					else
					{
						echo anchor('admin/people', 'People', array('title' => 'Manage People')); 
					}
				?>
			</li>
			<li>
				<?php 
					if ($primary === "churches")
					{
						echo anchor('admin/churches', 'Churches', array('title' => 'Manage Churches', 'class' => 'active')); 
					}
					else
					{
						echo anchor('admin/churches', 'Churches', array('title' => 'Manage Churches')); 
					}
				?>
			</li>
			<li>
				<?php 
					if ($primary === "home")
					{
						echo anchor('admin/home', 'Home', array('title' => 'Control Panel Homepage', 'class' => 'active')); 
					}
					else
					{
						echo anchor('admin/home', 'Home', array('title' => 'Control Panel Homepage')); 
					}
				?>
			</li>
		</ul>
	</nav>	
</header>
<?php if ($primary === "churches"): ?>
	<ul id="secondary-nav" class="centre clearfix">	
		<li>
			<?php 
				if ($secondary === "church-regions")
				{
					echo anchor('admin/churches/regions', 'Church Regions', array('title' => 'View all Church Regions', 'class' => 'active')); 
				}
				else
				{
					echo anchor('admin/churches/regions', 'Church Regions', array('title' => 'View all Church Regions')); 
				}
			?>
		</li>
		
		<li>
			<?php 
				if ($secondary === "church-groups")
				{
					echo anchor('admin/churches/groups', 'Church Groups', array('title' => 'View all Church Groups', 'class' => 'active')); 
				}
				else
				{
					echo anchor('admin/churches/groups', 'Church Groups', array('title' => 'View all Church Groups')); 
				}
			?>
		</li>
	
		<li>
			<?php 
				if ($secondary === "add-church")
				{
					echo anchor('admin/churches/create', 'Add Church', array('title' => 'Add a new Church', 'class' => 'active')); 
				}
				else
				{
					echo anchor('admin/churches/create', 'Add Church', array('title' => 'Add a new Church')); 
				}
			?>
		</li>
		
		<li>
			<?php 
				if ($secondary === "all")
				{
					echo anchor('admin/churches', 'All Churches', array('title' => 'View all Churches', 'class' => 'active')); 
				}
				else
				{
					echo anchor('admin/churches', 'All Churches', array('title' => 'View all Churches')); 
				}
			?>
		</li>
	</ul>
<?php endif; ?>