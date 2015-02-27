package {
	public class Parser {

		//test if undefined or null
		public static function isEmptyValue ( value:Object ):Boolean {
//			if( value == undefined || value == null ) {
//				return true;
//			}	else {
				return false;
//			}
		} 
		
		
		
		
		// get valid String value from input value
		// if value is not defined, return default value
		// default value is valid String (cannot be undefined or null)
		// in case that isEmptyStringValid is false - take defaultvalue instead of value
		public static function getStringValue ( 
			value:Object, 
			defaultValue:String , 
			isEmptyStringValid:Boolean ):String{

			//defaultValue if not defined - set to empty String
			if( Parser.isEmptyValue (defaultValue)) {
				defaultValue = "";
			}
			
			//for undefined / null - return defaultValue
			if( Parser.isEmptyValue (value)) {
				return defaultValue;
			}
			
			if (!isEmptyStringValid && value.length == 0) {
				return defaultValue;
			}
			
			return String(value);
		}
		
		
		
		
		
		// get valid Number value from input value
		// if value is not defined, return default value
		// default value is valid String (cannot be undefined or null)
		// in case that isEmptyStringValid is false - take defaultvalue instead of value
		public static function getNumberValue ( 
			value:Object, 
			defaultValue:Number , 
			isZeroValueValid:Boolean ,
			isNegativeValueValid:Boolean 
			):Number{

			//defaultValue if not defined - set to zero
			if( Parser.isEmptyValue (defaultValue)
				|| isNaN(defaultValue)
				) {
				defaultValue = 0;
			}
			
			//for undefined / null - return defaultValue
			if( Parser.isEmptyValue (value) ) {
				return defaultValue;
			}
			
			var numValue:Number =  Number(value);
			if ( isNaN (numValue) ){
				return defaultValue;
			}
			
			if (!isZeroValueValid && numValue==0) {
				return defaultValue;
			}

			if (!isNegativeValueValid && numValue<0) {
				return defaultValue;
			}		
			
			return numValue;

		}
		
		
		
		public static function getBooleanValue ( 
			value:Object, 
			defaultValue:Boolean 
			):Boolean{
		
			if( Parser.isEmptyValue (value) ) {
				return defaultValue;
			}		
			
			var numValue:Number =  Number(value);
			if ( !isNaN (numValue) ){
				//for numeric value then 0 is false, everything else is true
				if (numValue==0)	{
					return false;
				} else {
					return true;
				}
			} 		

			//parse string falue 'true' -> true; else false
			var strValue:String = Parser.getStringValue (value,"false", false);
	//trace ("0------------------" + strValue);
			strValue = strValue.toLowerCase();
	//trace ("1------------------" + strValue);		
			if (strValue.indexOf('true') !=-1){
				return true;
			} else {
				return false;
			}
			
		}
		
		

		public static function runTests():void{
			var notDefinedNum:Number;
			trace ("testing Parser.getStringValue...");
			trace("1) stringOK  '" + Parser.getStringValue("stringOK","myDefault",true) + "'");
			trace("2) ''        '" + Parser.getStringValue("","myDefault",true) + "'");
			trace("3) myDefault '" + Parser.getStringValue("","myDefault",false) + "'");
//			trace("4) ''        '" + Parser.getStringValue(notDefinedNum) + "'");
//			trace("5) 999       '" + Parser.getStringValue(999) + "'");


			trace ("testing Parser.getNumberValue...");
			trace("01) 999       '" + Parser.getNumberValue(999,22222222,true,true) + "'");
			trace("02) 999       '" + Parser.getNumberValue("999",22222222,true,true) + "'");
//			trace("03) 999       '" + Parser.getNumberValue("999") + "'");
//			trace("04) 0         '" + Parser.getNumberValue("abc") + "'");
//			trace("05) -1        '" + Parser.getNumberValue("abc",-1) + "'");
			trace("06) -1        '" + Parser.getNumberValue("abc",-1, false, false) + "'");
			trace("07) -1        '" + Parser.getNumberValue(null,-1, false, false) + "'");
//			trace("08) 22222222  '" + Parser.getNumberValue(0,22222222) + "'");
//			trace("09) 0         '" + Parser.getNumberValue(0,22222222,true) + "'");
//			trace("10) 22222222  '" + Parser.getNumberValue(0,22222222,false) + "'");
			trace("11) 22222222  '" + Parser.getNumberValue(0,22222222,false,false) + "'");
			trace("12) 22222222  '" + Parser.getNumberValue(-0.1,22222222,false,false) + "'");
			trace("13) -0.1      '" + Parser.getNumberValue(-0.1,22222222,false,true) + "'");
			trace("13) 22222222  '" + Parser.getNumberValue("-0.1x",22222222,false,true) + "'");
			
			trace ("testing Parser.getBooleanValue...");
			trace("true       '" + Parser.getBooleanValue("1",false) + "'");
			trace("true       '" + Parser.getBooleanValue("-1",false) + "'");
			trace("false      '" + Parser.getBooleanValue("0.000",false) + "'");
			trace("false      '" + Parser.getBooleanValue("",false) + "'");
			trace("true       '" + Parser.getBooleanValue("",true) + "'");
			trace("false      '" + Parser.getBooleanValue("false",false) + "'");
			trace("false      '" + Parser.getBooleanValue("xxx",false) + "'");
			trace("true      '" + Parser.getBooleanValue("true",true) + "'");
			trace("true      '" + Parser.getBooleanValue("TRUE",true) + "'");
			trace("true      '" + Parser.getBooleanValue(" TRUE ",true) + "'");
		}
		
	}
}