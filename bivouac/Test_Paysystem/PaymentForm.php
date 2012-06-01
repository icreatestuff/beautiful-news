<?php
	require_once ("Config.php");
	require_once ("ISOCountries.php");
	require_once ("PreProcessPaymentForm.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Untitled Page</title>
    <link href="CSS/StyleSheet.css" rel="stylesheet" type="text/css" />
</head>

<body<?= $BodyAttributes ?>>
	<div style="width:800px;margin:auto">
    	<form name="Form" action="<?= $FormAction ?>" method="post"<?= $FormAttributes ?>>
<?php
	switch ($NextFormMode)
	{
		case "RESULTS":
?>
			<input name="FormMode" type="hidden" value="<?= $NextFormMode ?>" />
<?php
			if (isset($DuplicateTransaction) != true)
			{
				$DuplicateTransaction=false;							
			}
			if ($TransactionSuccessful == false)
			{
				$MessageClass = "ErrorMessage";
			}
			else
			{
				$MessageClass = "SuccessMessage";
			}
?>
			<div class="<?= $MessageClass ?>">
				<div class="TransactionResultsItem">
					<div class="TransactionResultsLabel">Payment Processor Response:</div>
					<div class="TransactionResultsText">
						<?= $Message ?>
					</div>
				</div>
<?php
			if ($DuplicateTransaction == true)
			{
?>
				<div style="color:#000;margin-top:10px">
					A duplicate transaction means that a transaction with these details
					has already been processed by the payment provider. The details of
					the original transaction are given below
				</div>
				<div class="TransactionResultsItem" style="margin-top:10px">
					<div class="TransactionResultsLabel">
						Previous Transaction Response:
					</div>
					<div class="TransactionResultsText">
						<?= $PreviousTransactionMessage ?>
					</div>
				</div>
<?php
			}
?>
				<div style="margin-top:10px">
					<a href="<?= $SiteSecureBaseURL ?>PaymentForm.php">Process Another</a>
				</div>
			</div>
<?php
			break;
		case "THREE_D_SECURE":
?>
			<input name="PaReq" type="hidden" value="<?= $PaREQ ?>" />
			<input name="MD" type="hidden" value="<?= $CrossReference ?>" />
			<input name="TermUrl" type="hidden" value="<?= $SiteSecureBaseURL ?>ThreeDSecureLandingPage.php" />

			<iframe id="ACSFrame" name="ACSFrame" src="<?= $SiteSecureBaseURL ?>Loading.htm" width="800" height="400" frameborder="0"></iframe>
<?php
			break;
		case "PAYMENT_FORM":
?>
			<input name="FormMode" type="hidden" value="<?= $NextFormMode ?>" />
<?php
			if (isset($Message) == true)
			{
				if ($Message != "")
				{
?>
			<div class="ErrorMessage">
				<?= $Message ?>
			</div>
<?php
				}
			}
?>
			<div class="ContentRight">
			    <div class="ContentHeader">
			        Order Details
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Amount:</div>
			        <div class="FormInputTextOnly">10.00 GBP</div>
					<input type="hidden" name="Amount" value="1000" />
					<input type="hidden" name="CurrencyISOCode" value="826" />
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Order Description:</div>
			        <div class="FormInputTextOnly">A Test Order</div>
					<input type="hidden" name="OrderID" value="Order-1234" />
					<input type="hidden" name="OrderDescription" value="A Test Order" />
			    </div>
			</div>
			<div class="ContentRight">
			    <div class="ContentHeader">
			        Card Details
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Name On Card:</div>
			        <div class="FormInput">
			            <input name="CardName" value="<?= $CardName ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Card Number:</div>
			        <div class="FormInput">            
			            <input name="CardNumber" value="<?= $CardNumber ?>" class="InputTextField" MaxLength="20" />
			        </div>
			    </div>
<?php
			$ThisYear = date("Y");
			$ThisYearPlusTen = $ThisYear + 10;
?>
				<div class="FormItem">
				    <div class="FormLabel">
				        Expiry Date:
				    </div>
				    <div class="FormInput">
				        <select name="ExpiryDateMonth" style="width:45px">
							<option></option>
<?php
			for ($LoopIndex = 1; $LoopIndex <= 12; $LoopIndex++)
			{
				$DisplayMonth = $LoopIndex;
				if ($LoopIndex < 10)
				{
					$DisplayMonth = "0".$LoopIndex;
				}
				if ($ExpiryDateMonth != "" &&
				    $ExpiryDateMonth == $LoopIndex)
				{
?>
							<option selected="selected"><?= $DisplayMonth ?></option>
<?php
				}
				else
				{
?>
							<option><?= $DisplayMonth ?></option>
<?php
				}
			}
?>
						</select>
						/
						<select name="ExpiryDateYear" style="width:55px">
							<option></option>
<?php
			for ($LoopIndex = $ThisYear; $LoopIndex <= $ThisYearPlusTen; $LoopIndex++)
			{
				$ShortYear=substr($LoopIndex, strlen($LoopIndex)-2, 2);
				if ($ExpiryDateYear != "" &&
				    $ExpiryDateYear == $ShortYear)
				{
?>
							<option value="<?= $ShortYear ?>" selected="selected"><?= $LoopIndex ?></option>
<?php
				}
				else
				{
?>
							<option value="<?= $ShortYear ?>"><?= $LoopIndex ?></option>
<?php
				}
			}
?>
						</select>
					</div>
				</div>
				<div class="FormItem">
				    <div class="FormLabel">
				        Start Date:
				    </div>
				    <div class="FormInput">
				        <select name="StartDateMonth" style="width:45px">
							<option></option>
<?php
			for ($LoopIndex = 1; $LoopIndex <= 12; $LoopIndex++)
			{
				$DisplayMonth = $LoopIndex;
				if ($LoopIndex < 10)
				{
					$DisplayMonth = "0".$LoopIndex;
				}
				if ($StartDateMonth != "" &&
				    $StartDateMonth == $LoopIndex)
				{
?>
							<option selected="selected"><?= $DisplayMonth ?></option>
<?php
				}
				else
				{
?>
							<option><?= $DisplayMonth ?></option>
<?php
				}
			}
?>
						</select>
				        /
				        <select name="StartDateYear" style="width:55px">
							<option></option>
<?php
			for ($LoopIndex = 2000; $LoopIndex <= $ThisYear; $LoopIndex++)
		   	{
		   		$ShortYear=substr($LoopIndex, strlen($LoopIndex)-2, 2);
		   		if ($StartDateYear != "" &&
		   	    	$StartDateYear == $ShortYear)
		   		{
?>
							<option value="<?= $ShortYear ?>" selected="selected"><?= $LoopIndex ?></option>
<?php
				}
				else
				{
?>
							<option value="<?= $ShortYear ?>"><?= $LoopIndex ?></option>
<?php
				}
			}
?>
			            </select>
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Issue Number:</div>
			        <div class="FormInput">
			            <input name="IssueNumber" value="<?= $IssueNumber ?>" class="InputTextField" MaxLength="2" style="width:50px" />
			        </div>
			        <div class="FormValnameationText"></div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">CV2:</div>
			        <div class="FormInput">
			            <input name="CV2" value="<?= $CV2 ?>" class="InputTextField" MaxLength="4" style="width:50px" />
			        </div>
			    </div>
			</div>

			<div class="ContentRight">
			    <div class="ContentHeader">
			        Customer Details
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Address:</div>
			        <div class="FormInput">
			            <input name="Address1" value="<?= $Address1 ?>" class="InputTextField" MaxLength="100" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">&nbsp</div>
			        <div class="FormInput">
			            <input name="Address2" value="<?= $Address2 ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">&nbsp</div>
			        <div class="FormInput">
			            <input name="Address3" value="<?= $Address3 ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">&nbsp</div>
			        <div class="FormInput">
			            <input name="Address4" value="<?= $Address4 ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">City:</div>
			        <div class="FormInput">
			            <input name="City" value="<?= $City ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">State:</div>
			        <div class="FormInput">
			            <input name="State" value="<?= $State ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">Post Code:</div>
			        <div class="FormInput">
			            <input name="PostCode" value="<?= $PostCode ?>" class="InputTextField" MaxLength="50" />
			        </div>
			    </div>
			    <div class="FormItem">
			        <div class="FormLabel">
			            Country:
			        </div>
			        <div class="FormInput">
			            <select name="CountryISOCode" style="width:200px">
							<option value="-1"></option>
<?php
			$FirstZeroPriorityGroup = true;
			for ($LoopIndex = 0; $LoopIndex < $iclISOCountryList->getCount()-1; $LoopIndex++)
			{
				if ($iclISOCountryList->getAt($LoopIndex)->getListPriority() == 0 &&
					$FirstZeroPriorityGroup == true)
				{
?>
							<option value="-1">--------------------</option>
<?php
					$FirstZeroPriorityGroup = false;
				} 

				if ($CountryISOCode != "" &&
					$CountryISOCode != -1 &&
					$CountryISOCode == $iclISOCountryList->getAt($LoopIndex)->getISOCode())
				{
?>
							<option value="<?= $iclISOCountryList->getAt($LoopIndex)->getISOCode() ?>" selected="selected"><?= $iclISOCountryList->getAt($LoopIndex)->getCountryName() ?></option>
<?php
				}
				else
				{
?>
							<option value="<?= $iclISOCountryList->getAt($LoopIndex)->getISOCode() ?>"><?= $iclISOCountryList->getAt($LoopIndex)->getCountryName() ?></option>
<?php
				}
			}
?>
						</select>
					</div>
			   	</div>
			   	<div class="FormItem">
			       	<div class="FormSubmit">
			           	<input type="submit" value="Submit For Processing" />
			       	</div>
			   	</div>
			</div>
<?php
			break;
	}
?>
	    </form>
	</div>
</body>
</html>
