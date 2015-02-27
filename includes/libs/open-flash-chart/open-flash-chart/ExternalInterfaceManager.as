package {
	import flash.external.ExternalInterface;
	
	/**
	 * This manages all External calls, not all players have this ability (Flex does not,
	 * flash in a browser does, flash standalone does not)
	 * 
	 * We also have an optional chart_id that the user may set, this is passed out
	 * as parameter one if it is set.
	 */
	public class ExternalInterfaceManager
	{
		public var has_id:Boolean;
		public var chart_id:String;
		
		private static var _instance:ExternalInterfaceManager;
		
		public static function getInstance():ExternalInterfaceManager {
			
			if (_instance == null) {
				_instance = new ExternalInterfaceManager();
			}
			
			return _instance;
		}
		
		public function setUp(chart_id:String):void {
			this.has_id = true;
			this.chart_id = chart_id;
	tr.aces('this.chart_id',this.chart_id);
		}
		
		// THIS NEEDS FIXING. I can't figure out how to preprend the chart
		// id to the optional parameters.
		public function callJavascript(functionName:String, ... optionalArgs ): * {
			
			// the debug player does not have an external interface
			// because it is NOT embedded in a browser
			if (ExternalInterface.available) {
				if ( this.has_id ) {
					tr.aces(functionName, optionalArgs);
					optionalArgs.unshift(this.chart_id);
					tr.aces(functionName, optionalArgs);
				}
				
				return ExternalInterface.call(functionName, optionalArgs);
			}
			
		}
	}
}