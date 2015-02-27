package {
	import object_helper;
	
	public class NumberFormat
	{
		public static var DEFAULT_NUM_DECIMALS:Number = 2;
		
		public var numDecimals:Number = DEFAULT_NUM_DECIMALS;
		public var isFixedNumDecimalsForced:Boolean = false;
		public var isDecimalSeparatorComma:Boolean = false;
		public var isThousandSeparatorDisabled:Boolean = false;
		
		public function NumberFormat( numDecimals:Number, isFixedNumDecimalsForced:Boolean, isDecimalSeparatorComma:Boolean, isThousandSeparatorDisabled:Boolean )
		{
			this.numDecimals = Parser.getNumberValue (numDecimals, DEFAULT_NUM_DECIMALS, true, false);
			this.isFixedNumDecimalsForced = Parser.getBooleanValue(isFixedNumDecimalsForced,false);
			this.isDecimalSeparatorComma = Parser.getBooleanValue(isDecimalSeparatorComma,false);
			this.isThousandSeparatorDisabled = Parser.getBooleanValue(isThousandSeparatorDisabled,false);
		}
		
		
		//singleton
	//	public static function getInstance (lv,c:Number):NumberFormat{
	//		if (c==2){
	//			return NumberFormat.getInstanceY2(lv);
	//		} else {
	//			return NumberFormat.getInstance(lv);
	//		}
	//	}

		private static var _instance:NumberFormat = null;
		
		public static function getInstance( json:Object ):NumberFormat {
			if (_instance == null) {
//				if (lv==undefined ||  lv == null){
//					lv=_root.lv;
//				}

				var o:Object = {
					num_decimals: 2,
					is_fixed_num_decimals_forced: 0,
					is_decimal_separator_comma: 0,
					is_thousand_separator_disabled: 0
				};
				
				object_helper.merge_2( json, o );
				
				_instance = new NumberFormat (
					o.num_decimals,
					o.is_fixed_num_decimals_forced==1,
					o.is_decimal_separator_comma==1,
					o.is_thousand_separator_disabled==1
				 );
	//			 trace ("getInstance NEW!!!!");
	//			 trace (_instance.numDecimals);
	//			 trace (_instance.isFixedNumDecimalsForced);
	//			 trace (_instance.isDecimalSeparatorComma);
	//			 trace (_instance.isThousandSeparatorDisabled);
			} else {
				 //trace ("getInstance found");
			}
			return _instance;
		}

		private static var _instanceY2:NumberFormat = null;
		
		public static function getInstanceY2( json:Object ):NumberFormat{
			if (_instanceY2 == null) {
//				if (lv==undefined ||  lv == null){
//					lv=_root.lv;
//				}
				
				var o:Object = {
					num_decimals: 2,
					is_fixed_num_decimals_forced: 0,
					is_decimal_separator_comma: 0,
					is_thousand_separator_disabled: 0
				};
				
				object_helper.merge_2( json, o );
				
				_instanceY2 = new NumberFormat (
					o.num_decimals,
					o.is_fixed_num_decimals_forced==1,
					o.is_decimal_separator_comma==1,
					o.is_thousand_separator_disabled==1
				 );
				
				 //trace ("getInstanceY2 NEW!!!!");
			} else {
				 //trace ("getInstanceY2 found");
			}
			return _instanceY2;
		}
	}
}