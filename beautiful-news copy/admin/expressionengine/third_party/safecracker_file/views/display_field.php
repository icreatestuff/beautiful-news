<div class="safecracker_file_set">
	<?php if ($data): ?>
		<div class="safecracker_file_thumb">
			<a href="#" class="safecracker_file_remove_button"><img src="<?= $this->config->item('theme_folder_url'); ?>cp_themes/default/images/write_mode_close.png" /></a>
			<img src="<?= $thumb_src; ?>" />
			<p><?= $data; ?></p>
		</div>
		<div class="safecracker_file_remove" style="display:none;"><?= $remove; ?></div>
		<div class="clear"></div>
	<?php endif; ?>

	<div class="safecracker_file_hidden"><?= $hidden; ?></div>
	<div class="safecracker_file_placeholder_input"><?= $placeholder_input; ?></div>
	<div class="safecracker_file_input<?php if ($data): ?> js_hide<?php endif; ?>">
		<?php if ($data): ?>
			<a href="#" class="safecracker_file_undo_button">&larr; Undo Remove</a>
		<?php endif; ?>
		<?= $upload; ?>
	</div>
	
	<?php if ( ! empty($settings['safecracker_show_existing'])) : ?>
		<div class="safecracker_file_existing<?php if ($data): ?> js_hide<?php endif; ?>">
			<?=form_dropdown($existing_input_name, $existing_files)?>
		</div>
	<?php endif; ?>
</div>