package {
	
	public class object_helper {
		
		//
		// merge two objects, one from the user
		// and is JSON, the other is the default
		// values this object should have
		//
		public static function merge( o:Object, defaults:Object ):Object {
			
			for (var prop:String in defaults ) {
				if( o[prop] == undefined )
					o[prop] = defaults[prop];
			}
			return o;
		}
		
		public static function merge_2( json:Object, defaults:Object ):void {
			
			for (var prop:String in json ) {
				
				// tr.ace( prop +' = ' + json[prop]);
				defaults[prop] = json[prop];
			}
		}
	}
}