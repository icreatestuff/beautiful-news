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

		<div class="heading"><h2 class="edit"><?=$cp_page_title?></h2></div>
		<div class="pageContents">

			<?php $this->load->view('_shared/message');?>
		<div class="clear_left"></div>
		
		<?php
			$this->table->set_heading(
										lang('group_name'),
										'',
										''
									);
									
			if ($field_groups->num_rows() > 0)
			{
				foreach ($field_groups->result() as $field)
				{
					$this->table->add_row(
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=field_management'.AMP.'group_id='.$field->group_id.'">'.$field->group_name.'</a> ('.$field->count.')', 
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=field_group_edit'.AMP.'group_id='.$field->group_id.'">'.lang('rename').'</a>',
						'<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=field_group_delete_confirm'.AMP.'group_id='.$field->group_id.'">'.lang('delete').'</a>'
					);
				}
			}
			else
			{
				$this->table->add_row(array('data' => lang('no_field_group_message'), 'colspan' => 5));
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

/* End of file field_group_management.php */
/* Location: ./themes/cp_themes/default/admin/field_group_management.php */