<aside>
	<h2>Navigation</h2>
	<nav> 
		<ul id="nav"> 
			<li> 
				<?php 
					if ($location === "admin")
					{ 
						echo anchor('admin/bookings', 'Bookings', array('title' => 'View bookings overview', 'class' => 'active')); 
					}
					else
					{
						echo anchor('admin/bookings', 'Bookings', array('title' => 'View bookings overview'));
					}	
				?> 
			</li> 
			<li> 
				<?php 
					if ($location === "reports")
					{ 
						echo anchor('admin/reports', 'Reports', array('title' => 'View Reports', 'class' => 'active')); 
					}
					else
					{
						echo anchor('admin/reports', 'Reports', array('title' => 'View Reports'));
					}	
				?> 
			</li> 
			<li> 
				<?php 
					if ($location === "accommodation")
					{
						echo anchor('admin/accommodation', 'Accommodation', array('title' => 'Manage accommodation', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/accommodation', 'Accommodation', array('title' => 'Manage accommodation'));
					}
				?>
			</li> 
			<li> 
				<?php 
					if ($location === "extras")
					{
						echo anchor('admin/extras', 'Extras', array('title' => 'Manage Extras', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/extras', 'Extras', array('title' => 'Manage Extras')); 
					}		
				?>
			</li> 
			<li> 
				<?php 
					if ($location === "pricing")
					{
						echo anchor('admin/pricing', 'Pricing', array('title' => 'Manage Prices', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/pricing', 'Pricing', array('title' => 'Manage Prices')); 
					}		
				?>
			</li>  
			<li> 
				<?php 
					if ($location === "holidays")
					{
						echo anchor('admin/holidays', 'Public Holidays', array('title' => 'Manage Public Holidays', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/holidays', 'Public Holidays', array('title' => 'Manage Public Holidays')); 
					}		
				?>
			</li> 
			<li> 
				<?php 
					if ($location === "closed")
					{
						echo anchor('admin/site_closed', 'Site Closed Dates', array('title' => 'Site Closed Dates', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/site_closed', 'Site Closed Dates', array('title' => 'Site closed Dates')); 
					}		
				?>
			</li> 
			<li> 
				<?php 
					if ($location === "offers")
					{
						echo anchor('admin/offers', 'Offers', array('title' => 'Manage Last Minute Offers', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/offers', 'Offers', array('title' => 'Manage Last Minute Offers')); 
					}		
				?>
			<li> 
				<?php 
					if ($location === "vouchers")
					{
						echo anchor('admin/vouchers', 'Voucher Codes', array('title' => 'Manage Voucher Codes', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/vouchers', 'Voucher Codes', array('title' => 'Manage Voucher Codes')); 
					}		
				?>
			</li>
			<li> 
				<?php 
					if ($location === "weddings")
					{
						echo anchor('admin/weddings', 'Weddings', array('title' => 'Manage Wedding Bookings', 'class' => 'active'));
					}
					else
					{
						echo anchor('admin/weddings', 'Weddings', array('title' => 'Manage Wedding Bookings')); 
					}		
				?>
			</li> 
		</ul> 
	</nav> 
</aside>