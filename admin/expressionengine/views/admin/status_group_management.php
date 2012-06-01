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

		<div class="heading"><h2 class="edit"><?=lang('statuses')?></h2></div>
		<div class="pageContents">

		<?php $this->load->view('_shared/message');?>
		<div class="clear_left"></div>

		<?php
			$this->table->set_heading(
										lang('group_name'),
										'',
										''
									);
									
			if ($status_groups->num_rows() > 0)
			{
				foreach ($status_groups->result() as $status)
				{
					$this->table->add_row(
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=status_management'.AMP.'group_id='.$status->group_id.'">'.$status->group_name.'</a> ('.$status->count.')',
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=status_group_edit'.AMP.'group_id='.$status->group_id.'">'.lang('rename').'</a>',
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=status_group_delete_confirm'.AMP.'group_id='.$status->group_id.'">'.lang('delete').'</a>'
					);
				}
			}
			else
			{
				$this->table->add_row(array('data' => lang('no_status_group_message'), 'colspan' => 5));
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

/* End of file status_group_management.php */
/* Location: ./themes/cp_themes/default/admin/status_group_management.php */