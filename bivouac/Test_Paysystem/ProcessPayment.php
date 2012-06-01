<?php
	require_once ("ThePaymentGateway/PaymentSystem.php");

	$rgeplRequestGatewayEntryPointList = new RequestGatewayEntryPointList();
	// you need to put the correct gateway entry point urls in here
	// contact support to get the correct urls

    // The actual values to use for the entry points can be established in a number of ways
    // 1) By periodically issuing a call to GetGatewayEntryPoints
    // 2) By storing the values for the entry points returned with each transaction
    // 3) Speculatively firing transactions at https://gw1.xxx followed by gw2, gw3, gw4....
	// The lower the metric (2nd parameter) means that entry point will be attempted first,
	// EXCEPT if it is -1 - in this case that entry point will be skipped
	// NOTE: You do NOT have to add the entry points in any particular order - the list is sorted
	// by metric value before the transaction sumbitting process begins
	// The 3rd parameter is a retry attempt, so it is possible to try that entry point that number of times
	// before failing over onto the next entry point in the list
	$rgeplRequestGatewayEntryPointList->add("https://gw1.".$PaymentProcessorFullDomain, 100, 2);
	$rgeplRequestGatewayEntryPointList->add("https://gw2.".$PaymentProcessorFullDomain, 200, 2);
	$rgeplRequestGatewayEntryPointList->add("https://gw3.".$PaymentProcessorFullDomain, 300, 2);

	$mdMerchantDetails = new MerchantDetails($MerchantID, $Password);

	$ttTransactionType = new NullableTRANSACTION_TYPE(TRANSACTION_TYPE::SALE);
	$mdMessageDetails = new MessageDetails($ttTransactionType);

	$boEchoCardType = new NullableBool(true);
	$boEchoAmountReceived = new NullableBool(true);
	$boEchoAVSCheckResult = new NullableBool(true);
	$boEchoCV2CheckResult = new NullableBool(true);
	$boThreeDSecureOverridePolicy = new NullableBool(true);
	$nDuplicateDelay = new NullableInt(60);
	$tcTransactionControl = new TransactionControl($boEchoCardType, $boEchoAVSCheckResult, $boEchoCV2CheckResult, $boEchoAmountReceived, $nDuplicateDelay, "",  "", $boThreeDSecureOverridePolicy,  "",  null, null);

	$nAmount = new NullableInt($Amount);
	$nCurrencyCode = new NullableInt($CurrencyISOCode);
	$nDeviceCategory = new NullableInt(0);
	$tdsbdThreeDSecureBrowserDetails = new ThreeDSecureBrowserDetails($nDeviceCategory, "*/*",  $_SERVER["HTTP_USER_AGENT"]);
	$tdTransactionDetails = new TransactionDetails($mdMessageDetails, $nAmount, $nCurrencyCode, $OrderID, $OrderDescription, $tcTransactionControl, $tdsbdThreeDSecureBrowserDetails);

	if ($ExpiryDateMonth != "")
	{
		$nExpiryDateMonth = new NullableInt($ExpiryDateMonth);
	}
	else
	{
		$nExpiryDateMonth = null;
	}
	if ($ExpiryDateYear != "")
	{
		$nExpiryDateYear = new NullableInt($ExpiryDateYear);
	}
	else
	{
		$nExpiryDateYear = null;
	}
	$ccdExpiryDate = new CreditCardDate($nExpiryDateMonth, $nExpiryDateYear);
	if ($StartDateMonth != "")
	{
		$nStartDateMonth = new NullableInt($StartDateMonth);
	}
	else
	{
		$nStartDateMonth = null;
	}
	if ($StartDateYear != "")
	{
		$nStartDateYear = new NullableInt($StartDateYear);
	}
	else
	{
		$nStartDateYear = null;
	}
	$ccdStartDate = new CreditCardDate($nStartDateMonth, $nStartDateYear);
	$cdCardDetails = new CardDetails($CardName, $CardNumber, $ccdExpiryDate, $ccdStartDate, $IssueNumber, $CV2);

	if ($CountryISOCode != "" &&
		$CountryISOCode != -1)
	{
		$nCountryCode = new NullableInt($CountryISOCode);
	}
	else
	{
		$nCountryCode = null;
	}
	$adBillingAddress = new AddressDetails($Address1, $Address2, $Address3, $Address4, $City, $State, $PostCode, $nCountryCode);
	$cdCustomerDetails = new CustomerDetails($adBillingAddress, "test@test.com", "123456789", $_SERVER["REMOTE_ADDR"]);

	$cdtCardDetailsTransaction = new CardDetailsTransaction($rgeplRequestGatewayEntryPointList, 1, null, $mdMerchantDetails, $tdTransactionDetails, $cdCardDetails, $cdCustomerDetails, "Some data to be passed out");
	$boTransactionProcessed = $cdtCardDetailsTransaction->processTransaction($goGatewayOutput, $tomTransactionOutputMessage);

	if ($boTransactionProcessed == false)
	{
		// could not communicate with the payment gateway 
		$NextFormMode = "PAYMENT_FORM";
		$Message = "Grr .Couldn't communicate with payment gateway";
	}
	else
	{
		switch ($goGatewayOutput->getStatusCode())
		{
			case 0:
				// status code of 0 - means transaction successful 
				$NextFormMode = "RESULTS";
				$Message = $goGatewayOutput->getMessage();
				$TransactionSuccessful = true;
				break;
			case 3:
				// status code of 3 - means 3D Secure authentication required 
				$NextFormMode = "THREE_D_SECURE";
				$PaREQ = $tomTransactionOutputMessage->getThreeDSecureOutputData()->getPaREQ();
				$CrossReference = $tomTransactionOutputMessage->getCrossReference();
				$BodyAttributes = " onload=\"document.Form.submit();\"";
				$FormAttributes = " target=\"ACSFrame\"";
				$FormAction = $tomTransactionOutputMessage->getThreeDSecureOutputData()->getACSURL();
				break;
			case 5:
				// status code of 5 - means transaction declined 
				$NextFormMode = "RESULTS";
				$Message=$goGatewayOutput->getMessage();
				$TransactionSuccessful = false;
				break;
			case 20:
				// status code of 20 - means duplicate transaction 
				$NextFormMode = "RESULTS";
				$Message = $goGatewayOutput->getMessage();
				if ($goGatewayOutput->getPreviousTransactionResult()->getStatusCode()->getValue() == 0)
				{
					$TransactionSuccessful = true;
				}
				else
				{
					$TransactionSuccessful = false;
			   	}
				$PreviousTransactionMessage = $goGatewayOutput->getPreviousTransactionResult()->getMessage();
				$DuplicateTransaction = true;
				break;
			case 30:
				// status code of 30 - means an error occurred 
				$NextFormMode = "PAYMENT_FORM";
				$Message = $goGatewayOutput->getMessage();
				if ($goGatewayOutput->getErrorMessages()->getCount() > 0)
				{
					$Message = $Message."<br /><ul>";

					for ($LoopIndex = 0; $LoopIndex < $goGatewayOutput->getErrorMessages()->getCount(); $LoopIndex++)
					{
						$Message = $Message."<li>".$goGatewayOutput->getErrorMessages()->getAt($LoopIndex)."</li>";
					}
					$Message = $Message."</ul>";
					$TransactionSuccessful = false;
				}
				break;
			default:
				// unhandled status code  
				$NextFormMode = "PAYMENT_FORM";
				$Message = $goGatewayOutput->getMessage();
				break;
		}
	}
?>