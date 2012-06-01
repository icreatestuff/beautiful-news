<?php
	include ("Config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Untitled Page</title>
    <link href="CSS/StyleSheet.css" rel="stylesheet" type="text/css" />
</head>

<?php
	$BodyAttributes = "";
	$ErrorOccurred = false;
	$Message = "";

	if (isset($_POST['MD']) == false || 
		isset($_POST['PaRes']) == false)
	{
	    $Message = "There were errors collecting the responses back from the ACS";
		$ErrorOccurred = true;
	}
	else
	{
		$BodyAttributes = " onload=\"document.Form.submit();\"";
	}
?>

<body<?= $BodyAttributes ?>>
<?php
	if ($ErrorOccurred == true)
	{
?>
	<div class="ErrorMessage">
		<?= $Message ?>
	</div>
<?php
	}
	else
	{
?>
	<form name="Form" action="<?= $SiteSecureBaseURL ?>PaymentForm.php" method="post" target="_parent">
		<input name="CrossReference" type="hidden" value="<?= $_POST['MD'] ?>" />
		<input name="PaRES" type="hidden" value="<?= $_POST['PaRes'] ?>" />
		<input name="FormMode" type="hidden" value="THREE_D_SECURE" />
	</form>
<?php
	}
?>
</body>
</html>
