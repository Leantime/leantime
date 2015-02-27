package string
{
	public class DateUtils
	{

		protected static var dateConsts:Object = {
			shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
			longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
		}

		public static function replace_magic_values( tip:String, xVal:Number):String {
			// convert from a unix timestamp to an AS3 date
			var as3Date:Date = new Date(xVal * 1000);
			tip = tip.replace('#date#', DateUtils.formatDate(as3Date, "Y-m-d"));
			// check for user formatted dates
			var begPtr:int = tip.indexOf("#date:");
			while (begPtr >= 0)
			{
				var endPtr:int = tip.indexOf("#", begPtr + 1) + 1;
				var replaceStr:String = tip.substr(begPtr, endPtr-begPtr);
				var timeFmt:String = replaceStr.substr(6, replaceStr.length - 7);
				var dateStr:String = DateUtils.formatDate(as3Date, timeFmt);
				tip = tip.replace(replaceStr, dateStr);
				begPtr = tip.indexOf("#date:");
			}

			begPtr = tip.indexOf("#gmdate:");
			while (begPtr >= 0)
			{
				endPtr = tip.indexOf("#", begPtr + 1) + 1;
				replaceStr = tip.substr(begPtr, endPtr-begPtr);
				timeFmt = replaceStr.substr(8, replaceStr.length - 9);
				dateStr= DateUtils.formatUTCDate(as3Date, timeFmt);
				tip = tip.replace(replaceStr, dateStr);
				begPtr = tip.indexOf("#gmdate:");
			}

			return tip;
		}
		
		// Simulates PHP's date function
		public static function formatDate( aDate:Date, fmt:String ): String
		{
			var returnStr:String = '';
			for (var i:int = 0; i < fmt.length; i++) {
				var curChar:String = fmt.charAt(i);
				switch (curChar)
				{
					// day
					case 'd':
							returnStr += (aDate.getDate() < 10 ? '0' : '') + aDate.getDate(); 
							break;
					case 'D':
							returnStr += DateUtils.dateConsts.shortDays[aDate.getDate()];
							break;
					case 'j':
							returnStr += aDate.getDate();
							break;
					case 'l': 
							returnStr += DateUtils.dateConsts.longDays[aDate.getDay()];
							break;
					case 'N':
							returnStr += aDate.getDay() + 1;
							break;
					case 'S':
							returnStr += (aDate.getDate() % 10 == 1 && aDate.getDate() != 11 ? 'st' : (aDate.getDate() % 10 == 2 && aDate.getDate() != 12 ? 'nd' : (aDate.getDate() % 10 == 3 && aDate.getDate() != 13 ? 'rd' : 'th')));
							break;
					case 'w':
							returnStr += aDate.getDay();
							break;
					//z: function() { return "Not Yet Supported"; },
					
					// Week
					//W: function() { return "Not Yet Supported"; },
					
					// Month
					case 'F':
						returnStr += DateUtils.dateConsts.longMonths[aDate.getMonth()];
						break;
					case 'm':
						returnStr += (aDate.getMonth() < 9 ? '0' : '') + (aDate.getMonth() + 1);
						break;
					case 'M':
						returnStr += DateUtils.dateConsts.shortMonths[aDate.getMonth()];
						break;
					case 'n':
						returnStr += aDate.getMonth() + 1;
						break;
					//t: function() { return "Not Yet Supported"; },
					
					// Year
					//L: function() { return "Not Yet Supported"; },
					//o: function() { return "Not Supported"; },
					case 'Y':
						returnStr += aDate.getFullYear();
						break;
					case 'y':
						returnStr += ('' + aDate.getFullYear()).substr(2);
						break;
						
					// Time
					case 'a':
						returnStr += aDate.getHours() < 12 ? 'am' : 'pm';
						break;
					case 'A':
						returnStr += aDate.getHours() < 12 ? 'AM' : 'PM';
						break;
					//B: function() { return "Not Yet Supported"; },
					case 'g':
						returnStr += aDate.getHours() == 0 ? 12 : (aDate.getHours() > 12 ? aDate.getHours() - 12 : aDate.getHours());
						break;
					case 'G':
						returnStr += aDate.getHours();
						break;
					case 'h':
						returnStr += (aDate.getHours() < 10 || (12 < aDate.getHours() < 22) ? '0' : '') + (aDate.getHours() < 10 ? aDate.getHours() + 1 : aDate.getHours() - 12);
						break;
					case 'H':
						returnStr += (aDate.getHours() < 10 ? '0' : '') + aDate.getHours();
						break;
					case 'i':
						returnStr += (aDate.getMinutes() < 10 ? '0' : '') + aDate.getMinutes();
						break;
					case 's':
						returnStr += (aDate.getSeconds() < 10 ? '0' : '') + aDate.getSeconds();
						break;
					
					// Timezone
					//e: function() { return "Not Yet Supported"; },
					//I: function() { return "Not Supported"; },
					case 'O':
						returnStr += (aDate.getTimezoneOffset() < 0 ? '-' : '+') + (aDate.getTimezoneOffset() / 60 < 10 ? '0' : '') + (aDate.getTimezoneOffset() / 60) + '00';
						break;
					//T: function() { return "Not Yet Supported"; },
					case 'Z':
						returnStr += aDate.getTimezoneOffset() * 60;
						break;
						
					// Full Date/Time
					//c: function() { return "Not Yet Supported"; },
					case 'r':
						returnStr += aDate.toString();
						break;
					case 'U':
						returnStr += aDate.getTime() / 1000;
						break;
							
					default:
						returnStr += curChar;
				}
			}
			return returnStr;
		};
		
		// Simulates PHP's date function
		public static function formatUTCDate( aDate:Date, fmt:String ): String
		{
			var returnStr:String = '';
			for (var i:int = 0; i < fmt.length; i++) {
				var curChar:String = fmt.charAt(i);
				switch (curChar)
				{
					// day
					case 'd':
							returnStr += (aDate.getUTCDate() < 10 ? '0' : '') + aDate.getUTCDate(); 
							break;
					case 'D':
							returnStr += DateUtils.dateConsts.shortDays[aDate.getUTCDate()];
							break;
					case 'j':
							returnStr += aDate.getUTCDate();
							break;
					case 'l': 
							returnStr += DateUtils.dateConsts.longDays[aDate.getUTCDay()];
							break;
					case 'N':
							returnStr += aDate.getUTCDay() + 1;
							break;
					case 'S':
							returnStr += (aDate.getUTCDate() % 10 == 1 && aDate.getUTCDate() != 11 ? 'st' : (aDate.getUTCDate() % 10 == 2 && aDate.getUTCDate() != 12 ? 'nd' : (aDate.getUTCDate() % 10 == 3 && aDate.getUTCDate() != 13 ? 'rd' : 'th')));
							break;
					case 'w':
							returnStr += aDate.getUTCDay();
							break;
					//z: function() { return "Not Yet Supported"; },
					
					// Week
					//W: function() { return "Not Yet Supported"; },
					
					// Month
					case 'F':
						returnStr += DateUtils.dateConsts.longMonths[aDate.getUTCMonth()];
						break;
					case 'm':
						returnStr += (aDate.getUTCMonth() < 9 ? '0' : '') + (aDate.getUTCMonth() + 1);
						break;
					case 'M':
						returnStr += DateUtils.dateConsts.shortMonths[aDate.getUTCMonth()];
						break;
					case 'n':
						returnStr += aDate.getUTCMonth() + 1;
						break;
					//t: function() { return "Not Yet Supported"; },
					
					// Year
					//L: function() { return "Not Yet Supported"; },
					//o: function() { return "Not Supported"; },
					case 'Y':
						returnStr += aDate.getUTCFullYear();
						break;
					case 'y':
						returnStr += ('' + aDate.getUTCFullYear()).substr(2);
						break;
						
					// Time
					case 'a':
						returnStr += aDate.getUTCHours() < 12 ? 'am' : 'pm';
						break;
					case 'A':
						returnStr += aDate.getUTCHours() < 12 ? 'AM' : 'PM';
						break;
					//B: function() { return "Not Yet Supported"; },
					case 'g':
						returnStr += aDate.getUTCHours() == 0 ? 12 : (aDate.getUTCHours() > 12 ? aDate.getUTCHours() - 12 : aDate.getHours());
						break;
					case 'G':
						returnStr += aDate.getUTCHours();
						break;
					case 'h':
						returnStr += (aDate.getUTCHours() < 10 || (12 < aDate.getUTCHours() < 22) ? '0' : '') + (aDate.getUTCHours() < 10 ? aDate.getUTCHours() + 1 : aDate.getUTCHours() - 12);
						break;
					case 'H':
						returnStr += (aDate.getUTCHours() < 10 ? '0' : '') + aDate.getUTCHours();
						break;
					case 'i':
						returnStr += (aDate.getUTCMinutes() < 10 ? '0' : '') + aDate.getUTCMinutes();
						break;
					case 's':
						returnStr += (aDate.getUTCSeconds() < 10 ? '0' : '') + aDate.getUTCSeconds();
						break;
					
					// Timezone
					//e: function() { return "Not Yet Supported"; },
					//I: function() { return "Not Supported"; },
					case 'O':
						returnStr += '+0000';
						break;
					//T: function() { return "Not Yet Supported"; },
					case 'Z':
						returnStr += 0;
						break;
						
					// Full Date/Time
					//c: function() { return "Not Yet Supported"; },
					case 'r':
						returnStr += aDate.toUTCString();
						break;
					case 'U':
						returnStr += aDate.getTime() / 1000;
						break;
							
					default:
						returnStr += curChar;
				}
			}
			return returnStr;
		};

		
	}
}