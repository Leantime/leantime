package {
	
	public class JsonErrorMsg extends ErrorMsg {
		
		public function JsonErrorMsg( json:String, e:Error ):void {
			
			var tmp:String = "Open Flash Chart\n\n";
			tmp += "JSON Parse Error ["+ e.message +"]\n";
			
			// find the end of line after the error location:
			var pos:Number = json.indexOf( "\n", e.errorID );
			var s:String = json.substr(0, pos);
			var lines:Array = s.split("\n");
			
			tmp += "Error at character " + e.errorID + ", line " + lines.length +":\n\n";
			
			for ( var i:Number = 3; i > 0; i-- ) {
				if( lines.length-i > -1 )
					tmp += (lines.length - i).toString() +": " + lines[lines.length - i];
					
			}
			
			super( tmp );
		}
	}
}