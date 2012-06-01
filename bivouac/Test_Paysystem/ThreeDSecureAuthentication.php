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

	$tdsidThreeDSecureInputData = new ThreeDSecureInputData($CrossReference, $PaRES);

	$tdsaThreeDSecureAuthentication = new ThreeDSecureAuthentication($rgeplRequestGatewayEntryPointList, 1, null, $mdMerchantDetails, $tdsidThreeDSecureInputData, "Some data to be passed out");
	$boTransactionProcessed = $tdsaThreeDSecureAuthentication->processTransaction($goGatewayOutput, $tomTransactionOutputMessage);

	if ($boTransactionProcessed == false)
	{
		// could not communicate with the payment gateway
		$NextFormMode = "RESULTS";
		$Message = "Couldn't communicate with payment gateway";
		$TransactionSuccessful = false;
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
			case 5:
				// status code of 5 - means transaction declined
				$NextFormMode = "RESULTS";
				$Message = $goGatewayOutput->getMessage();
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
				$NextFormMode = "RESULTS";
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
				$NextFormMode = "RESULTS";
				$Message=$goGatewayOutput->getMessage();
				$TransactionSuccessful = false;
				break;
		}
	}
?>
