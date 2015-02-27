package {
	public class NumberUtils {


		public static function formatNumber (i:Number) : String{
			var format:NumberFormat = NumberFormat.getInstance(null);
			return NumberUtils.format (i, 
				format.numDecimals, 
				format.isFixedNumDecimalsForced, 
				format.isDecimalSeparatorComma,
				format.isThousandSeparatorDisabled 
			);
		} 
		
		public static function formatNumberY2 (i:Number) : String{
			var format:NumberFormat = NumberFormat.getInstanceY2(null);
			return NumberUtils.format (i, 
				format.numDecimals, 
				format.isFixedNumDecimalsForced, 
				format.isDecimalSeparatorComma,
				format.isThousandSeparatorDisabled 
			);
		}	

		public static function format( 
			i:Number, 
			numDecimals:Number,
			isFixedNumDecimalsForced:Boolean, 
			isDecimalSeparatorComma:Boolean,
			isThousandSeparatorDisabled:Boolean 
		) : String {
			if ( isNaN (numDecimals )) {
				numDecimals = 4;
			}
			
			// round the number down to the number of
			// decimals we want ( fixes the -1.11022302462516e-16 bug)
			i = Math.round(i*Math.pow(10,numDecimals))/Math.pow(10,numDecimals);
			
			var s:String = '';
			var num:Array;
			if( i<0 )
				num = String(-i).split('.');
			else
				num = String(i).split('.');
			
			//trace ("a: " + num[0] + ":" + num[1]);
			var x:String = num[0];
			var pos:Number=0;
			var c:Number=0;
			for(c=x.length-1;c>-1;c--)
			{
				if( pos%3==0 && s.length>0 )
				{
					s=','+s;
					pos=0;
				}
				pos++;
					
				s=x.substr(c,1)+s;
			}
			if( num[1] != undefined ) {
				if (isFixedNumDecimalsForced){
					num[1] += "0000000000000000";
				}
				s += '.'+ num[1].substr(0,numDecimals);
			} else {
				if (isFixedNumDecimalsForced && numDecimals>0){
					num[1] = "0000000000000000";
					s += '.'+ num[1].substr(0,numDecimals);			
				}
				
			}
				
			if( i<0 )
				s = '-'+s;
			
			if (isThousandSeparatorDisabled){
				s=s.replace (",","");
			}
			
			if (isDecimalSeparatorComma) {
				s = toDecimalSeperatorComma(s);
			}			
			return s;
		}
		
		public static function toDecimalSeperatorComma (value:String) : String{
			return value
				.replace(".","|")
				.replace(",",".")
				.replace("|",",")
		}

	}
}