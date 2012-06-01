<!doctype html> 
<html class="no-js" lang="en">
<head> 
  <meta charset="utf-8"> 
 
  <title><?php echo $title; ?> &mdash; The Bivouac</title> 
  <meta name="description" content="Short Breaks Yorkshire, Holiday in Yorkshire"> 
  <meta name="author" content="The Bivouac"> 
  <meta name="viewport" content="width=device-width,initial-scale=1"> 
  
  <!--<link rel="stylesheet" href="https://f.fontdeck.com/s/css/6STQZhTRE/f+V0VKoutA4We3IYg/www.thebivouac.co.uk/6763.css">-->
  <link rel="stylesheet" href="http://localhost:8888/bivouac/css/custom-theme/jquery-ui-1.8.16.custom.css" />
  <link rel="stylesheet" href="http://localhost:8888/bivouac/css/jquery.fancybox.css" />
  <link rel="stylesheet" href="http://localhost:8888/bivouac/css/styles.css"> 
 
  <script src="http://localhost:8888/bivouac/js/libs/modernizr-2.0.6.min.js"></script> 
  
</head> 
<body>

<img src="http://localhost:8888/bivouac/images/bivouac_lamp.png" width="169" height="129" id="lamp-illustration">
 
<?php 
$admin = $this->session->userdata('is_admin');
$is_logged_in = $this->session->userdata('is_logged_in');

if (isset($is_logged_in) && $is_logged_in === TRUE)
{
	if (isset($admin) && $admin === "y")
	{
?>

<div class="admin-bar"> 
	<h2>You are logged in as an adminstrator!</h2>
</div>
<?php
	}
}
?>