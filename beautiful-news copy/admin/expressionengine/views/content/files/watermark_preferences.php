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

		<div class="heading">
				<h2><?=lang('watermark_prefs')?>
					<?php $this->load->view('_shared/action_nav') ?>
				</h2>
		</div>
		<div class="pageContents">

			<?php $this->load->view('_shared/message');?>
			<div class="clear_left"></div>

			<?php
				$this->table->set_heading(
					lang('wm_name'),
					array('data' => lang('wm_type'), 'width' => '10%'),
					array('data' => lang('edit'), 'width' => '5%'),
					array('data' => lang('delete'), 'width' => '5%')
				);
									
				if ($watermarks->num_rows() > 0)
				{
					foreach ($watermarks->result() as $wm)
					{
						$type = ($wm->wm_type == 'text') ? lang('text') : lang('image');
						$this->table->add_row(
							'<strong>'.$wm->wm_name.'</strong>',
							$type,
							'<a href="'.BASE.AMP.'C=content_files'.AMP.'M=edit_watermark_preferences'.AMP.'id='.$wm->wm_id.'" title="'.lang('edit').'"><img src="'.$cp_theme_url.'images/icon-edit.png" alt="'.lang('edit').'"</a>',
							'<a href="'.BASE.AMP.'C=content_files'.AMP.'M=delete_watermark_preferences_conf'.AMP.'id='.$wm->wm_id.'" title="'.lang('delete').'"><img src="'.$cp_theme_url.'images/icon-delete.png" alt="'.lang('delete').'" /></a>'
						);
					}
				}
				else
				{
					$this->table->add_row(array('data' => lang('no_watermarks'), 'colspan' => 4));
				}
			
				echo $this->table->generate();
			?>
		
		</div>

	</div> <!-- contents -->
</div> <!-- mainContent -->

<?php
if ($EE_view_disable !== TRUE)
{
	$this->load->view('_shared/accessories');
	$this->load->view('_shared/footer');
}

/* End of file watermark_prefs.php */
/* Location: ./themes/cp_themes/default/content/files/watermark_prefs.php */