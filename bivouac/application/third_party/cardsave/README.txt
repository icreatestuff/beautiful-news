PHP Sample Payment Pages
------------------------

To get the sample pages working
-------------------------------
In the file Config.php, you need to edit the following fields:
1) MerchantID & Password - these need to be set to the MerchantID & Password you were issued during signup
2) SiteSecureBaseURL - this needs to be the base URL to the payment pages for your environment - e.g. "https://www.yoursite.com/Pages/". This path MUST include the trailing slash "/"
3) PaymentProcessorDomain & PaymentProcessorPort - these need to be set to the domain and port for your payment processor entry points - e.g. if your payment processor has entry points of the form https://gwX.paymentprocessor.net:4430/, then PaymentProcessorDomain needs to be "paymentprocessor.net" and PaymentProcessorPort needs to be 4430

The entry point for the sample pages is PaymentForm.php
