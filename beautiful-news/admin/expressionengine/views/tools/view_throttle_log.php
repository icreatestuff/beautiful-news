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

			<div class="heading"><h2 class="edit">
			<span id="filter_ajax_indicator" style="visibility:hidden; float:right;"><img src="<?=$cp_theme_url?>images/indicator2.gif" style="padding-right:20px;" /></span>			
			<?=$cp_page_title?></h2></div>
			<div class="pageContents">
				
			<?php $this->load->view('_shared/message'); ?>

				<?php if($blacklist_installed): ?>
				<div class="cp_button"><a href="<?=BASE.AMP.'C=tools_logs'.AMP.'M=blacklist_throttled_ips'?>"><?=lang('blacklist_all_ips')?></a></div>
				<div class="clear_left"></div>
				<?php endif;?>
				
			<?php if ($this->config->item('enable_throttling') == 'n'):?>
					<p><?=lang('throttling_disabled')?></p>
			<?php else:
				echo $table_html;
				echo $pagination_html;
			endif;?>

			</div>

	</div> <!-- contents -->
</div> <!-- mainContent -->

<?php
if ($EE_view_disable !== TRUE)
{
	$this->load->view('_shared/accessories');
	$this->load->view('_shared/footer');
}

/* End of file view_throttle_log.php */
/* Location: ./themes/cp_themes/default/tools/view_throttle_log.php */