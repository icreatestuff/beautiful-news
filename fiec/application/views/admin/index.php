<?php 
$data['title'] = "Solid Rock Control Panel";
$header_data['primary'] = "home";
$header_data['secondary'] = "";
$this->load->view("/admin/head", $data); 
$this->load->view("/admin/header", $header_data);
?>
<div id="content" class="centre">
	<h1>Content here</h1>
</div>
<?php
$this->load->view("/admin/footer");
?>