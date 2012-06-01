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

		<?php
			// Edit categories from the publish page works by ajax loading
			// this view into itself. The "create new category" link is only
			// available in the right_nav, but the loaded content is entirely
			// contained inside of "pageContents", this the right_nav needs
			// to be available in here if its called from the publish page.
			if ($this->input->get('modal') == 'yes')
			{
				$this->load->view('_shared/right_nav');
			}
		?>

		<div class="clear_left"></div>



		<?php 
			$this->table->set_heading(
										'ID',
										lang('order'),
										lang('category_name'),
										(($can_edit) ? lang('edit') : FALSE),
										(($can_delete) ? lang('delete') : FALSE)
									);
						
			if (count($categories) > 0)
			{
				$up		= '<img src="'.PATH_CP_GBL_IMG.'arrow_up.gif" border="0"  width="16" height="16" alt="" title="" />';
				$down	= '<img src="'.PATH_CP_GBL_IMG.'arrow_down.gif" border="0"  width="16" height="16" alt="" title="" />';

				foreach ($categories as $category)
				{
					$link = '<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=change_category_order'.AMP.'cat_id='.$category['0'].AMP.'group_id='.$group_id.AMP.'order=up" class="edit_cat_order_trigger">'.$up.'</a>'.NBS;
					$link .= '<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=change_category_order'.AMP.'cat_id='.$category['0'].AMP.'group_id='.$group_id.AMP.'order=down" class="edit_cat_order_trigger">'.$down.'</a>';
					$spcr = '<img src="'.PATH_CP_GBL_IMG.'clear.gif" border="0"  width="24" height="14" alt="" title="" />';
					$cat_marker = '<img src="'.PATH_CP_GBL_IMG.'cat_marker.gif" border="0"  width="18" height="14" alt="" title="" />';					
					$indent = ($category['5'] != 1) ? repeater($spcr, $category['5']).$cat_marker : '';

					 $this->table->add_row(
					 	$category['0'],
					 	$link,
					 	$indent.$category['1'],
					 	(($can_edit) ? '<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=category_edit'.AMP.'cat_id='.$category['0'].AMP.'group_id='.$group_id.'">'. lang('edit').'</a>' : NULL),
					 	(($can_delete) ? '<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=category_delete_conf'.AMP.'cat_id='.$category['0'].AMP.'group_id='.$group_id.'">'. lang('delete').'</a>' : NULL)
					 );
				}
			}
			else
			{
				$this->table->add_row(array('data' => lang('no_category_message'), 'colspan' => 5));
			}
			
			echo $this->table->generate();
		?>
		
		<?php if (count($categories) > 0):?>
	
<div class='defaultSmall' ></div>


	
<?=form_open($form_action)?>

<div class='box320' >
<div class='defaultBold'><?=lang('global_sort_order')?></div>
<div class='itemWrapper'>
<?=form_radio('sort_order', 'a', (($sort_order == 'a') ? TRUE : FALSE)).NBS.lang('alpha').NBS.NBS.form_radio('sort_order', 'c', (($sort_order == 'c') ? TRUE : FALSE)).NBS.lang('custom').NBS.NBS.NBS?>
<?=form_submit('submit', lang('update'), 'class="submit"')?>	

</div>
</div>
	<?=form_close()?>
		
	<?php endif;?>	
		
		</div>


	</div> <!-- contents -->
</div> <!-- mainContent -->

<?php
if ($EE_view_disable !== TRUE)
{
	$this->load->view('_shared/accessories');
	$this->load->view('_shared/footer');
}

/* End of file category_editor.php */
/* Location: ./themes/cp_themes/default/admin/category_editor.php */