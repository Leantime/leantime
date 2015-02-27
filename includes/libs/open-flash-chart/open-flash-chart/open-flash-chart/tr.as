package {
	import org.flashdevelop.utils.FlashConnect;
	import com.serialization.json.JSON;
	
	public class tr {
		
		public static function ace( o:Object ):void	{
			if ( o == null )
				FlashConnect.trace( 'null' );
			else
				FlashConnect.trace( o.toString() );
		
			// var tempError:Error = new Error();
			// var stackTrace:String = tempError.getStackTrace();
			// FlashConnect.trace( 'stackTrace:' + stackTrace );
		
			if ( false )
				tr.trace_full();
		}
		
		//
		// e.g: tr.aces( 'my val', val );
		//
		public static function aces( ... optionalArgs ):void	{
			
			var tmp:Array = [];
			for each( var o:Object in optionalArgs )
			{
				// FlashConnect.trace( o.toString() );
				if ( o == null )
					tmp.push( 'null' );
				else
					tmp.push( o.toString() );
			}
			
			FlashConnect.trace( tmp.join(', ') );
		}
		
		// this doesn't work cos I don't know how to set 'permit debugging' yet
		/**
		 * Found this at:
		 *   http://www.ultrashock.com/forums/actionscript/can-you-trace-a-line-95261.html
		 */
		static public function ace_full(snum:uint=3):void
		{
			// FROM:
			// http://snippets.dzone.com/posts/show/3703
			//----------------------------------------------------------------------------------------------------------------
			// With debugging turned on, this is what we get:
			//
			// Error
			// <tab>at com.flickaway::Trace$/log_full()[D:\web\flickaway_branch\flash\lib\com\flickaway\Trace.as:83]
			// <tab>at com.flickaway::Trace$/print_r_full()[D:\web\flickaway_branch\flash\lib\com\flickaway\Trace.as:114]
			// <tab>at com.flickaway::Trace$/print_r()[D:\web\flickaway_branch\flash\lib\com\flickaway\Trace.as:46]
			// <tab>at com.flickaway::Params()[D:\web\flickaway_branch\flash\lib\com\flickaway\Params.as:36]                <==== this line we want
			// <tab>at com.flickaway::Params$/get_instance()[D:\web\flickaway_branch\flash\lib\com\flickaway\Params.as:27]
			// <tab>at HomeDefault()[D:\web\flickaway_branch\flash\homepage\HomeDefault.as:57]
			// <tab>at com.flickaway::Params()[D:\web\flickaway_branch\flash\lib\com\flickaway\Params.as:36])
			//
			// with debugging turned off:
			//
			// Error
			// <tab>at com.flickaway::Trace$/log_full()
			// <tab>at com.flickaway::Trace$/print_r_full()
			// <tab>at com.flickaway::Trace$/print_r()
			// <tab>at com.flickaway::Params()
			// <tab>at com.flickaway::Params$/get_instance()
			// <tab>at HomeDefault()
			//----------------------------------------------------------------------------------------------------------------
			var e:Error = new Error();
			var str:String = e.getStackTrace();                     // get the full text str

			if (str == null)                                          // means we aren't on the Debug player
			{
				FlashConnect.trace( "(!debug) " );
			}
			else
			{
				var stacks:Array = str.split("\n");                     // split into each line
				var caller:String = tr.gimme_caller(stacks[snum]);   // get the caller for just one specific line in the stack trace
				FlashConnect.trace( caller );
			}
		}

		/**
		* Returns a string like "[HomeDefault():51]" - line number present only if "permit debugging" is turned on.
		*/
		static private function gimme_caller(line:String):String
		{
			//-------------------------------------------------------------------------------------------------
			// the line can look like any of these (so we must be able to clean up all of them):
			//
			// <tab>at com.flickaway::Params()
			// <tab>at com.flickaway::Params()[D:\web\flickaway_branch\flash\lib\com\flickaway\Params.as:36]
			// <tab>at HomeDefault()
			// <tab>at HomeDefault()[D:\web\flickaway_branch\flash\homepage\HomeDefault.as:57]
			//-------------------------------------------------------------------------------------------------
			var dom_pos:int = line.indexOf("::");                  // find the '::' part
			var caller:String;

			if (dom_pos == -1)
			{
				caller = line.substr(4);                         // just remove '<tab>at ' beginning part (4 characters)
			}
			else
			{
				caller = line.substr(dom_pos+2);                 // remove '<tab>at com.flickaway::' beginning part
			}
			var lb_pos:int = caller.indexOf("[");                // get position of the left bracket (lb)

			if (lb_pos == -1)                                    // if the lb doesn't exist (then we don't have "permit debugging" turned on)
			{
				return "[" + caller + "]";
			}
			else
			{
				var line_num:String = caller.substr(caller.lastIndexOf(":"));      // find the line number
				caller = caller.substr(0, lb_pos);                                 // cut it out - it'll look like ":51]"
				return "[" + caller + line_num;                                    // line_num already has the trailing right bracket
			}
		}



		
		public static function ace_json( json:Object ):void {
			tr.ace(JSON.serialize(json));
		}
	}
}