<?php
	//XMLEntities
	$g_XMLEntities = array();
	$g_XMLEntities[] = new XMLEntity(0x26, "&amp;");
	$g_XMLEntities[] = new XMLEntity(0x22, "&quot;");
	$g_XMLEntities[] = new XMLEntity(0x27, "&apos;");
	$g_XMLEntities[] = new XMLEntity(0x3c, "&lt;");
	$g_XMLEntities[] = new XMLEntity(0x3e, "&gt;");

	abstract class Nullable
	{
		protected $m_boHasValue;
		
		function getHasValue()
		{
			return $this->m_boHasValue;
		}
		
		public function __construct()
		{
			$this->m_boHasValue = false;	
		}
	}

	class NullableInt extends Nullable 
	{
		private $m_nValue;
		
		function getValue()
		{
			if ($this->m_boHasValue == false)
			{
				throw new Exception('Object has no value');
			}
			
			return $this->m_nValue;
		}
		function setValue($value)
		{
			$this->m_boHasValue = true;
			$this->m_nValue = $value;
		}
		
		//constructor
		public function __construct($nValue)
		{
			Nullable::__construct();
			
			$this->setValue($nValue);
		}
	}

	class NullableBool extends Nullable 
	{
		private $m_boValue;
		
		public function getValue()
		{
			if ($this->m_boHasValue == false)
			{
				throw new Exception("Object has no value");
			}
			
			return ($this->m_boValue); 
		}
		public  function setValue($value)
		{
			$this->m_boHasValue = true;
			$this->m_boValue = $value;
		}
		
		//constructor
		public function __construct($boValue)
		{
			Nullable::__construct();
			
			$this->setValue($boValue);
		}
	}

	/******************/
	/* Common classes */
	/******************/
	class StringList
	{
		private $m_lszStrings;
		
		public function getAt($nIndex)
		{
			if ($nIndex < 0 ||
				$nIndex >= count($this->m_lszStrings))
				{
					throw new Exception('Array index out of bounds');
				}
				
				return (string)($this->m_lszStrings[$nIndex]);
		}
		
		function getCount()
		{
			return count($this->m_lszStrings);
		}
		
		function add($szString)
		{
			if (!is_string($szString))
			{
				throw new Exception('Invalid parameter type');
			}
		
			return ($this->m_lszStrings[] = $szString);
		}
		
		//constructor
		function __construct()
		{
			$this->m_lszStrings = array();
		}
	}

	class ISOCountry
	{
		private $m_szCountryName;
		private $m_szCountryNameShort;
		private $m_nISOCode;
		private $m_nListPriority;
		
		//public properties
		public function getCountryName()
		{
			return $this->m_szCountryName;
		}
		public function getCountryNameShort()
		{
			return $this->m_szCountryNameShort;
		}
		public function getISOCode()
		{
			return $this->m_nISOCode;
		}
		public function getListPriority()
		{
			return $this->m_nListPriority;
		}
		
		//constructor
		public function __construct($nISOCode, $szCountryName, $szCountryNameShort, $nListPriority)
		{
			if (!is_int($nISOCode) ||
				!is_string($szCountryName) ||
				!is_string($szCountryNameShort) ||
				!is_int($nListPriority))
			{
				throw new Exception('Invalid parameter type');
			}
				
			$this->m_nISOCode = $nISOCode;
			$this->m_szCountryName = $szCountryName;
			$this->m_szCountryNameShort = $szCountryNameShort;
			$this->m_nListPriority = $nListPriority;
		}
	}

	class ISOCountryList
	{
		private $m_licISOCountries;
		
		public function getISOCountry($nISOCode, ISOCountry & $icISOCountry)
		{
			$boFound = false;
			$nCount = 0;
			$icISOCountry2;
			
			$icISOCountry = null;
			
			if (!is_int($nISOCode))
			{
				throw new Exception('Invalid parameter type:$nISOCode');
			}
			
			while(!$boFound &&
					$nCount < count($this->m_licISOCountries))
			{
				$icISOCountry2 = ISOCountry ($this->m_licISOCountries[$nCount]);
						
				if ($nISOCode == $icISOCountry2->getISOCode())
			    {
			    	$icISOCountry = new ISOCountry($icISOCountry2->getISOCode(), $icISOCountry2->getCountryName(), $icISOCountry2->getCountryNameShort(), $icISOCountry2->getListPriority());
			    	$boFound = true;
			    }
			                
		        $nCount++;
			}
					
			return $boFound;
		}
		
		public function getCount()
		{
			return count($this->m_licISOCountries);
		}
		
		public function getAt($nIndex)
		{
			if ($nIndex < 0 ||
				$nIndex >= count($this->m_licISOCountries))
			{
				throw new Exception('Array index out of bounds');
			}
				
			return $this->m_licISOCountries[$nIndex];
		}
		
		public function add($nISOCode, $szCountryName, $szCountryNameShort, $nListPriority)
		{
			$newISOCountry = new ISOCountry($nISOCode, $szCountryName, $szCountryNameShort, $nListPriority);

			$this->m_licISOCountries[] = $newISOCountry;
		}

		//constructor
		public function __construct()
		{
	        $this->m_licISOCountries = array();
		}
	}

	class ISOCurrency
	{
		private $m_szCountryName;
	   	private $m_nExponent;
	    private $m_nISOCode;
	    private $m_szCurrency;
	    private $m_szCurrencyShort;
	    private $m_boInUse;
	    
	    //public properties
	    public function getCountryName()
	    {
	    	return $this->m_szCountryName;
	    }
	    
	    public function getExponent()
	    {
	    	return $this->m_nExponent;
	    }
	   
	    public function getCurrency()
	    {
	    	return $this->m_szCurrency;
	    }
	   
	    public function getCurrencyShort()
	    {
	    	return $this->m_szCurrencyShort;
	    }
	   
	    public function getISOCode()
	    {
	    	return $this->m_nISOCode;
	    }
	    
	    public function getInUse()
	    {
	    	return $this->m_boInUse;
	    }
	    
	    //constructor
	    public function __construct($nISOCode, $szCurrency, $szCurrencyShort, $szCountryName, $nExponent, $boInUse)
	    {
	    	$this->m_nISOCode = $nISOCode;
	    	$this->m_szCountryName = $szCountryName;
	    	$this->m_nExponent = $nExponent;
	    	$this->m_szCurrency = $szCurrency;
	    	$this->m_szCurrencyShort = $szCurrencyShort;
	    	$this->m_boInUse = $boInUse;
	    }
	}

	class ISOCurrencyList
	{
		private $m_licISOCurrencies;
		
		public function getISOCurrency($nISOCurrency, ISOCurrency &$icISOCurrency)
		{
			$boFound = false;
	        $nCount = 0;
	        $icISOCurrency2;

	       	$icISOCurrency = null;
	        
	        while (!$boFound &&
	              	$nCount < count($this->m_licISOCurrencies))
	     	{
	           	$icISOCurrency2 = $this->m_licISOCurrencies[$nCount];
	            	
	        	if ($nISOCurrency == $icISOCurrency2->getISOCode())
	            {
	            	$icISOCurrency = new ISOCurrency($icISOCurrency2->getISOCode(), $icISOCurrency2->getCurrency(),$icISOCurrency2->getCurrencyShort(), $icISOCurrency2->getCountryName(), $icISOCurrency2->getExponent(), $icISOCurrency2->getInuse()); //new ISOCurrency(((ISOCurrency) m_licISOCurrencies[nCount]).ISOCode, ((ISOCurrency) m_licISOCurrencies[nCount]).Currency, ((ISOCurrency) m_licISOCurrencies[nCount]).CurrencyShort, ((ISOCurrency) m_licISOCurrencies[nCount]).CountryName, ((ISOCurrency) m_licISOCurrencies[nCount]).Exponent, ((ISOCurrency) m_licISOCurrencies[nCount]).InUse);
	                $boFound = true;
	          	}
	                
	            $nCount++;
	        }

	      	return ($boFound);
		}
		
		public function getCount()
		{
			return count($this->m_licISOCurrencies);
		}
		
		public function getAt($nIndex)
		{
			if ($nIndex < 0 ||
	         	$nIndex >= count($this->m_licISOCurrencies))
	        {
	        	throw new Exception('Array index out of bounds');
	        }
	         	
	      	return $this->m_licISOCurrencies[$nIndex];
		}
		
		public function add($nISOCode, $szCurrency, $szCurrencyShort, $szCountryName, $nExponent, $boInUse)
		{
			$newISOCurrency = new ISOCurrency($nISOCode, $szCurrency, $szCurrencyShort, $szCountryName, $nExponent, $boInUse);

			$this->m_licISOCurrencies[] = $newISOCurrency;
		}

		//constructor
		public function __construct()
		{
	        $this->m_licISOCurrencies = array();
		}
	}

	class XMLEntity
	{
		private $m_bCharCode;
		private $m_szReplacement;
		
		public function getCharCode()
		{
			return $this->m_bCharCode;
		}
		public function getReplacement()
		{
			return $this->m_szReplacement;
		}
			
		//constructor
		public function __construct($bCharCode, $szReplacement)
		{
			$this->m_bCharCode = $bCharCode;
			$this->m_szReplacement = $szReplacement;
		}
	}

	class SharedFunctions
	{
		public static function getNamedTagInTagList($szName, $xtlTagList)
		{
			$lszHierarchicalNames = null;
	        $nCount = 0;
	        $boAbort = false;
	        $boFound = false;
	        $boLastNode = false;
	        $szString;
	        $szTagNameToFind;
	        $nCurrentIndex = 0;
	        $xtReturnTag = null;
	        $xtCurrentTag = null;
	        $nTagCount = 0;
	        $xtlCurrentTagList = null;
	        $nCount2 = 0;
	        
	        if (is_null($xtlTagList))
	        {
	        	return null;
	        }
	        
	        if (count($xtlTagList) == 0)
	        {
	        	return null;
	        }
	        
	        $lszHierarchicalNames = new StringList();
	        
	        $lszHierarchicalNames = SharedFunctions::getStringListFromCharSeparatedString($szName, '.');
	        
	        $xtlCurrentTagList = $xtlTagList;
	        
	        // loop over the hierarchical list
	        for ($nCount = 0; $nCount <$lszHierarchicalNames->getCount() && !$boAbort; $nCount++)
	        {
	        	if ($nCount == ($lszHierarchicalNames->getCount() - 1))
				{
	            	$boLastNode = true;
	           	}
	                
	          	$szString = (string)$lszHierarchicalNames[$nCount];
	          	
	          	// look to see if this tag name has the special "[]" array chars
	            $szTagNameToFind = SharedFunctions::getArrayNameAndIndex(szString, $nCurrentIndex);
	            $nCurrentIndex = $nIndex;

	           	$boFound = false;
	            $nCount2 = 0;
	            
	            for ($nTagCount = 0; $nTagCount < $xtlCurrentTagList->getCount() && !$boFound; $nTagCount++)
	            {
	            	$xtCurrentTag = $xtlCurrentTagList->getXmlTagForIndex($nTagCount);
	            	
	            	// if this is the last node then check the attributes of the tag first
	            	
	            	if ($xtCurrentTag->getName() == $szTagNameToFind)
	            	{
	            		if ($nCount2 == $nCurrentIndex)
	            		{
	            			$boFound = true;
	            		}
	            		else 
	            		{
	            			$nCount2++;
	            		}
	            	}
	            	
	            	if ($boFound)
	            	{
	            		if (!$boLastNode)
	            		{
	            			$xtlCurrentTagList = $xtCurrentTag->getChildTags();
	            		}
	            		else
	            		{
	            			// don't continue the search
	            			$xtReturnTag = $xtCurrentTag;
	            		}
	            	}
	            }
	            
	            if (!$boFound)
	            {
	            	$boAbort = true;
	            }
	        }
	        
	        return $xtReturnTag;
		}
		
		public static function getStringListFromCharSeparatedString($szString, $cDelimiter)
		{
			$nCount = 0;
	        $nLastCount = -1;
	        $szSubString;
	        $nStringLength;
	        $lszStringList;
	        
	        if ($szString == null ||
	        	$szString == "" ||
	         	(string)$cDelimiter == "")
	      	{
	        	return null;
	       	}
	            
	      	$lszStringList = new StringList();
	      	
	      	$nStringLength = strlen($szString);
	      	
	      	for ($nCount = 0; $nCount < $nStringLength; $nCount++)
	      	{
	      		if ($szString[$nCount] == $cDelimiter)
	      		{
	      			$szSubString = substr($szString, ($nLastCount + 1), ($nCount - $nLastCount - 1));
	      			$nLastCount = $nCount;
	      			$lszStringList->add($szSubString);
	      			
	      			if ($nCount == $nStringLength)
	      			{
	      				$lszStringList->add('');
	      			}
	      		}
	      		else 
	      		{
	      			if ($nCount == ($nStringLength - 1))
	      			{
	      				$szSubString = substr($szString, ($nLastCount + 1), ($nCount - $nLastCount));
	      				$lszStringList->add($szSubString);
	      			}
	      		}
	      	}
	      	
	      	return $lszStringList;
		}
		
		public static function getValue($szXMLVariable, $xtlTagList, & $szValue)
		{
			$boReturnValue = false;
	        $lszHierarchicalNames;
	        $szXMLTagName;
	        $szLastXMLTagName;
	        $nCount = 0;
	        $xtCurrentTag = null;
	        $xaXmlAttribute = null;
	        $lXmlTagAttributeList;
			
			if (xtlTagList == null)
	       	{
	        	$szValue = null;
	            return (false);
	       	}
	       	
	       	$lszHierarchicalNames = new StringList();
	        $szValue = null;
	        $lszHierarchicalNames = SharedFunctions::getStringListFromCharSeparatedString($szXMLVariable, '.');
	        
			if (count($lszHierarchicalNames) == 1)
	     	{
	       		$szXMLTagName = $lszHierarchicalNames->getAt(0);

	            $xtCurrentTag = SharedFunctions::GetNamedTagInTagList($szXMLTagName, $xtlTagList);

	            if ($xtCurrentTag != null)
	            {
	            	$lXmlTagAttributeList = $xtCurrentTag->getAttributes();
	              	$xaXmlAttribute = $lXmlTagAttributeList->getAt($szXMLTagName);

	                if ($xaXmlAttribute != null)
	                {
	                  	$szValue = $xaXmlAttribute->getValue();
	                    $boReturnValue = true;
	                }
	                else
	                {
	                    $szValue = $xtCurrentTag->getContent();
	                    $boReturnValue = true;
	                }
	            }
	    	}
	    	else 
	    	{
	    		if (count($lszHierarchicalNames) > 1)
	          	{
	            	$szXMLTagName = $lszHierarchicalNames->getAt(0);
	                $szLastXMLTagName = $lszHierarchicalNames->getAt(($lszHierarchicalNames->getCount() - 1));

	                // need to remove the last variable from the passed name
	                for ($nCount = 1; $nCount < ($lszHierarchicalNames->getCount() - 1); $nCount++)
	                {
	                	$szXMLTagName .= "." . $lszHierarchicalNames->getAt($nCount);
	               	}

	               	$xtCurrentTag = SharedFunctions::getNamedTagInTagList($szXMLTagName, $xtlTagList);

	                // first check the attributes of this tag
	                if ($xtCurrentTag != null)
	                {
	                	$lXmlTagAttributeList = $xtCurrentTag->getAttributes();
	                    $xaXmlAttribute = $lXmlTagAttributeList->getXmlAttributeForAttributeName($szLastXMLTagName);

	                    if ($xaXmlAttribute != null)
	                    {
	                    	$szValue = $xaXmlAttribute->getValue();
	                      	$boReturnValue = true;
	                  	}
	                    else
	                    {
	                    	// check to see if it's actually a tag
	                        $xtCurrentTag = SharedFunctions::getNamedTagInTagList($szLastXMLTagName, $xtCurrentTag->getChildTags());

	                        if ($xtCurrentTag != null)
	                        {
	                        	$szValue = SharedFunctions::replaceEntitiesInStringWithChars($xtCurrentTag->getContent());
	                            $boReturnValue = true;
	                      	}
	                   	}
	             	}
	           	}
	    	}
	        
			return $boReturnValue;
		}
		
		public static function getArrayNameAndIndex($szName, &$nIndex)
		{
			$szReturnString;
	        $nCount = 0;
	      	$szSubString;
	       	$boFound = false;
	        $boAbort = false;
	        $boAtLeastOneDigitFound = false;
			
	        if ($szName == '')
	      	{
	        	$nIndex = 0;
	            return $szName;
	       	}

	      	$szReturnString = $szName;
	        $nIndex = 0;
	        
	        if ($szName[(strlen($szName) - 1)] == ']')
	        {
	        	$nCount = strlen($szName) - 2;

	          	while (!$boFound &&
	                	!$boAbort &&
	                  	$nCount >= 0)
	        	{
	          		// if we've found the closing array brace
		            if ($szName[$nCount] == '[')
		            {
		            	$boFound = true;
		          	}
		            else
		            {
		            	if (!is_numeric($szName[$nCount]))
		                {
		                	$boAbort = true;
		                }
		              	else
		                {
		                	$boAtLeastOneDigitFound = true;
		                    $nCount--;
		                }
		            }
	          	}
	                  	
	        	// did we finish successfully?
	          	if ($boFound &&
	                $boAtLeastOneDigitFound)
	            {
	            	$szSubString = substr($szName, ($nCount + 1), (strlen($szName) - $nCount - 2));
	                $szReturnString = substr($szName, 0, $nCount);
	                $nIndex = (int)($szSubString);
	           	}
	        }
	        
	        return $szReturnString;
		}
		
		public static function stringToByteArray($str)
		{
			$encoded;
			
			$encoded = utf8_encode($str);
			
			return $encoded;
		}
		
		public static function byteArrayToString($aByte)
		{
			return utf8_decode($aByte);
		}
		
		public static function forwardPaddedNumberString($nNumber, $nPaddingAmount, $cPaddingChar)
		{
			$szReturnString;
	        $sbString;
	        $nCount = 0;

	        $szReturnString = (string)$nNumber;
	         
	        if (strlen($szReturnString) < $nPaddingAmount &&
	        		$nPaddingAmount > 0)
	      	{
	       		$sbString = '';

				for ($nCount = 0; $nCount < ($nPaddingAmount - strlen($szReturnString)); $nCount++)
		        {
		        	$sbString .= $cPaddingChar;   
		        }
		                
		      	$sbString .= $szReturnString;
		        $szReturnString = (string)$sbString;
	        }
	           		
	      	return $szReturnString;
		}
		
		public static function stripAllWhitespace($szString)
		{
			$sbReturnString;
	        $nCount = 0;

	        if ($szString == null)
	        {
	        	return (null);
	        }
	         
	        $sbReturnString = '';
	         
	        for ($nCount = 0; $nCount < strlen($szString); $nCount++)
	      	{
	        	if ($szString[$nCount] != ' ' &&
	            	$szString[$nCount] != '\t' &&
	                $szString[$nCount] != '\n' &&
	                $szString[$nCount] != '\r')
	          	{
	            	$sbReturnString .= $szString[$nCount];
	           	}
	       	}
	            
	      	return (string)$sbReturnString;
		}
		
		public static function getAmountCurrencyString($nAmount, $nExponent)
		{
			$szReturnString = "";
	      	$lfAmount;
	       	$nDivideAmount;

	       	$nDivideAmount = (int)(pow(10, $nExponent));
	        $lfAmount = (double)($nAmount/$nDivideAmount);
	        $szReturnString = (string)$lfAmount;

	       	return ($szReturnString);
		}
		
		public static function isStringNullOrEmpty($szString)
		{
			$boReturnValue = false;

			if ($szString == null ||
				$szString == '')
			{
				$boReturnValue = true;
			}
				
			return ($boReturnValue);
		}
		
		public static function replaceCharsInStringWithEntities($szString)
		{
			//give access to enum like associated array
			global $g_XMLEntities;
			
			$szReturnString;
	      	$nCount;
	      	$boFound;
	       	$nHTMLEntityCount;

	      	$szReturnString = null;
	      	
	      	for ($nCount = 0; $nCount < strlen($szString); $nCount++)
	      	{
	      		$boFound = false;
	           	$nHTMLEntityCount = 0;
				
	           	while (!$boFound && 
	                  	$nHTMLEntityCount < count($g_XMLEntities))
	            {
	            	//$test1 = htmlspecialchars('&');
	                  		
	                if ($g_XMLEntities[$nHTMLEntityCount]->getReplacement() == htmlspecialchars($szString[$nCount]))
	                {
	                	$boFound = true;
	                }
	                else 
	                {
	                	$nHTMLEntityCount++;
	                }
	          	}
	                  	
	        	if ($boFound)
	        	{
	        		$szReturnString .= $g_XMLEntities[$nHTMLEntityCount]->getReplacement();
	        	}
	        	else 
	        	{
	        		$szReturnString .= $szString[$nCount];
	        	}
	      	}
	        
	      	return $szReturnString;
		}
		
		public static function replaceEntitiesInStringWithChars($szString)
		{
			$szReturnString = null;
	        $nCount;
	        $boFound = false;
	        $boFoundAmpersand = false;
	        $nHTMLEntityCount;
	        $szAmpersandBuffer = "";
	        $nAmpersandBufferCount = 0;
	        
	        for ($nCount = 0; $nCount < strlen($szString); $nCount++)
	        {
	        	$boFound = false;
	            $nHTMLEntityCount = 0;

	          	if (!$boFoundAmpersand)
	           	{
	            	if ($szString[$nCount] == '&')
	                {
	                	$boFoundAmpersand = true;
	                    $szAmpersandBuffer = (string)$szString[$nCount];
	                   	$nAmpersandBufferCount = 0;
	                }
	                else
	                {
	                	$szReturnString .= $szString[$nCount];
	                }
	            }
	            else 
	            {
	            	$szAmpersandBuffer .= $szString[$nCount];

	               	if ($nAmpersandBufferCount < (10 - 2))
	                {
	                	if ($szString[$nCount] == ';')
	                    {
	                    	$boFound = true;
	                        $boFoundAmpersand = false;
	                    }
	                    else
	                    {
	                        $nAmpersandBufferCount++;
	                    }
	                }
	                else
	                {
	                    $szReturnString .= $szAmpersandBuffer;
	                    $boFoundAmpersand = false;
	                }
	            }
	            
	            if ($boFound)
	           	{
	           		// need to find the entity in the list
	            	$boFoundEntity = false;
	                $nXMLEntityCount = 0;
	                
	                while (!$boFoundEntity &&
	                      	$nXMLEntityCount < count($g_XMLEntities))
	              	{
	                	if (strtoupper($g_XMLEntities[$nXMLEntityCount]->getReplacement()) == strtoupper($szAmpersandBuffer))
	                    {
	                    	$boFoundEntity = true;
	                    }
	                    else
	                    {
	                         $nXMLEntityCount++;
	                    }
	                }
	                
	                if ($boFoundEntity)
	              	{
	                	$szReturnString .= $g_XMLEntities[$nXMLEntityCount]->getCharCode();
	                }
	                else
	                {
	                 	$szReturnString .= $szAmpersandBuffer;
	                }
	                $boFound = false;
	            }
	        }
	        
	        if ($boFoundAmpersand && !$boFound)
	       	{
	        	$szReturnString .= $szAmpersandBuffer;
	      	}

	        return $szReturnString;
		}
		
		public static function boolToString($boValue)
		{
			if ($boValue == true)
			{
				return 'true';
			}
			elseif ($boValue == false)
			{
				return 'false';
			}
		}
	}
?>