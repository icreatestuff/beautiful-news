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
			<h2><?=lang('create_new_template')?></h2>
	</div>

	<div class="pageContents">
		<?=form_open('C=design'.AMP.'M=create_new_template'.AMP.'tgpref='.$form_hidden['group_id'], '', $form_hidden)?>
		<?php
			$this->table->set_template($cp_pad_table_template);
			$this->table->set_heading(
				array('data' => lang('preference'), 'style' => 'width:50%;'),
				lang('setting')
			);

		// Name of Template
		$template_name = array(
			'id'		=> 'template_name',
			'name'		=> 'template_name',
			'size'		=> 30,
			'maxlength' => 50
		);

		$this->table->add_row(array(
				lang('name_of_template', 'name_of_template').'<br />'.
				lang('template_group_instructions').' '.lang('undersores_allowed'),
				form_input($template_name)
			)
		);

		$this->table->add_row(array(
				lang('template_type', 'template_type'),
				form_dropdown('template_type', $template_types)
			)
		);

		// Default Template Data
		$this->table->add_row(array(
				lang('duplicate_existing_template', 'duplicate_existing_template'),
				form_dropdown('existing_template', $templates)
			)
		);

		echo $this->table->generate();
		?>
		<p>
			<?=form_submit('create', lang('create'), 'class="submit"')?> 
			<?=form_submit('create_and_edit', lang('create_and_edit'), 'class="submit"')?> 
		</p>

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

/* End of file new_template.php */
/* Location: ./themes/cp_themes/default/design/new_template.php */