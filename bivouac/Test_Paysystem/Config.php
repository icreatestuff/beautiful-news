<?php
	// Will need to set these variables to valid a MerchantID and Password
	// These were obtained during sign up
	$MerchantID = "TheBiv-8839405";
	$Password = "1L147V74M8";
	
	// Will need to put a valid path here for where the payment pages reside 
	// e.g. https://www.yoursitename.com/Pages/ 
	// NOTE: This path MUST include the trailing "/" 
	$SiteSecureBaseURL = "http://booking.thebivouac.co.uk/booking/payment/";

	// This is the domain (minus any host header or port number for your payment processor
	// e.g. for "https://gwX.paymentprocessor.net:4430/", this should just be "paymentprocessor.net"
	$PaymentProcessorDomain = "cardsaveonlinepayments.com";
	// This is the port that the gateway communicates on
	$PaymentProcessorPort = 4430;

	if ($PaymentProcessorPort == 443)
	{
		$PaymentProcessorFullDomain = $PaymentProcessorDomain."/";
	}
	else
	{
		$PaymentProcessorFullDomain = $PaymentProcessorDomain.":".$PaymentProcessorPort."/";
	}
?>