<?php
	require_once('Common.php');
	require_once('SOAP.php');

	class NullableTRANSACTION_TYPE extends Nullable 
	{
		private $m_ttValue;
	 	
	 	public function getValue()
	 	{
			if ($this->m_boHasValue == false)
			{
				throw new Exception("Object has no value");
			}
			
			return ($this->m_ttValue);
	 	}
	 	public function setValue($value)
	 	{
	 		$this->m_boHasValue = true;
	 		$this->m_ttValue = $value;
	 	}
	 	
	 	//constructor
	 	public function __construct($ttValue)
	 	{
	 		Nullable::__construct();
	 		
	 		if ($ttValue != null)
	 		{
	 			//$this->setValue($ttValue);
	 			$this->setValue($ttValue);
	 		}
	 	}
	}
	 
	class NullableCHECK_RESULT extends Nullable 
	{
	 	private $m_crValue;
	 	
	 	public function getValue()
	 	{
	 		if ($this->m_boHasValue == false)
			{
				throw new Exception("Object has no value");
			}
			return ($this->m_crValue);
	 	}
	 	public function setValue($value)
	 	{
	 		$this->m_boHasValue = true;
	 		$this->m_crValue = $value;	
	 	}
	 	
	 	//constructor
	 	public function __construct($crValue)
	 	{
	 		Nullable::__construct();
	 		
	 		if ($crValue != null)
	 		{
	 			//$this->m_crValue = $crValue;
	 			$this->setValue($crValue);
	 		}
	 	}
	}

	class NullableCARD_DATA_STATUS extends Nullable
	{
		private $m_cdsValue;

	    function getValue()
	    {
	   		if ($this->m_boHasValue == false)
	        {
	         	throw new Exception("Object has no value");
	       	}
	         	return ($this->m_cdsValue);
	    }
	        
	    function setValue($value)
	    {
	        $this->m_boHasValue = true;
	        $this->m_cdsValue = $value;
	    }

	    function __construct($cdsValue)
	    {
	        parent::__construct();
	        	
	        if ($cdsValue != null)
	        {
	        	$this->setValue($cdsValue);
	        }
	    }
	}
	    
	/*****************/
	/* Gateway Enums */
	/*****************/
	final class CARD_TYPE
	{
		const UNKNOWN = 'UNKNOWN';
	    const AMERICAN_EXPRESS = 'AMERICAN_EXPRESS';
	    const JCB = 'JCB';
	    const MASTERCARD = 'MASTERCARD';
	    const DINERS_CLUB = 'DINERS_CLUB';
	    const VISA_DEBIT = 'VISA_DEBIT';
	    const SOLO = 'SOLO';
	    const VISA_ELECTRON = 'VISA_ELECTRON';
	    const VISA = 'VISA';
	    const VISA_PURCHASING = 'VISA_PURCHASING';
	    const MAESTRO = 'MAESTRO';
	    const LASER = 'LASER';
	    const DEBIT_MASTERCARD = 'DEBIT_MASTERCARD';

	    //make sure the class cannot be instantiated	
	    private function __construct()
	    {
	    }
	}
	final class TRANSACTION_TYPE
	{
		const UNKNOWN = 'UNKNOWN';
	    const SALE = 'SALE';
	    const REFUND = 'REFUND';
	    const PREAUTH = 'PREAUTH';
	    const VOID = 'VOID';
	    const COLLECTION = 'COLLECTION';
	    const RETRY = 'RETRY';

	    //make sure the class cannot be instantiated
	    private function __construct()
	    {
	    }
	}
	final class CHECK_RESULT
	{
		const UNKNOWN = 'UNKNOWN';
	    const PASSED = 'PASSED';
	    const FAILED = 'FAILED';
	    const PARTIAL = 'PARTIAL';
	    const ERROR = 'ERROR';
	    const NOT_SUBMITTED = 'NOT_SUBMITTED';
	    const NOT_CHECKED = 'NOT_CHECKED';
	    const NOT_ENROLLED = 'NOT_ENROLLED';

	   	//make sure the class cannot be instantiated	
	   	private function __construct()
	   	{
	   	}
	}
	final class CARD_DATA_STATUS
	{
		const UNKNOWN = 'UNKNOWN';
		const MUST_BE_SUBMITTED = 'MUST_BE_SUBMITTED';
		const DO_NOT_SUBMIT = 'DO_NOT_SUBMIT';
		const SUBMIT_ONLY_IF_ON_CARD = 'SUBMIT_ONLY_IF_ON_CARD';
		const IGNORED_IF_SUBMITTED = 'IGNORED_IF_SUBMITTED';
	}

		
	/*****************/
	/* Input classes */
	/*****************/
	class RequestGatewayEntryPoint extends GatewayEntryPoint 
	{
		private $m_nRetryAttempts;

	  	public function getRetryAttempts()
	  	{
	  		return $this->m_nRetryAttempts;
	  	}
		
		//constructor
	  	public function __construct($szEntryPointURL, $nMetric, $nRetryAttempts)
	   	{
	   		//do NOT forget to call the parent constructor too
	   		//parent::GatewayEntryPoint($szEntryPointURL, $nMetric);
	   		GatewayEntryPoint::__construct($szEntryPointURL, $nMetric);
	   		
	     	$this->m_nRetryAttempts = $nRetryAttempts;
	   	}
	}

	class RequestGatewayEntryPointList
	{
		private $m_lrgepRequestGatewayEntryPoint;
		
		public function getAt($nIndex)
		{
			if ($nIndex < 0 ||
				$nIndex >= count($this->m_lrgepRequestGatewayEntryPoint))
			{
				throw new Exception("Array index out of bounds");
			}
				
			return $this->m_lrgepRequestGatewayEntryPoint[$nIndex];
		}
		
		public function getCount()
		{
			return count($this->m_lrgepRequestGatewayEntryPoint);
		}
		
		public function sort($ComparerClassName, $ComparerMethodName)
		{
			usort($this->m_lrgepRequestGatewayEntryPoint, array("$ComparerClassName","$ComparerMethodName"));		
		}
		
		public function add($EntryPointURL, $nMetric, $nRetryAttempts)
		{
			return array_push($this->m_lrgepRequestGatewayEntryPoint, new RequestGatewayEntryPoint($EntryPointURL, $nMetric, $nRetryAttempts));
		}
		
		//constructor
		public function __construct()
		{
			$this->m_lrgepRequestGatewayEntryPoint = array();
		}
	}

	class GenericVariable
	{
		private $m_szName;
	   	private $m_szValue;

	   	public function getName()
	   	{
	   		return $this->m_szName;
	   	}
	   	public function getValue()
	   	{
	   		return $this->m_szValue;
	   	}

	   	//constructor
	   	public function __construct($szName, $szValue)
	    {
	    	$this->m_szName = $szName;
	    	$this->m_szValue = $szValue;
	    }
	}

	class GenericVariableList
	{
		private $m_lgvGenericVariableList;
		
		public function getAt($intOrStringValue)
		{
			$nCount = 0;
			$boFound = false;
			$gvGenericVariable = null;
			//$gvGenericVariable2;
			
			if (is_int($intOrStringValue))
			{
				if ($intOrStringValue < 0 ||
					$intOrStringValue >= count($this->m_lgvGenericVariableList))
				{
					throw new Exception("Array index out of bounds");
				}
				
				return $this->m_lgvGenericVariableList[$intOrStringValue];
			}
			elseif (is_string($intOrStringValue))
			{
				if ($intOrStringValue == null ||
					$intOrStringValue == '')
				{
					return (null);
				}

				while (!$boFound &&
						$nCount < count($this->m_lgvGenericVariableList))
				{
					if (strtoupper($this->m_lgvGenericVariableList[$nCount]->getName()) ==
						strtoupper($intOrStringValue))
					{
						$gvGenericVariable = $this->m_lgvGenericVariableList[$nCount];
						$boFound = true;
					}
					$nCount++;
				}

				return $gvGenericVariable;
			}
			else 
			{
				throw new Exception('Invalid parameter type:$intOrStringValue');
			}
		}
		
		public function getCount()
		{
			return count($this->m_lgvGenericVariableList);
		}
		
		public function add($Name, $szValue)
		{
			$nReturnValue = -1;
			
			if ($Name != null &&
				$Name != "")
			{
	        	$nReturnValue = array_push($this->m_lgvGenericVariableList, new GenericVariable($Name, $szValue));
			}

	        return ($nReturnValue);
		}
		
		//constructor
		public function __construct()
		{
			$this->m_lgvGenericVariableList = array();
		}
	}

	class CustomerDetails
	{
		private $m_adBillingAddress;
	    private $m_szEmailAddress;
	    private $m_szPhoneNumber;
	    private $m_szCustomerIPAddress;
	    
	    public function getBillingAddress()
	    {
	    	return $this->m_adBillingAddress;
	    }
	    public function getEmailAddress()
	    {
	    	return $this->m_szEmailAddress;
	    }
	    public function getPhoneNumber()
	    {
	    	return $this->m_szPhoneNumber;
	    }
	    public function getCustomerIPAddress()
	    {
	    	return $this->m_szCustomerIPAddress;
	    }
	    
	    //constructor
	    public function __construct($adBillingAddress = null, $szEmailAddress, $szPhoneNumber, $szCustomerIPAddress)
	    {
	    	$this->m_adBillingAddress = $adBillingAddress;
	    	$this->m_szEmailAddress = $szEmailAddress;
	    	$this->m_szPhoneNumber = $szPhoneNumber;
	    	$this->m_szCustomerIPAddress = $szCustomerIPAddress;
	    }
	}

	class AddressDetails
	{
		private $m_szAddress1;
	    private $m_szAddress2;
	    private $m_szAddress3;
	    private $m_szAddress4;
	    private $m_szCity;
	    private $m_szState;
	    private $m_szPostCode;
	    private $m_nCountryCode;
	    
	    public function getAddress1()
	    {
	    	return $this->m_szAddress1;
	    }
	    public function getAddress2()
	    {
	    	return $this->m_szAddress2;
	    }
	    public function getAddress3()
	    {
	    	return $this->m_szAddress3;
	    }
	    public function getAddress4()
	    {
	    	return $this->m_szAddress4;
	    }
	    public function getCity()
	    {
	    	return $this->m_szCity;
	    }
	    public function getState()
	    {
	    	return $this->m_szState;
	    }
	    public function getPostCode()
	    {
	    	return $this->m_szPostCode;
	    }
	    public function getCountryCode()
	    {
	  		return $this->m_nCountryCode;
	    }
	        
	    //constructor
	    public function __construct($szAddress1, $szAddress2, $szAddress3, $szAddress4, $szCity, $szState, $szPostCode, NullableInt $nCountryCode = null)
	    {
	    	$this->m_szAddress1 = $szAddress1;
	    	$this->m_szAddress2 = $szAddress2;
	    	$this->m_szAddress3 = $szAddress3;
	    	$this->m_szAddress4 = $szAddress4;
	    	$this->m_szCity = $szCity;
	    	$this->m_szState = $szState;
	    	$this->m_szPostCode = $szPostCode;
	    	$this->m_nCountryCode = $nCountryCode;
	    }
	}

	class CreditCardDate
	{
		private  $m_nMonth;
	    private $m_nYear;
	    
	    public function getMonth()
	    {
	    	return $this->m_nMonth;
	    }
	    public function getYear()
	    {
	    	return $this->m_nYear;
	    }
	    
	    //constructor
	    public function __construct(NullableInt $nMonth = null, NullableInt $nYear = null)
	    {
	    	$this->m_nMonth = $nMonth;
	    	$this->m_nYear = $nYear;
	    }
	}

	class CardDetails
	{
		private $m_szCardName;
	    private $m_szCardNumber;
	    private $m_ccdExpiryDate;
	    private $m_ccdStartDate;
	    private $m_szIssueNumber;
	    private $m_szCV2;
	    
	    public function getCardName()
	    {
	    	return $this->m_szCardName;
	    }
	    public function getCardNumber()
	    {
	    	return $this->m_szCardNumber;
	    }
	    
	    public function getExpiryDate()
	    {
	    	return $this->m_ccdExpiryDate;
	    }
	   
	    public function getStartDate()
	    {
	    	return $this->m_ccdStartDate;
	    }
	    
	    public function getIssueNumber()
	    {
	    	return $this->m_szIssueNumber;
	    }
	    
	    public function getCV2()
	    {
	    	return $this->m_szCV2;
	    }
	    
	    //constructor
	    public function __construct($szCardName, $szCardNumber, CreditCardDate $ccdExpiryDate = null, CreditCardDate $ccdStartDate = null, $IssueNumber, $CV2)
	    {
	    	$this->m_szCardName = $szCardName;
	    	$this->m_szCardNumber = $szCardNumber;
	    	$this->m_ccdExpiryDate = $ccdExpiryDate;
	    	$this->m_ccdStartDate = $ccdStartDate;
	    	$this->m_szIssueNumber = $IssueNumber;
	    	$this->m_szCV2 = $CV2;
	    }
	}

	class MerchantDetails
	{
		private $m_szMerchantID;
	    private $m_szPassword;

	    public function getMerchantID()
	    {
	    	return $this->m_szMerchantID;
	    }
	    public function getPassword()
	    {
	    	return $this->m_szPassword;
	    }
	    
	    //constructor
	    public function __construct($szMerchantID, $szPassword)
	    {
	    	$this->m_szMerchantID = $szMerchantID;
	    	$this->m_szPassword = $szPassword;
	    }
	}

	class MessageDetails
	{
		private $m_ttTransactionType;
	    private $m_boNewTransaction;
	    private $m_szCrossReference;

	    public function getTransactionType()
	    {
	    	return $this->m_ttTransactionType;
	    }
	    public function getNewTransaction()
	    {
	    	return $this->m_boNewTransaction;
	    }
	    public function getCrossReference()
	    {
	    	return $this->m_szCrossReference;
	    }
	    
	    //constructor
	    public function __construct($ttTransactionType, $szCrossReference = null, NullableBool $boNewTransaction = null)
	    {
	    	$this->m_ttTransactionType = $ttTransactionType;
	    	
	    	if ($szCrossReference != null)
	    	{
	    		$this->m_szCrossReference = $szCrossReference;
	    	}
	    	if ($boNewTransaction != null)
	    	{
	    		$this->m_boNewTransaction = $boNewTransaction;
	    	}
	    }
	}

	class TransactionDetails
	{
		private $m_mdMessageDetails;
	    private $m_nAmount;
	    private $m_nCurrencyCode;
	    private $m_szOrderID;
	    private $m_szOrderDescription;
	    private $m_tcTransactionControl;
	    private $m_tdsbdThreeDSecureBrowserDetails;
	    
	    public function getMessageDetails()
	    {
	    	return $this->m_mdMessageDetails;
	    }
	    public function getAmount()
	    {
	    	return $this->m_nAmount;
	    }
	    public function getCurrencyCode()
	    {
	    	return $this->m_nCurrencyCode;
	    }
	   	public function getOrderID()
	    {
	    	return $this->m_szOrderID;
	    }
	    public function getOrderDescription()
	    {
	    	return $this->m_szOrderDescription;
	    }
	    public function getTransactionControl()
	    {
	    	return $this->m_tcTransactionControl;
	    }
	    public function getThreeDSecureBrowserDetails()
	    {
	    	return $this->m_tdsbdThreeDSecureBrowserDetails;
	    }
	    
	    //constructor
	    public function __construct($TransactionTypeOrMessageDetails, NullableInt $nAmount = null, NullableInt $nCurrencyCode = null, $szOrderID, $szOrderDescription, TransactionControl $tcTransactionControl = null, ThreeDSecureBrowserDetails $tdsbdThreeDSecureBrowserDetails = null)
	    {
			if ($TransactionTypeOrMessageDetails instanceof MessageDetails)
			{
				$this->m_mdMessageDetails = $TransactionTypeOrMessageDetails;
	    		$this->m_nAmount = $nAmount;
	    		$this->m_nCurrencyCode = $nCurrencyCode;
	    		$this->m_szOrderID = $szOrderID;
	    		$this->m_szOrderDescription = $szOrderDescription;
	    		$this->m_tcTransactionControl = $tcTransactionControl;
	    		$this->m_tdsbdThreeDSecureBrowserDetails = $tdsbdThreeDSecureBrowserDetails;
			}
			else
			{
				$this->__construct(new MessageDetails(new NullableTRANSACTION_TYPE($TransactionTypeOrMessageDetails)), $nAmount, $nCurrencyCode, $szOrderID, $szOrderDescription, $tcTransactionControl, $tdsbdThreeDSecureBrowserDetails);
			}
	    }
	}

	class ThreeDSecureBrowserDetails
	{
		private $m_nDeviceCategory;
	    private $m_szAcceptHeaders;
	    private $m_szUserAgent;

	    public function getDeviceCategory()
	    {
	    	return $this->m_nDeviceCategory;
	    }
	    
	    public function getAcceptHeaders()
	    {
	    	return $this->m_szAcceptHeaders;
	    }
	    
	    public function getUserAgent()
	    {
	    	return $this->m_szUserAgent;
	    }
	    
	    //constructor
	    public function __construct(NullableInt $nDeviceCategory = null, $szAcceptHeaders, $szUserAgent)
	    {
	    	$this->m_nDeviceCategory = $nDeviceCategory;
	    	$this->m_szAcceptHeaders = $szAcceptHeaders;
	    	$this->m_szUserAgent = $szUserAgent;	
	    }
	}
	    
	class TransactionControl
	{
		private $m_boEchoCardType;
	    private $m_boEchoAVSCheckResult;
	    private $m_boEchoCV2CheckResult;
	   	private $m_boEchoAmountReceived;
	    private $m_nDuplicateDelay;
	    private $m_szAVSOverridePolicy;
	    private $m_szCV2OverridePolicy;
	    private $m_boThreeDSecureOverridePolicy;
	    private $m_szAuthCode;
	    private $m_tdsptThreeDSecurePassthroughData;
	    private $m_lgvCustomVariables;
	    
	    public function getEchoCardType()
	    {
	    	return $this->m_boEchoCardType;
	    }
	   
	    public function getEchoAVSCheckResult()
	    {
	    	return $this->m_boEchoAVSCheckResult;
	    }
	    
	    public function getEchoCV2CheckResult()
	    {
	    	return $this->m_boEchoCV2CheckResult;
	    }
	    
	    public function getEchoAmountReceived()
	    {
	    	return $this->m_boEchoAmountReceived;
	    }
	   
	    public function getDuplicateDelay()
	    {
	    	return $this->m_nDuplicateDelay;
	    }
	    
	    public function getAVSOverridePolicy()
	    {
	    	return $this->m_szAVSOverridePolicy;
	    }
	    
	    public function getCV2OverridePolicy()
	    {
	    	return $this->m_szCV2OverridePolicy;
	    }
	    
	    public function getThreeDSecureOverridePolicy()
	    {
	    	return $this->m_boThreeDSecureOverridePolicy;
	    }
	    
	    public function getAuthCode()
	    {
	    	return $this->m_szAuthCode;
	    }
	    
	    function getThreeDSecurePassthroughData()
	    {
	    	return $this->m_tdsptThreeDSecurePassthroughData;
	    }
	   
	    public function getCustomVariables()
	    {
	    	return $this->m_lgvCustomVariables;
	    }
	    
	    //constructor
	    public function __construct(NullableBool $boEchoCardType = null, NullableBool $boEchoAVSCheckResult = null, NullableBool $boEchoCV2CheckResult = null, NullableBool $boEchoAmountReceived = null, NullableInt $nDuplicateDelay = null, $szAVSOverridePolicy, $szCV2OverridePolicy, NullableBool $boThreeDSecureOverridePolicy = null, $szAuthCode, ThreeDSecurePassthroughData $tdsptThreeDSecurePassthroughData = null, GenericVariableList $lgvCustomVariables = null)
	    {
	    	$this->m_boEchoCardType = $boEchoCardType;
	    	$this->m_boEchoAVSCheckResult = $boEchoAVSCheckResult;
	    	$this->m_boEchoCV2CheckResult = $boEchoCV2CheckResult;
	    	$this->m_boEchoAmountReceived = $boEchoAmountReceived;
	    	$this->m_nDuplicateDelay = $nDuplicateDelay;
	    	$this->m_szAVSOverridePolicy = $szAVSOverridePolicy;
	    	$this->m_szCV2OverridePolicy = $szCV2OverridePolicy;
	    	$this->m_boThreeDSecureOverridePolicy = $boThreeDSecureOverridePolicy;
	    	$this->m_szAuthCode = $szAuthCode;
	    	$this->m_tdsptThreeDSecurePassthroughData = $tdsptThreeDSecurePassthroughData;
	    	$this->m_lgvCustomVariables = $lgvCustomVariables;
	    }
	}

	class ThreeDSecureInputData
	{
		private $m_szCrossReference;
	    private $m_szPaRES;

	    public function getCrossReference()
	    {
	    	return $this->m_szCrossReference;
	    }
	    
	    public function getPaRES()
	    {
	    	return $this->m_szPaRES;
	    }
	   
	    //constructor
	    public function __construct($szCrossReference, $szPaRES)
	    {
	    	$this->m_szCrossReference = $szCrossReference;
	    	$this->m_szPaRES = $szPaRES;
	    }
	}

	class ThreeDSecurePassthroughData
	{
	 	private $m_szEnrolmentStatus;
	    private $m_szAuthenticationStatus;
	    private $m_szElectronicCommerceIndicator;
	    private $m_szAuthenticationValue;
	    private $m_szTransactionIdentifier;

	    function getEnrolmentStatus()
	    {
	    	return $this->m_szEnrolmentStatus;
	    }
	    
	    function getAuthenticationStatus()
	    {
	    	return $this->m_szAuthenticationStatus;
	    }
	    
	    function getElectronicCommerceIndicator()
	    {
	    	return $this->m_szElectronicCommerceIndicator;
	    }
	    
	    function getAuthenticationValue()
	    {
	    	return $this->m_szAuthenticationValue;
	    }

	    function getTransactionIdentifier()
	    {
	    	return $this->m_szTransactionIdentifier;
	    }

	    //constructor
	    function __construct($szEnrolmentStatus,
	                    	 $szAuthenticationStatus,
	                         $szElectronicCommerceIndicator,
	                         $szAuthenticationValue,
	                         $szTransactionIdentifier)
	    {
	     	$this->m_szEnrolmentStatus = $szEnrolmentStatus;
	        $this->m_szAuthenticationStatus = $szAuthenticationStatus;
	        $this->m_szElectronicCommerceIndicator = $szElectronicCommerceIndicator;
	        $this->m_szAuthenticationValue = $szAuthenticationValue;
	        $this->m_szTransactionIdentifier = $szTransactionIdentifier;
	    }
	}


	/******************/
	/* Output classes */
	/******************/
	class Issuer
	{
		private $m_szIssuer;
		private $m_nISOCode;
		
		public function getValue()
		{
			return $this->m_szIssuer;
		}
		
		public function getISOCode()
		{
			return $this->m_nISOCode;
		}
		
		//constructor
	    public function __construct($szIssuer, NullableInt $nISOCode = null)
	    {
	        $this->m_szIssuer = $szIssuer;
	        $this->m_nISOCode = $nISOCode;
	    }
	}
	
	class CardTypeData
	{
	    private $m_ctCardType;
	    private $m_iIssuer;
	    private $m_boLuhnCheckRequired;
	    private $m_cdsIssueNumberStatus;
	    private $m_cdsStartDateStatus;

	    public function getCardType()
	    {
	        return $this->m_ctCardType;
	    }
	   
	    public function getIssuer()
	    {
	    	return $this->m_iIssuer;
	    }
	   
	    public function getLuhnCheckRequired()
	    {
	        return $this->m_boLuhnCheckRequired;
	    }
	    
	    public function getIssueNumberStatus()
	    {
	        return $this->m_cdsIssueNumberStatus;
	    }
	   
	    public function getStartDateStatus()
	    {
	        return $this->m_cdsStartDateStatus;
	    }
	    
	    //constructor
	    public function __construct($ctCardType = null, $iIssuer, NullableBool $boLuhnCheckRequired = null, NullableCARD_DATA_STATUS $cdsIssueNumberStatus = null, NullableCARD_DATA_STATUS $cdsStartDateStatus = null)
	    {
	        $this->m_ctCardType = $ctCardType;
	        $this->m_iIssuer = $iIssuer;
	        $this->m_boLuhnCheckRequired = $boLuhnCheckRequired;
	        $this->m_cdsIssueNumberStatus = $cdsIssueNumberStatus;
	        $this->m_cdsStartDateStatus = $cdsStartDateStatus;
	    }
	}

	class GatewayEntryPoint
	{
		private $m_szEntryPointURL;
	    private $m_nMetric;

	 	public function getEntryPointURL()
	 	{
	 		return $this->m_szEntryPointURL;
	 	}
	 	
	    public function getMetric()
	    {
	    	return $this->m_nMetric;
	    }

	    //constructor
	    public function __construct($szEntryPointURL, $nMetric)
	    {
			$this->m_szEntryPointURL = $szEntryPointURL;
			$this->m_nMetric = $nMetric;
	    }
	}

	class GatewayEntryPointList
	{
	    private $m_lgepGatewayEntryPoint;

	    public function getAt($nIndex)
	    {
	        if ($nIndex < 0 ||
		     	$nIndex >= count($this->m_lgepGatewayEntryPoint))
		     {
		  	 	throw new Exception("Array index out of bounds");
		     }
		
	        return $this->m_lgepGatewayEntryPoint[$nIndex];
	    }

	    public function getCount()
	    {
	        return count($this->m_lgepGatewayEntryPoint);
	    }

	    public function add($GatewayEntrypointOrEntrypointURL, $nMetric)
	    {
	    	return array_push($this->m_lgepGatewayEntryPoint, new GatewayEntryPoint($GatewayEntrypointOrEntrypointURL, $nMetric));
	    }
	    
	    //constructor
	    public function __construct()
	    {
	       $this->m_lgepGatewayEntryPoint = array();	
	    }
	}

	class PreviousTransactionResult
	{
		private $m_nStatusCode;
	    private $m_szMessage;
	    //private $m_szCrossReference;
	    
	    function getStatusCode()
	    {
	    	return $this->m_nStatusCode;
	    }
	    
	    function getMessage()
	    {
	    	return $this->m_szMessage;
	    }
	    
	    function __construct(NullableInt $nStatusCode = null,
	    					 $szMessage)
	    {
	    	$this->m_nStatusCode = $nStatusCode;
	    	$this->m_szMessage = $szMessage;
	    }
	}

	class GatewayOutput
	{
	    private $m_nStatusCode;
	    private $m_szMessage;
	    private $m_szPassOutData;
	    private $m_ptdPreviousTransactionResult;
	    private $m_boAuthorisationAttempted;
	    private $m_lszErrorMessages;

	    public function getStatusCode()
	    {
	        return $this->m_nStatusCode;
	    }
	    
	    public function  getMessage()
	    {
	        return $this->m_szMessage;
	    }
	    
	    public function  getPassOutData()
	    {
	        return $this->m_szPassOutData;
	    }
	   
	    public function  getPreviousTransactionResult()
	    {
	        return $this->m_ptdPreviousTransactionResult;
	    }
	    
	    public function  getAuthorisationAttempted()
	    {
	        return $this->m_boAuthorisationAttempted;
	    }
	    
	    public function  getErrorMessages()
	    {
	        return $this->m_lszErrorMessages;
	    }
	    
	    //constructor
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, NullableBool $boAuthorisationAttempted = null, PreviousTransactionResult $ptdPreviousTransactionResult = null, StringList $lszErrorMessages = null)
	    {
		    $this->m_nStatusCode = $nStatusCode;
			$this->m_szMessage = $szMessage;
			$this->m_szPassOutData = $szPassOutData;
			$this->m_boAuthorisationAttempted = $boAuthorisationAttempted;
			$this->m_ptdPreviousTransactionResult = $ptdPreviousTransactionResult;
			$this->m_lszErrorMessages = $lszErrorMessages;
	    }
	}

	class ThreeDSecureOutputData
	{
		private $m_szPaREQ;
	   	private $m_szACSURL;

	   	public function getPaREQ()
	   	{
			return $this->m_szPaREQ;
	   	}
	   
	   	public function getACSURL()
	   	{
	       	return ($this->m_szACSURL);
	   	}
	      
	   	//constructor
	   	public function __construct($szPaREQ, $szACSURL)
	   	{
			$this->m_szPaREQ = $szPaREQ;
	       	$this->m_szACSURL = $szACSURL;
	   	}
	}

	class GetGatewayEntryPointsOutputMessage extends BaseOutputMessage
	{
	   	//constructor
	   	function __construct(GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	parent::__construct($lgepGatewayEntryPoints);
	   	}
	}

	class TransactionOutputMessage extends BaseOutputMessage
	{
		private $m_szCrossReference;
		private $m_szAuthCode;
	    private $m_crAddressNumericCheckResult;
	    private $m_crPostCodeCheckResult;
	    private $m_crThreeDSecureAuthenticationCheckResult;
	    private $m_crCV2CheckResult;
	    private $m_ctdCardTypeData;
	    private $m_nAmountReceived;
	    private $m_tdsodThreeDSecureOutputData;
	    private $m_lgvCustomVariables;

	    public function getCrossReference()
	    { 
	        return $this->m_szCrossReference;
	    }
	    
	    public function getAuthCode()
	    { 
	        return $this->m_szAuthCode;
	    }

	    public function getAddressNumericCheckResult()
	    {
	       	return $this->m_crAddressNumericCheckResult;
	    }
	    
	    public function getPostCodeCheckResult()
	    { 
			return $this->m_crPostCodeCheckResult;
	    }
	    
	    public function getThreeDSecureAuthenticationCheckResult()
	    {
	        return $this->m_crThreeDSecureAuthenticationCheckResult;
	    }
	   
	    public function getCV2CheckResult()
	    {
	    	return $this->m_crCV2CheckResult;
	    }
	    
	    public function getCardTypeData()
	    {
	        return $this->m_ctdCardTypeData;
	    }
	   
	    public function getAmountReceived()
	    {
	       	return $this->m_nAmountReceived;
	    }
	    
	    public function getThreeDSecureOutputData()
	    {
	       	return $this->m_tdsodThreeDSecureOutputData;
	    }
	    
	    public function getCustomVariables()
	    {
	       	return $this->m_lgvCustomVariables;
	    }
	    
	 	//constructor
	    public function __construct($szCrossReference,
									$szAuthCode,
	    							NullableCHECK_RESULT $crAddressNumericCheckResult = null,
	    							NullableCHECK_RESULT $crPostCodeCheckResult = null,
	    							NullableCHECK_RESULT $crThreeDSecureAuthenticationCheckResult = null,
	    							NullableCHECK_RESULT $crCV2CheckResult = null,
	    							CardTypeData $ctdCardTypeData = null,
	    							NullableInt $nAmountReceived = null,
	    							ThreeDSecureOutputData $tdsodThreeDSecureOutputData = null,
	    							GenericVariableList $lgvCustomVariables = null,
	    							GatewayEntryPointList $lgepGatewayEntryPoints = null)
	    {
	     	//first calling the parent constructor
	        parent::__construct($lgepGatewayEntryPoints);
	        
		   	$this->m_szCrossReference = $szCrossReference;
			$this->m_szAuthCode = $szAuthCode;
			$this->m_crAddressNumericCheckResult = $crAddressNumericCheckResult;
			$this->m_crPostCodeCheckResult = $crPostCodeCheckResult;
			$this->m_crThreeDSecureAuthenticationCheckResult = $crThreeDSecureAuthenticationCheckResult;
			$this->m_crCV2CheckResult = $crCV2CheckResult;
			$this->m_ctdCardTypeData = $ctdCardTypeData;
			$this->m_nAmountReceived = $nAmountReceived;
			$this->m_tdsodThreeDSecureOutputData = $tdsodThreeDSecureOutputData;
			$this->m_lgvCustomVariables = $lgvCustomVariables;
	    }
	}

	class GetCardTypeOutputMessage extends BaseOutputMessage
	{
		private $m_ctdCardTypeData;

	   	public function getCardTypeData()
	   	{
	   		return $this->m_ctdCardTypeData;
	   	}

	  	//constructor
	   	public function __construct(CardTypeData $ctdCardTypeData,
	   								GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	parent::__construct($lgepGatewayEntryPoints);

	      	$this->m_ctdCardTypeData = $ctdCardTypeData;
	   	}
	}

	class BaseOutputMessage
	{
	   	private $m_lgepGatewayEntryPoints;

	   	public function getGatewayEntryPoints()
	   	{
	      	return $this->m_lgepGatewayEntryPoints;
	   	}

	   	//constructor
	   	public function __construct(GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	$this->m_lgepGatewayEntryPoints = $lgepGatewayEntryPoints;
	   	}
	}


	/********************/
	/* Gateway messages */
	/********************/
	class GetGatewayEntryPoints extends GatewayTransaction
	{
	  	function processTransaction(GatewayOutput &$goGatewayOutput = null, GetGatewayEntryPointsOutputMessage &$ggepGetGatewayEntryPointsOutputMessage = null)
	   	{
	      	$boTransactionSubmitted = false;
	      	$sSOAPClient;
	      	$lgepGatewayEntryPoints;

	      	$ggepGetGatewayEntryPointsOutputMessage = null;
	      	$goGatewayOutput = null;

	      	$sSOAPClient = new SOAP('GetGatewayEntryPoints', GatewayTransaction::getSOAPNamespace());
	      	$boTransactionSubmitted = GatewayTransaction::processTransaction($sSOAPClient, 'GetGatewayEntryPointsMessage', 'GetGatewayEntryPointsResult', 'GetGatewayEntryPointsOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
	      
	      	if ($boTransactionSubmitted)
	      	{
	      		$ggepGetGatewayEntryPointsOutputMessage = new GetGatewayEntryPointsOutputMessage($lgepGatewayEntryPoints);
	      	}
	      
	      	return $boTransactionSubmitted;
	   	}
	   
	   	//constructor
	   	public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	   								$nRetryAttempts,
	   								NullableInt $nTimeout = null,
	   								MerchantDetails $mdMerchantAuthentication = null,
	   								$szPassOutData)
	 	{
	   		if ($nRetryAttempts == null &&
	   			$nTimeout == null)
	   		{
	   			GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, 1, null, $mdMerchantAuthentication, $szPassOutData);								
	   		}
	   		else 
	   		{
	   			GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $mdMerchantAuthentication, $szPassOutData);
	   		}
	   	}
	}


	class CardDetailsTransaction extends GatewayTransaction 
	{
		private $m_tdTransactionDetails;
	    private $m_cdCardDetails;
	    private $m_cdCustomerDetails;
	     
	    public function getTransactionDetails()
	    {
	    	return $this->m_tdTransactionDetails;
	   	}
	     
	    public function getCardDetails()
	    {
	     	return $this->m_cdCardDetails;	
	    }
	     
	   	public function getCustomerDetails()
	    {
	    	return $this->m_cdCardDetails;
	    }
	     
	   	public function processTransaction(GatewayOutput &$goGatewayOutput = null, TransactionOutputMessage &$tomTransactionOutputMessage = null)
	   	{
	     	$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $XmlDocument;

	      	$tomTransactionOutputMessage = null;
	        $goGatewayOutput = null;

	        $sSOAPClient = new SOAP('CardDetailsTransaction', parent::getSOAPNamespace());
	        
	    	// transaction details
	       	if ($this->m_tdTransactionDetails != null)
	        {
	        	$test = $this->m_tdTransactionDetails->getAmount();
	       		if ($this->m_tdTransactionDetails->getAmount() != null)
	          	{
	            	if ($this->m_tdTransactionDetails->getAmount()->getHasValue())
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'Amount', (string)$this->m_tdTransactionDetails->getAmount()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getCurrencyCode() != null)
	          	{
	            	if ($this->m_tdTransactionDetails->getCurrencyCode()->getHasValue())
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'CurrencyCode', (string)$this->m_tdTransactionDetails->getCurrencyCode()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getMessageDetails() != null)
	            {
	            	if ($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'TransactionType', SharedFunctionsPaymentSystemShared::getTransactionType($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()->getValue()));
	                   	}
	                }
	            }
	            if ($this->m_tdTransactionDetails->getTransactionControl() != null)
	           	{
	             	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAuthCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AuthCode', $this->m_tdTransactionDetails->getTransactionControl()->getAuthCode());
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecureOverridePolicy() != null)
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecureOverridePolicy', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecureOverridePolicy()->getValue()));
	               	}
	               	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AVSOverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.CV2OverridePolicy', ($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()));
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.DuplicateDelay', (string)$this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getValue());
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCardType', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                  	}
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCV2CheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getValue()));
	                    }
	               	}
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAmountReceived', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData() != null)
	                {
	                	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getEnrolmentStatus()))
	                	{
	                		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData', 'EnrolmentStatus', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getEnrolmentStatus());
	                	}
	                	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationStatus()))
	                	{
	                		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData', 'AuthenticationStatus', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationStatus());
	                	}
	                	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getElectronicCommerceIndicator()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.ElectronicCommerceIndicator', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getElectronicCommerceIndicator());
	                	}
	                	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationValue()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.AuthenticationValue', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationValue());
	                	}
	                	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getTransactionIdentifier()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.TransactionIdentifier', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getTransactionIdentifier());
	                	}
	                }
	          	}
	          	if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails() != null)
	            {
	            	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getAcceptHeaders()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails.AcceptHeaders', $this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getAcceptHeaders());
	                }
	                if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails', 'DeviceCategory', (string)$this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory()->getValue());
	                    }
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getUserAgent()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails.UserAgent', $this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getUserAgent());
	                }
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderID()))
	           	{
	             	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderID', $this->m_tdTransactionDetails->getOrderID());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderDescription()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderDescription', $this->m_tdTransactionDetails->getOrderDescription());
	            }
	        }
	        // card details
	        if ($this->m_cdCardDetails != null)
	        {
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCardName()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CardDetails.CardName', $this->m_cdCardDetails->getCardName());
	            }
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCV2()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CV2', $this->m_cdCardDetails->getCV2());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCardNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CardNumber', $this->m_cdCardDetails->getCardNumber());
	            }
	            if ($this->m_cdCardDetails->getExpiryDate() != null)
	            {
	                if ($this->m_cdCardDetails->getExpiryDate()->getMonth() != null)
	                {
	                	if ($this->m_cdCardDetails->getExpiryDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.ExpiryDate', 'Month', (string)$this->m_cdCardDetails->getExpiryDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdCardDetails->getExpiryDate()->getYear() != null)
	                {
	                    if ($this->m_cdCardDetails->getExpiryDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.ExpiryDate', 'Year', (string)$this->m_cdCardDetails->getExpiryDate()->getYear()->getValue());
	                    }
	               	}
	            }
	            if ($this->m_cdCardDetails->getStartDate() != null)
	            {
	                if ($this->m_cdCardDetails->getStartDate()->getMonth() != null)
	                {
	                	if ($this->m_cdCardDetails->getStartDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.StartDate', 'Month', (string)$this->m_cdCardDetails->getStartDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdCardDetails->getStartDate()->getYear() != null)
	                {
	                    if ($this->m_cdCardDetails->getStartDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.StartDate', 'Year', (string)$this->m_cdCardDetails->getStartDate()->getYear()->getValue());
	                    }
	                }
	            }
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getIssueNumber()))
	            {
	               	$sSOAPClient->addParam('PaymentMessage.CardDetails.IssueNumber', $this->m_cdCardDetails->getIssueNumber());
	            }
	        }
	        // customer details
	        if ($this->m_cdCustomerDetails != null)
	        {
	        	if ($this->m_cdCustomerDetails->getBillingAddress() != null)
	            {
	             	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress1()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address1', $this->m_cdCustomerDetails->getBillingAddress()->getAddress1());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress2()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address2', $this->m_cdCustomerDetails->getBillingAddress()->getAddress2());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress3()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address3', $this->m_cdCustomerDetails->getBillingAddress()->getAddress3());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress4()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address4', $this->m_cdCustomerDetails->getBillingAddress()->getAddress4());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getCity()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.City', $this->m_cdCustomerDetails->getBillingAddress()->getCity());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getState()))
	                {
	                  	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.State', $this->m_cdCustomerDetails->getBillingAddress()->getState());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getPostCode()))
	                {
	                   	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.PostCode', $this->m_cdCustomerDetails->getBillingAddress()->getPostCode());
	                }
	                if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode() != null)
	                {
	                  	if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getHasValue())
	                    {
	                   		$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.CountryCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getValue());
	                    }
	                }
	      		}
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getEmailAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.EmailAddress', $this->m_cdCustomerDetails->getEmailAddress());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getPhoneNumber()))
	            {
	              	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.PhoneNumber', $this->m_cdCustomerDetails->getPhoneNumber());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getCustomerIPAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.CustomerIPAddress', $this->m_cdCustomerDetails->getCustomerIPAddress());
	            }
	       	}
	       	
	       	$boTransactionSubmitted = GatewayTransaction::processTransaction($sSOAPClient, 'PaymentMessage', 'CardDetailsTransactionResult', 'TransactionOutputData', $XmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);

			if ($boTransactionSubmitted)
			{
				$tomTransactionOutputMessage = SharedFunctionsPaymentSystemShared::getTransactionOutputMessage($XmlDocument, $lgepGatewayEntryPoints);
			}

			return ($boTransactionSubmitted);
		}
	     
		public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	     								$nRetryAttempts,
	     								NullableInt $nTimeout = null,
	     								MerchantDetails $mdMerchantAuthentication = null,
	                                    TransactionDetails $tdTransactionDetails = null,
	                                    CardDetails $cdCardDetails = null,
	                                    CustomerDetails $cdCustomerDetails = null,
	                                    $szPassOutData)
	  	{
	    	parent::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $mdMerchantAuthentication, $szPassOutData);
	        	
	        $this->m_tdTransactionDetails = $tdTransactionDetails;
	        $this->m_cdCardDetails = $cdCardDetails;
	        $this->m_cdCustomerDetails = $cdCustomerDetails;
	    }
	     
	}
	class CrossReferenceTransaction extends GatewayTransaction 
	{
		private $m_tdTransactionDetails;
	    private $m_cdOverrideCardDetails;
	    private $m_cdCustomerDetails;

	    public function getTransactionDetails()
	    {
			return $this->m_tdTransactionDetails;
	    }
	    public function getOverrideCardDetails()
	    {
	    	return $this->m_cdOverrideCardDetails;
	    }
	    public function getCustomerDetails()
	    {
	    	return $this->m_cdCustomerDetails;
	    }
	        
	    public function processTransaction(GatewayOutput &$goGatewayOutput = null, TransactionOutputMessage &$tomTransactionOutputMessage = null)
	    {
	    	$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $sxXmlDocument = null;

	        $tomTransactionOutputMessage = null;
	        $goGatewayOutput = null;

	        $sSOAPClient = new SOAP('CrossReferenceTransaction', GatewayTransaction::getSOAPNamespace());
	      	// transaction details
	        if ($this->m_tdTransactionDetails != null)
	        {
	        	if ($this->m_tdTransactionDetails->getAmount() != null)
	          	{
	             	if ($this->m_tdTransactionDetails->getAmount()->getHasValue())
	                {
	               		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'Amount', (string)$this->m_tdTransactionDetails->getAmount()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getCurrencyCode() != null)
	            {
	                if ($this->m_tdTransactionDetails->getCurrencyCode()->getHasValue())
	                {
	                    $sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'CurrencyCode', (string)$this->m_tdTransactionDetails->getCurrencyCode()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getMessageDetails() != null)
	            {
	                if ($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()->getHasValue())
	                    {
	                     	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'TransactionType', SharedFunctionsPaymentSystemShared::getTransactionType($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()->getValue()));
	                    }
	            	}
	                if ($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction()->getHasValue())
	                    {
	                        $sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'NewTransaction', SharedFunctions::boolToString($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction()->getValue()));
	                    }
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getMessageDetails()->getCrossReference()))
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'CrossReference', $this->m_tdTransactionDetails->getMessageDetails()->getCrossReference());
	                }
	           	}
	           	if ($this->m_tdTransactionDetails->getTransactionControl() != null)
	           	{
	             	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAuthCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AuthCode', $this->m_tdTransactionDetails->getTransactionControl()->getAuthCode());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AVSOverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.CV2OverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy());
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.DuplicateDelay', (string)($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getHasValue())
	                    {
	                   		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCardType', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                  	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                  	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCV2CheckResult', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getValue()));
	                    }
	              	}
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAmountReceived', SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getValue()));
		               	}
	                }
	         	}
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderID()))
	            {
	           		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderID', $this->m_tdTransactionDetails->getOrderID());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderDescription()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderDescription', $this->m_tdTransactionDetails->getOrderDescription());
	            }
	        }
	        // card details
	       	if ($this->m_cdOverrideCardDetails != null)
	        {
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdOverrideCardDetails->getCardName()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.OverrideCardDetails.CardName', $this->m_cdOverrideCardDetails->getCardName());
	            }
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdOverrideCardDetails->getCV2()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CV2', $this->m_cdOverrideCardDetails->getCV2());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdOverrideCardDetails->getCardNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.OverrideCardDetails.CardNumber', $this->m_cdOverrideCardDetails->getCardNumber());
	            }
	            if ($this->m_cdOverrideCardDetails->getExpiryDate() != null)
	            {
	                if ($this->m_cdOverrideCardDetails->getExpiryDate()->getMonth() != null)
	                {
	                	if ($this->m_cdOverrideCardDetails->getExpiryDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.ExpiryDate', 'Month', (string)$this->m_cdOverrideCardDetails->getExpiryDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdOverrideCardDetails->getExpiryDate()->getYear() != null)
	                {
	                    if ($this->m_cdOverrideCardDetails->getExpiryDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.ExpiryDate', 'Year', (string)$this->m_cdOverrideCardDetails->getExpiryDate()->getYear()->getValue());
	                    }
	                }
	            }
	            if ($this->m_cdOverrideCardDetails->getStartDate() != null)
	            {
	              	if ($this->m_cdOverrideCardDetails->getStartDate()->getMonth() != null)
	                {
	                	if ($this->m_cdOverrideCardDetails->getStartDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.StartDate', 'Month', (string)$this->m_cdOverrideCardDetails->getStartDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdOverrideCardDetails->getStartDate()->getYear() != null)
	                {
	                   	if ($this->m_cdOverrideCardDetails->getStartDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.StartDate', 'Year', (string)$this->m_cdOverrideCardDetails->getStartDate()->getYear()->getValue());
	                    }
	                }
	            }
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdOverrideCardDetails->getIssueNumber()))
	            {
	               	$sSOAPClient->addParam('PaymentMessage.CardDetails.IssueNumber', $this->m_cdOverrideCardDetails->getIssueNumber());
	            }
	        }
	        // customer details
			if ($this->m_cdCustomerDetails != null)
	        {
	        	if ($this->m_cdCustomerDetails->getBillingAddress() != null)
	            {
	             	if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress1()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address1', $this->m_cdCustomerDetails->getBillingAddress()->getAddress1());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress2()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address2', $this->m_cdCustomerDetails->getBillingAddress()->getAddress2());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress3()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address3', $this->m_cdCustomerDetails->getBillingAddress()->getAddress3());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress4()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address4', $this->m_cdCustomerDetails->getBillingAddress()->getAddress4());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getCity()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.City', $this->m_cdCustomerDetails->getBillingAddress()->getCity());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getState()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.State', $this->m_cdCustomerDetails->getBillingAddress()->getState());
	                }
	                if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getPostCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.PostCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getPostCode());
	                }
	                if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode() != null)
	                {
	                    if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.CountryCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getValue());
	                    }
	                }
	         	}
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getEmailAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.EmailAddress', $this->m_cdCustomerDetails->getEmailAddress());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getPhoneNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CustomerDetails.PhoneNumber', $this->m_cdCustomerDetails->getPhoneNumber());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getCustomerIPAddress()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CustomerDetails.CustomerIPAddress', $this->m_cdCustomerDetails->getCustomerIPAddress());
	            }
	        }
	        
	        $boTransactionSubmitted = GatewayTransaction::processTransaction($sSOAPClient, 'PaymentMessage', 'CrossReferenceTransactionResult', 'TransactionOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);

	       	if ($boTransactionSubmitted)
	        {
	        	$tomTransactionOutputMessage = SharedFunctionsPaymentSystemShared::getTransactionOutputMessage($sxXmlDocument, $lgepGatewayEntryPoints);
	        }

	        return $boTransactionSubmitted;
	    }
	    
	    //constructor
	    public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	    							$nRetryAttempts,
	    							NullableInt $nTimeout = null,
	    							MerchantDetails $mdMerchantAuthentication = null,
	    							TransactionDetails $tdTransactionDetails = null,
	    							CardDetails $cdOverrideCardDetails = null,
	    							CustomerDetails $cdCustomerDetails = null,
	    							$szPassOutData)
	    {
	    	GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $mdMerchantAuthentication, $szPassOutData);
		    	
		    $this->m_tdTransactionDetails = $tdTransactionDetails;
	      	$this->m_cdOverrideCardDetails = $cdOverrideCardDetails;
	       	$this->m_cdCustomerDetails = $cdCustomerDetails;
	    }
	}

	class ThreeDSecureAuthentication extends GatewayTransaction
	{
		private $m_tdsidThreeDSecureInputData;
		
		public function getThreeDSecureInputData()
		{
			return $this->m_tdsidThreeDSecureInputData;
		}
		
		public function processTransaction(GatewayOutput &$goGatewayOutput = null, TransactionOutputMessage &$tomTransactionOutputMessage = null)
		{
			$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $sxXmlDocument = null;

	        $tomTransactionOutputMessage = null;
	        $goGatewayOutput = null;

	       	$sSOAPClient = new SOAP('ThreeDSecureAuthentication', GatewayTransaction::getSOAPNamespace());
	       	if ($this->m_tdsidThreeDSecureInputData != null)
	        {
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_tdsidThreeDSecureInputData->getCrossReference()))
	            {
	                $sSOAPClient->addParamAttribute('ThreeDSecureMessage.ThreeDSecureInputData', 'CrossReference', $this->m_tdsidThreeDSecureInputData->getCrossReference());
	            }
	            if (!SharedFunctions::isStringNullOrEmpty($this->m_tdsidThreeDSecureInputData->getPaRES()))
	            {
	            	$sSOAPClient->addParam('ThreeDSecureMessage.ThreeDSecureInputData.PaRES', $this->m_tdsidThreeDSecureInputData->getPaRES());
	            }
	        }
	        
	        $boTransactionSubmitted = GatewayTransaction::processTransaction($sSOAPClient, 'ThreeDSecureMessage', 'ThreeDSecureAuthenticationResult', 'TransactionOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
	       	
	        if ($boTransactionSubmitted)
	      	{
	        	$tomTransactionOutputMessage = SharedFunctionsPaymentSystemShared::getTransactionOutputMessage($sxXmlDocument, $lgepGatewayEntryPoints);
	        }

	        return $boTransactionSubmitted;
		}
		
		//constructor
		public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
									NullableInt $nTimeout = null,
									MerchantDetails $mdMerchantAuthentication = null,
	                              	ThreeDSecureInputData $tdsidThreeDSecureInputData = null,
	                                $szPassOutData)
	 	{
	    	GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $mdMerchantAuthentication, $szPassOutData);
	    	
	    	$this->m_tdsidThreeDSecureInputData = $tdsidThreeDSecureInputData;
	    }
	}

	class getCardType extends GatewayTransaction
	{
		private $m_szCardNumber;
		
		public function getCardNumber()
		{
			return $this->m_szCardNumber;
		}
		
		public function processTransaction(GatewayOutput &$goGatewayOutput = null, GetCardTypeOutputMessage &$gctomGetCardTypeOutputMessage = null)
		{
			$boTransactionSubmitted = false;
	        $sSOAPClient;
	       	$lgepGatewayEntryPoints = null;
	        $ctdCardTypeData = null;
	        $sxXmlDocument = null;

	       	$gctomGetCardTypeOutputMessage = null;
	        $goGatewayOutput = null;

	      	$sSOAPClient = new SOAP('GetCardType', GatewayTransaction::getSOAPNamespace());
	      	if (!SharedFunctions::isStringNullOrEmpty($this->m_szCardNumber))
	       	{
	        	$sSOAPClient->addParam('GetCardTypeMessage.CardNumber', $this->m_szCardNumber);
	        }
	        
	        $boTransactionSubmitted = GatewayTransaction::processTransaction($sSOAPClient, 'GetCardTypeMessage', 'GetCardTypeResult', 'GetCardTypeOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);

	        if ($boTransactionSubmitted)
	        {
	        	if(!$sxXmlDocument->GetCardTypeOutputData->CardTypeData)
	        	{
	        		$ctdCardTypeData = null;
	        	}
	        	else
	        	{
	            	$ctdCardTypeData = SharedFunctionsPaymentSystemShared::getCardTypeData($sxXmlDocument->GetCardTypeOutputData->CardTypeData);
	        	}
	        	
	            if (!is_null($ctdCardTypeData)) 
	            {
	                $gctomGetCardTypeOutputMessage = new GetCardTypeOutputMessage($ctdCardTypeData, $lgepGatewayEntryPoints);
	            } 
			}
	        return $boTransactionSubmitted;
		}
		
		//constructor
		public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
	                           		NullableInt $nTimeout = null,
	                           		MerchantDetails $mdMerchantAuthentication = null,
	                           		$szCardNumber,
	                          		$szPassOutData)
	  	{
	    	GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $mdMerchantAuthentication, $szPassOutData);

	    	$this->m_szCardNumber = $szCardNumber;	
	    }
	}

	abstract class GatewayTransaction
	{
	    private $m_mdMerchantAuthentication;
	 	private $m_szPassOutData;
	    private $m_lrgepRequestGatewayEntryPoints;
	    private $m_nRetryAttempts;
	    private $m_nTimeout;
	    private $m_szSOAPNamespace = 'https://www.thepaymentgateway.net/';
	    private $m_szLastRequest;
		private $m_szLastResponse;
		private $m_eLastException;

	   	public function getMerchantAuthentication()
	   	{
	      	return $this->m_mdMerchantAuthentication;
	   	}
	  
	  	public function getPassOutData()
	   	{
	    	return $this->m_szPassOutData;
	  	}
	   
	   	public function getRequestGatewayEntryPoints()
	   	{
	    	return $this->m_lrgepRequestGatewayEntryPoints;
	   	}
	   
	   	public function getRetryAttempts()
	   	{
	      	return $this->m_nRetryAttempts;
	   	}
	   
	   	public function getTimeout()
	   	{
	      	return $this->m_nTimeout;
	   	}
	   
	   	public function getSOAPNamespace()
	   	{
	      	return $this->m_szSOAPNamespace;
	   	}
	   	public function setSOAPNamespace($value)
	   	{
	      	$this->m_szSOAPNamespace = $value;
	   	}
	   	
	   	public function getLastRequest()
	   	{
	   		return $this->m_szLastRequest;
	   	}
	   	
	   	public function getLastResponse()
	   	{
	   		return $this->m_szLastResponse;
	   	}
	   	
	   	public function getLastException()
	   	{
	   		return $this->m_eLastException;
	   	}

	   	public static function compare($x, $y)
	   	{
	      	$rgepFirst = null;
	      	$rgepSecond = null;
	     
	      	$rgepFirst = $x;
	      	$rgepSecond = $y;

	      	return (GatewayTransaction::compareGatewayEntryPoints($rgepFirst, $rgepSecond));
	   	}

	   	private static function compareGatewayEntryPoints(RequestGatewayEntryPoint $rgepFirst, RequestGatewayEntryPoint $rgepSecond)
	   	{
			$nReturnValue = 0;
	      	// returns >0 if rgepFirst greater than rgepSecond
	      	// returns 0 if they are equal
	      	// returns <0 if rgepFirst less than rgepSecond
	      
	      	// both null, then they are the same
	      	if ($rgepFirst == null &&
	          	$rgepSecond == null)
	   		{
	        	$nReturnValue = 0;
	        }
	      	// just first null? then second is greater
	      	elseif ($rgepFirst == null &&
		    		$rgepSecond != null)
	      	{
	        	$nReturnValue = 1;
	        }
	      	// just second null? then first is greater
	      	elseif ($rgepFirst != null  && $rgepSecond == null)
	      	{
	        	$nReturnValue = -1;
	        }
	      	// can now assume that first & second both have a value
	      	elseif ($rgepFirst->getMetric() == $rgepSecond->getMetric())
	        {
	        	$nReturnValue = 0;
	        }
	      	elseif ($rgepFirst->getMetric() < $rgepSecond->getMetric())
	        {
	        	$nReturnValue = -1;
	        }
	      	elseif ($rgepFirst->getMetric() > $rgepSecond->getMetric())
		    {
				$nReturnValue = 1;
	  	    }

	      	return $nReturnValue;
	   	}

	   	protected function processTransaction(SOAP $sSOAPClient, $szMessageXMLPath, $szGatewayOutputXMLPath, $szTransactionMessageXMLPath, SimpleXMLElement &$sxXmlDocument = null, GatewayOutput &$goGatewayOutput = null, GatewayEntryPointList &$lgepGatewayEntryPoints = null)
	   	{
			$boTransactionSubmitted = false;
		    $nOverallRetryCount = 0;
		    $nOverallGatewayEntryPointCount = 0;
		    $nGatewayEntryPointCount = 0;
		    $nErrorMessageCount = 0;
		    $rgepCurrentGatewayEntryPoint;
		    $nStatusCode;
		    $szMessage = null;
		    $lszErrorMessages;
		    $szString;
		    $sbXMLString;
		    $szXMLFormatString;
		    $nCount = 0;
		    $szEntryPointURL;
		    $nMetric;
		    $nTempValue = 0;
		    $gepGatewayEntryPoint = null;
		    $boAuthorisationAttempted = null;
		    $boTempValue;
		    $szPassOutData = null;
		    $nPreviousStatusCode = null;
		    $szPreviousMessage = null;
		    $ptdPreviousTransactionResult = null;
		    $ResponseDocument = null;
		    $ResponseMethod = null;

	      	$lgepGatewayEntryPoints = null;
	      	$goGatewayOutput = null;

	      	if ($sSOAPClient == null)
	      	{
	        	return false;
	      	}

	       	// populate the merchant details
	       	if ($this->m_mdMerchantAuthentication != null)
	       	{
	        	if (!SharedFunctions::isStringNullOrEmpty($this->m_mdMerchantAuthentication->getMerchantID()))
	          	{
	            	$sSOAPClient->addParamAttribute($szMessageXMLPath. '.MerchantAuthentication', 'MerchantID', $this->m_mdMerchantAuthentication->getMerchantID());
	          	}
	          	if (!SharedFunctions::isStringNullOrEmpty($this->m_mdMerchantAuthentication->getPassword()))
	          	{
	             	$sSOAPClient->addParamAttribute($szMessageXMLPath. '.MerchantAuthentication', 'Password', $this->m_mdMerchantAuthentication->getPassword());
	          	}
	       	}
	       	// populate the passout data
	       	if (!SharedFunctions::isStringNullOrEmpty($this->m_szPassOutData))
	       	{
	        	$sSOAPClient->addParam($szMessageXMLPath. '.PassOutData', $this->m_szPassOutData, null);
	       	}

	      	// first need to sort the gateway entry points into the correct usage order
	       	$number = $this->m_lrgepRequestGatewayEntryPoints->sort('GatewayTransaction','Compare');
	       
	       	// loop over the overall number of transaction attempts
	       	while (!$boTransactionSubmitted &&
	       			$nOverallRetryCount < $this->m_nRetryAttempts) 
	       	{
	       		$nOverallGatewayEntryPointCount = 0;
	       			
	       		// loop over the number of gateway entry points in the list
	            while (!$boTransactionSubmitted &&
	                 	$nOverallGatewayEntryPointCount < $this->m_lrgepRequestGatewayEntryPoints->getCount())
	          	{
	       			
					$rgepCurrentGatewayEntryPoint = $this->m_lrgepRequestGatewayEntryPoints->getAt($nOverallGatewayEntryPointCount);
					
					// ignore if the metric is "-1" this indicates that the entry point is offline
	              	if ($rgepCurrentGatewayEntryPoint->getMetric() >= 0)
	                {
	              		$nGatewayEntryPointCount = 0;
	                 	$sSOAPClient->setURL($rgepCurrentGatewayEntryPoint->getEntryPointURL());
						
	                    // loop over the number of times to try this specific entry point
	                    while (!$boTransactionSubmitted &&
	                          	$nGatewayEntryPointCount < $rgepCurrentGatewayEntryPoint->getRetryAttempts())
	                  	{
	                    	if ($sSOAPClient->sendRequest($ResponseDocument, $ResponseMethod))
	                        {
	                        	//getting the valid transaction type document format
	                        	$sxXmlDocument = $ResponseDocument->$ResponseMethod;
	                        	
	                        	$lszErrorMessages = new StringList();
	                        	
								$nStatusCode = (int)current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->StatusCode[0]);

								// a status code of 50 means that this entry point is not to be used
								if ($nStatusCode != 50)
								{
		                        	// the transaction was submitted
		                        	$boTransactionSubmitted = true;

									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->Message)
									{
										$szMessage = current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->Message[0]);
									}
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->ErrorMessages)
									{
										foreach ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->ErrorMessages->MessageDetail as $key => $value)
										{
											$lszErrorMessages->add(current($value->Detail));
 										}
									}
									
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->attributes())
									{
										foreach ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->attributes() as $key => $value)
										{
											$boAuthorisationAttempted = current($value);
											if (strtolower($boAuthorisationAttempted) == 'false')
											{
												$boAuthorisationAttempted = new NullableBool(false);
											}
											elseif (strtolower($boAuthorisationAttempted) == 'true')
											{
												$boAuthorisationAttempted = new NullableBool(true);
											}
											else 
											{
												throw new Exception('Return value must be true or false');
											}
										}
									}
									
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PassOutData)
									{
										$szPassOutData = current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PassOutData[0]);
									}
									else 
									{
										$szPassOutData = null;
									}
									
									//check to see if there is any previous transaction data
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PreviousTransactionResult->StatusCode)
									{
										$nPreviousStatusCode = new NullableInt(current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PreviousTransactionResult->StatusCode[0]));
									}
									else 
									{
										$nPreviousStatusCode = null;
									}
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PreviousTransactionResult->Message)
									{
										$szPreviousMessage = current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PreviousTransactionResult->Message[0]);
									}
									
									if ($nPreviousStatusCode != null &&
										!SharedFunctions::isStringNullOrEmpty($szPreviousMessage))
									{
										$ptdPreviousTransactionResult = new PreviousTransactionResult($nPreviousStatusCode, $szPreviousMessage);		
									}
									
									$goGatewayOutput = new GatewayOutput($nStatusCode, $szMessage, $szPassOutData, $boAuthorisationAttempted, $ptdPreviousTransactionResult, $lszErrorMessages);
		                                
		                            // look to see if there are any gateway entry points
		                            $nCount = 0;
		                            
		                            $nMetric = -1;
		                            
		                            if ($ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints)
		                            {
		                            	if($ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints->GatewayEntryPoint)
		                            	{
			                            	$szXMLFormatString = $ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints->GatewayEntryPoint;
			                            	
					                      	foreach($szXMLFormatString->attributes() as $key => $value)
					                        {
					                          	if (is_numeric(current($value)))
					                           	{
					                           		$nMetric = current($value);
					                           	}
					                           	else 
					                           	{
					                           		$szEntryPointURL = current($value);
					                           	}
					                       	}
				                            
				                            if ($lgepGatewayEntryPoints == null)
				                            {
				                            	$lgepGatewayEntryPoints = new GatewayEntryPointList();
				                            }
				                            $lgepGatewayEntryPoints->add($szEntryPointURL, $nMetric);
		                            	}
		                            }
		                            $nCount++;
								}
	                    	}
	                            
	                        $nGatewayEntryPointCount++;
	                  	}
	              	}
	                $nOverallGatewayEntryPointCount++;
	       		}
	       		$nOverallRetryCount++;
	   		}
	   		$this->m_szLastRequest = $sSOAPClient->getSOAPPacket();
	   		$this->m_szLastResponse = $sSOAPClient->getLastResponse();
	   		$this->m_eLastException = $sSOAPClient->getLastException();

	   		return $boTransactionSubmitted;
		}
		
		public function __construct(RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
									NullableInt $nTimeout = null,
									MerchantDetails $mdMerchantAuthentication = null,
									$szPassOutData)
		{
			$this->m_mdMerchantAuthentication = $mdMerchantAuthentication;
			$this->m_szPassOutData = $szPassOutData;
			$this->m_lrgepRequestGatewayEntryPoints = $lrgepRequestGatewayEntryPoints;
			$this->m_nRetryAttempts = $nRetryAttempts;
			$this->m_nTimeout = $nTimeout;
		}
	}

	class SharedFunctionsPaymentSystemShared
	{
		public static function getTransactionOutputMessage(SimpleXMLElement $sxXmlDocument, GatewayEntryPointList $lgepGatewayEntryPoints = null)
		{
			$szCrossReference = null;
	        $crAddressNumericCheckResult = null;
	        $crPostCodeCheckResult = null;
	        $crThreeDSecureAuthenticationCheckResult = null;
	        $crCV2CheckResult = null;
	        $szAddressNumericCheckResult = null;
	        $szPostCodeCheckResult = null;
	        $szThreeDSecureAuthenticationCheckResult = null;
	        $szCV2CheckResult = null;
	        $nAmountReceived = null;
	        $szPaREQ = null;
	        $szACSURL = null;
	        $nTempValue;
	        $ctdCardTypeData = null;
	        $tdsodThreeDSecureOutputData = null;
	        $lgvCustomVariables = null;
	        $nCount = 0;
	        $sbString;
	        $szXMLFormatString;
	        $szName;
	        $szValue;
	        $gvGenericVariable;
	        $nCount = 0;
	        $szCardTypeData;
	        
	        $tomTransactionOutputMessage = null;

			if (!$sxXmlDocument->TransactionOutputData)
			{
				return (null);
			}

		    if ($sxXmlDocument->TransactionOutputData->attributes())
		    {
		    	foreach($sxXmlDocument->TransactionOutputData->attributes() as $key => $value)
		    	{
		    		$szCrossReference = current($value);
		    	}
		    }
		    else 
		    {
		    	$szCrossReference = null;
		    }

			if ($sxXmlDocument->TransactionOutputData->AuthCode)
			{
				$szAuthCode = current($sxXmlDocument->TransactionOutputData->AuthCode[0]);
			}
			else
			{
				$szAuthCode = null;
			}

			if ($sxXmlDocument->TransactionOutputData->AddressNumericCheckResult)
			{
				$crAddressNumericCheckResult = new NullableCHECK_RESULT(current($sxXmlDocument->TransactionOutputData->AddressNumericCheckResult[0]));
			}
			else
			{
				$crAddressNumericCheckResult = new NullableCHECK_RESULT(null);
			}
			
			if ($sxXmlDocument->TransactionOutputData->PostCodeCheckResult)
			{
		    	$crPostCodeCheckResult = new NullableCHECK_RESULT(current($sxXmlDocument->TransactionOutputData->PostCodeCheckResult[0]));
			}
			else 
			{
				$crPostCodeCheckResult = new NullableCHECK_RESULT(null);
			}
		    
		    if ($sxXmlDocument->TransactionOutputData->ThreeDSecureAuthenticationCheckResult)
		    {
				$crThreeDSecureAuthenticationCheckResult = new NullableCHECK_RESULT(current($sxXmlDocument->TransactionOutputData->ThreeDSecureAuthenticationCheckResult[0]));
		    }
		    else 
		    {
		    	$crThreeDSecureAuthenticationCheckResult = new NullableCHECK_RESULT(null);
		    }

			if ($sxXmlDocument->TransactionOutputData->CV2CheckResult)
			{
		    	$crCV2CheckResult = new NullableCHECK_RESULT(current($sxXmlDocument->TransactionOutputData->CV2CheckResult[0]));
			}
			else 
			{
				$crCV2CheckResult = new NullableCHECK_RESULT(null);
			}
		    
		    if ($sxXmlDocument->TransactionOutputData->CardTypeData)
		    {
		    	$ctdCardTypeData = self::getCardTypeData($sxXmlDocument->TransactionOutputData->CardTypeData);
		    }
		    else 
		    {
		    	$ctdCardTypeData = null;
		    }

			if ($sxXmlDocument->TransactionOutputData->AmountReceived)
			{
		    	$nAmountReceived = new NullableInt(current($sxXmlDocument->TransactionOutputData->AmountReceived[0]));
			}
			else 
			{
				$nAmountReceived = new NullableInt(null);
			}

			if ($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData)
			{
				$szPaREQ = current($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData->PaREQ[0]);
				$szACSURL = current($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData->ACSURL[0]);
			}
			else 
			{
				$szPaREQ = null;
				$szACSURL = null;
			}
			

		    if (!SharedFunctions::isStringNullOrEmpty($szACSURL) &&
		    	!SharedFunctions::isStringNullOrEmpty($szPaREQ))
		    {
		    	$tdsodThreeDSecureOutputData = new ThreeDSecureOutputData($szPaREQ, $szACSURL);
		    }
		        
			if ($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable)
			{
				if ($lgvCustomVariables == null)
				{
					$lgvCustomVariables = new GenericVariableList();
				}
				for ($nCount=0; $nCount < count($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable); $nCount++)
				{
					$szName = current($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable[$nCount]->Name[0]);
					$szValue = current($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable[$nCount]->Value[0]);
					$gvGenericVariable = new GenericVariable($szName, $szValue);
					$lgvCustomVariables->add($gvGenericVariable);
				}
			}
			else 
			{
				$lgvCustomVariables = null;
			}


		    $tomTransactionOutputMessage = new TransactionOutputMessage($szCrossReference,
																		$szAuthCode,
															         	$crAddressNumericCheckResult,
															            $crPostCodeCheckResult,
															            $crThreeDSecureAuthenticationCheckResult,
															            $crCV2CheckResult,
															            $ctdCardTypeData,
															            $nAmountReceived,
															            $tdsodThreeDSecureOutputData,
															            $lgvCustomVariables,
															            $lgepGatewayEntryPoints);
			
	     	return $tomTransactionOutputMessage;
		}

		public static function getCardTypeData($CardTypeDataTag)
		{
			$ctdCardTypeData = null;
	        $nTempValue;
	        $boTempValue;
	        $ctCardType = null;
	        $boLuhnCheckRequired = null;
	        $cdsStartDateStatus = null;
	        $cdsIssueNumberStatus = null;
	        $szCardType;
	        $szIssuer = null;
	        $nISOCode = null;
	        $iIssuer = null;

			if ($CardTypeDataTag->CardType)
			{
                try
                {
				    $ctCardType = self::getCardType((string)$CardTypeDataTag->CardType[0]);
                }
				catch (Exception $e) 
				{
				}
			}
			
			if ($CardTypeDataTag->Issuer)
			{
				try 
				{
					$szIssuer = (string) $CardTypeDataTag->Issuer[0];
				} 
				catch (Exception $e) 
				{
				}
				
    			if ($CardTypeDataTag->Issuer->attributes()->ISOCode)
	    		{
		    		try
			    	{
				    	$nTempValue = current($CardTypeDataTag->Issuer->attributes()->ISOCode);
                        $nISOCode = new NullableInt($nTempValue);
    				}
	    			catch (Exception $e)
		    		{
				    }
                }
				
				$iIssuer = new Issuer($szIssuer, $nISOCode);
			}
			
			if ($CardTypeDataTag->LuhnCheckRequired)
			{
				try 
				{
    				$boLuhnCheckRequired = new NullableBool(current($CardTypeDataTag->LuhnCheckRequired[0]));
                }
				catch (Exception $e) 
				{
				}
			}
			
			if ($CardTypeDataTag->StartDateStatus)
			{
				try 
				{
					$cdsStartDateStatus = new NullableCARD_DATA_STATUS(self::getCardDataStatus((string)$CardTypeDataTag->StartDateStatus[0]));
				} 
				catch (Exception $e) 
				{
				}
			}
			
			if ($CardTypeDataTag->IssueNumberStatus)
			{
				try 
				{
					$cdsIssueNumberStatus = new NullableCARD_DATA_STATUS(self::getCardDataStatus((string)$CardTypeDataTag->IssueNumberStatus[0]));
				} 
				catch (Exception $e) 
				{
				}
			}
			
			$ctdCardTypeData = new CardTypeData($ctCardType, $iIssuer, $boLuhnCheckRequired, $cdsIssueNumberStatus, $cdsStartDateStatus);

	        return ($ctdCardTypeData);
		}
		
		public static function getCardType($CardType)
		{
			if ($CardType instanceof CARD_TYPE)
			{
				return (string)$CardType;
			}
			elseif (is_string($CardType))
			{
				$ctCardType = CARD_TYPE::UNKNOWN;
				
				if ($CardType == null ||
					!is_string($CardType))
		       	{
		         	throw new Exception('Invalid card type: ' . $CardType);
		        }
		        if (strtoupper($CardType) == 'AMERICAN_EXPRESS')
		      	{
		        	$ctCardType = CARD_TYPE::AMERICAN_EXPRESS;
		        }
		       	elseif (strtoupper($CardType) == 'DINERS_CLUB')
		      	{
		        	$ctCardType = CARD_TYPE::DINERS_CLUB;
		        }
		        elseif (strtoupper($CardType) == 'JCB')
		      	{
		        	$ctCardType = CARD_TYPE::JCB;
		        }
		        elseif (strtoupper($CardType) == 'MASTERCARD')
		      	{
		        	$ctCardType = CARD_TYPE::MASTERCARD;
		        }
		        elseif (strtoupper($CardType) == 'SOLO')
		      	{
		        	$ctCardType = CARD_TYPE::SOLO;
		        }
		        elseif (strtoupper($CardType) == 'VISA_ELECTRON')
		      	{
		        	$ctCardType = CARD_TYPE::VISA_ELECTRON;
		        }
		        elseif (strtoupper($CardType) == 'MAESTRO')
		      	{
		        	$ctCardType = CARD_TYPE::MAESTRO;
		        }
		        elseif (strtoupper($CardType) == 'VISA')
		      	{
		        	$ctCardType = CARD_TYPE::VISA;
		        }
		        elseif (strtoupper($CardType) == 'VISA_DEBIT')
		      	{
		        	$ctCardType = CARD_TYPE::VISA_DEBIT;
		        }
		        elseif (strtoupper($CardType) == 'VISA_PURCHASING')
		      	{
		        	$ctCardType = CARD_TYPE::VISA_PURCHASING;
		        }
		        elseif (strtoupper($CardType) == 'LASER')
		      	{
		        	$ctCardType = CARD_TYPE::LASER;
		        }
		        elseif (strtoupper($CardType) == 'DEBIT_MASTERCARD')
		      	{
		        	$ctCardType = CARD_TYPE::DEBIT_MASTERCARD;
		        }
		        
		        return $ctCardType;
			}
			else 
			{
				throw new Exception('Invalid card type' . $CardType);
			}
		}
		public static function getCheckResult($CheckResult)
		{
			if ($CheckResult instanceof CHECK_RESULT)
			{
				return (string)$CheckResult;
			}
			elseif (is_string($CheckResult))
			{
				$crCheckResult = CHECK_RESULT::UNKNOWN;
				
				if ($CheckResult == null ||
					!is_string($CheckResult))
		       	{
		         	throw new Exception('Invalid check result: ' . $CheckResult);
		        }
		        
		       	if (strtoupper($CheckResult) == 'FAILED')
		      	{
		        	$crCheckResult = CHECK_RESULT::FAILED;
		        }
		       	elseif (strtoupper($CheckResult) == 'PASSED')
		      	{
		        	$crCheckResult = CHECK_RESULT::PASSED;
		        }
		        elseif (strtoupper($CheckResult) == 'PARTIAL')
		      	{
		        	$crCheckResult = CHECK_RESULT::PARTIAL;
		        }
		        elseif (strtoupper($CheckResult) == 'ERROR')
		      	{
		        	$crCheckResult = CHECK_RESULT::ERROR;
		        }
		        elseif (strtoupper($CheckResult) == 'NOT_CHECKED')
		      	{
		        	$crCheckResult = CHECK_RESULT::NOT_CHECKED;
		        }
		        elseif (strtoupper($CheckResult) == 'NOT_SUBMITTED')
		      	{
		        	$crCheckResult = CHECK_RESULT::NOT_SUBMITTED;
		        }
		        elseif (strtoupper($CheckResult) == 'NOT_ENROLLED')
		      	{
		        	$crCheckResult = CHECK_RESULT::NOT_ENROLLED;
		        }
		        
		        return $crCheckResult;
			}
			else 
			{
				throw new Exception('Invalid check result' . $CheckResult);
			}
	        
		}
		public static function getTransactionType($TransactionType)
		{
			if ($TransactionType instanceof TRANSACTION_TYPE)
			{
				return (string)$TransactionType;
			}
			elseif (is_string($TransactionType))
			{
				$ttTransactionType = TRANSACTION_TYPE::UNKNOWN;
				
				if ($TransactionType == null ||
					!is_string($TransactionType))
		       	{
		         	throw new Exception('Invalid transaction type: ' . $TransactionType);
		        }
		
		       	if (strtoupper($TransactionType) == 'COLLECTION')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::COLLECTION;
		        }
		       	elseif (strtoupper($TransactionType) == 'PREAUTH')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::PREAUTH;
		        }
		        elseif (strtoupper($TransactionType) == 'REFUND')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::REFUND;
		        }
		        elseif (strtoupper($TransactionType) == 'RETRY')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::RETRY;
		        }
		        elseif (strtoupper($TransactionType) == 'SALE')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::SALE;
		        }
		        elseif (strtoupper($TransactionType) == 'VOID')
		      	{
		        	$ttTransactionType = TRANSACTION_TYPE::VOID;
		        }
		        
		        if ($ttTransactionType == TRANSACTION_TYPE::UNKNOWN)
		       	{
		        	throw new Exception('Invalid transaction type: ' . $szTransactionType);
		        }
		        
		        return ($ttTransactionType);
			}
			else 
			{
				throw new Exception('Invalid transaction type' . $TransactionType);
			}
		}
		public static function getCardDataStatus($CardDataStatus)
		{
			if ($CardDataStatus instanceof CARD_DATA_STATUS)
			{
				return (string)$CardDataStatus;
			}
			elseif (is_string($CardDataStatus))
			{
				$cdsCardDataStatus = CARD_DATA_STATUS::UNKNOWN;
				
				if ($CardDataStatus == null ||
					!is_string($CardDataStatus))
	            {
	                throw new Exception("Invalid card data status: " + $CardDataStatus);
	            }
	            
	            if (strtoupper($CardDataStatus) == 'DO_NOT_SUBMIT')
		      	{
		        	$cdsCardDataStatus = CARD_DATA_STATUS::DO_NOT_SUBMIT;
		        }
		       	elseif (strtoupper($CardDataStatus) == 'IGNORED_IF_SUBMITTED')
		      	{
		        	$cdsCardDataStatus = CARD_DATA_STATUS::IGNORED_IF_SUBMITTED;
		        }
		        elseif (strtoupper($CardDataStatus) == 'MUST_BE_SUBMITTED')
		      	{
		        	$cdsCardDataStatus = CARD_DATA_STATUS::MUST_BE_SUBMITTED;
		        }
		        elseif (strtoupper($CardDataStatus) == 'SUBMIT_ONLY_IF_ON_CARD')
		      	{
		        	$cdsCardDataStatus = CARD_DATA_STATUS::SUBMIT_ONLY_IF_ON_CARD;
		        }
		        
		        return ($cdsCardDataStatus);
			}
		}
	}
?>