<?php
if ($EE_view_disable !== TRUE)
{
	$this->load->view('_shared/header');
	$this->load->view('_shared/main_menu');
	$this->load->view('_shared/sidebar');
	$this->load->view('_shared/breadcrumbs');
}
?>

<div id="mainContent"<?=$maincontent_state?>>
	<?php $this->load->view('_shared/right_nav')?>
		<div class="contents">

			<div class="heading"><h2 class="edit"><?=lang('confirm_field_assignment')?></h2></div>
			<div class="pageContents">

			<?=validation_errors(); ?>
	
			<?=form_open('C=tools_utilities'.AMP.'M=create_xml', '', $form_hidden)?>
	
			<p><?=lang('confirm_field_assignment_blurb')?></p>		

			<?php

			$heading[] = lang('your_data');
			$heading[] = lang('member_fields');
						
			if ($custom_fields)
			{
				$heading[] = lang('custom_member_fields');
			}			
			
			$this->table->set_template($cp_pad_table_template);
			$this->table->set_heading($heading);

			if ($custom_fields)
			{
				foreach ($fields[0] as $key => $value)
				{
					$this->table->add_row(
										$value,
										$paired['field_'.($key)],
										$cpaired['c_field_'.($key)]
									);
				}
			}
			else
			{
				foreach ($fields[0] as $key => $value)
				{
					$this->table->add_row(
										$value,
										$paired['field_'.($key)]
									);
				}				
			}
			?>

			<?=$this->table->generate()?>
			
			<?php if ($form_hidden['encrypt'] == TRUE): ?>
			<p><?=lang('plaintext_passwords')?></p>
			<?php else:?>
			<p><?=lang('encrypted_passwords')?></p>
			<?php endif;?>

			<p class="field_format_option select_format">
					<?=form_radio('type', 'view', $type_view)?>
					<?=lang('view_in_browser', 'type_view')?><br />
					<?=form_radio('type', 'download', $type_download)?>
					<?=lang('download', 'type_download')?>
				</p>

			<p><?=form_submit('create_xml', lang('create_xml'), 'class="submit"')?></p>

			<?=form_close()?>

			</div>

	</div> <!-- contents -->
</div> <!-- mainContent -->

<?php
if ($EE_view_disable !== TRUE)
{
	$this->load->view('_shared/accessories');
	$this->load->view('_shared/footer');
}

/* End of file confirm_convert_xml.php */
/* Location: ./themes/cp_themes/default/tools/confirm_convert_xml.php */