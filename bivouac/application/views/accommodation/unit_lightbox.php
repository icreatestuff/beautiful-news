<?php 
$data['title'] = $accommodation->name . " | Bivouac Accommodation";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="unit-info" class="clearfix">
	<div class="unit-copy">
		<h2><?php echo nl2br($accommodation->description); ?></h2>
		
		<div class="clearfix">
			<ul class="sleeping-info">
				<li><b>Sleeps: <?php echo $accommodation->sleeps; ?><?php if ($accommodation->type !== '3') { echo " + one cot"; } ?></b></li>
				<?php if ($accommodation->type !== '3'): ?>
				<li><b>Double beds: x <?php if ($accommodation->type === '1') { echo " 2"; } else { echo " 1"; } ?></b></li>
				<li><b>Single beds: x 3</b></li>
				<?php else: ?>
				<li><b>Access to firepit</b></li>
				<?php endif; ?>
			</ul>
			
			<ul class="access-to">
				<li><b>Hot tub for hire</b></li>
				<?php if ($accommodation->type !== '3'): ?>
				<li><b>Access to firepit</b></li>
				<?php endif; ?>
			</ul>
		</div>
		
		<ul class="amenities">
			<?php 
				$amenities = explode("\n", $accommodation->amenities); 
				foreach ($amenities as $item):
			?>
				<li><?php echo $item; ?></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<ul class="accommodation-images">
	<?php if (!empty($accommodation->photo_2)): ?>
		<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_2, 360); ?></li>
	<?php endif; ?>
	<?php if (!empty($accommodation->photo_4)): ?>
		<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_4, 360); ?></li>
	<?php endif; ?>
	<?php if (!empty($accommodation->photo_5)): ?>
		<li><?php echo size(base_url() . 'images/accommodation/' . $accommodation->photo_5, 360); ?></li>
	<?php endif; ?>
	</ul>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>