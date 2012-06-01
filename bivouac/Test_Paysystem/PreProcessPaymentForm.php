<?php
	foreach ($_POST as $field => $value) 
	{
		$$field = $value;
	}

	$ResetFormVariables = false;
	$BodyAttributes = "";
	$FormAttributes = "";
	$FormAction = "PaymentForm.php";

	// Is this a postback? 
	if (!isset($FormMode))
	{
		$ResetFormVariables = true;
		$NextFormMode = "PAYMENT_FORM";	
	}
	else
	{
		// do we try to process the payment? 
		switch ($FormMode)
		{
			case "PAYMENT_FORM":
				// have just come from a payment form - try to process the payment
				include ("ProcessPayment.php");
				break;
			case "RESULTS":
				$ResetFormVariables = true;
				$NextFormMode = "PAYMENT_FORM";	
				break;
			case "THREE_D_SECURE":
				// have just come from a payment form - try to process the payment
				include ("ThreeDSecureAuthentication.php");
				break;
		}
	}

	// Reset the form variables if required 
	if ($ResetFormVariables == true)
	{
		$CardName = "";
		$CardNumber = "";
		$ExpiryDateMonth = "";
		$ExpiryDateYear = "";
		$StartDateMonth = "";
		$StartDateYear = "";
		$IssueNumber = "";
		$CV2 = "";
		$Address1 = "";
		$Address2 = "";
		$Address3 = "";
		$Address4 = "";
		$City = "";
		$State = "";
		$PostCode = "";
		$CountryISOCode = "";
	}
?>
