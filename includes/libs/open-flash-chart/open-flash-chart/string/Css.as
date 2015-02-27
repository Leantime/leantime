package string {

	public class Css {
		public var text_align:String;
		public var font_size:Number;
		private var text_decoration:String;
		private var margin:String;
		public var margin_top:Number;
		public var margin_bottom:Number;
		public var margin_left:Number;
		public var margin_right:Number;
		
		private var padding:String;
		public var padding_top:Number=0;
		public var padding_bottom:Number=0;
		public var padding_left:Number=0;
		public var padding_right:Number=0;
		
		public var font_weight:String;
		public var font_style:String;
		public var font_family:String;
		public var color:Number;
		private var stop_process:Number;  // Flag for disable checking
		public var background_colour:Number;
		public var background_colour_set:Boolean;
		
		private var display:String;
		
		public function Css( txt:String ) {
			// To lower case
			txt.toLowerCase();
			
			// monk.e.boy: remove the { and }
			txt = txt.replace( '{', '' );
			txt = txt.replace( '}', '' );
			
			// monk.e.boy: setup some default values.
			// does this confilct with 'clear()'?
			this.margin_top		= 0;
			this.margin_bottom	= 0;
			this.margin_left	= 0;
			this.margin_right	= 0;
			
			this.padding_top	= 0;
			this.padding_bottom	= 0;
			this.padding_left	= 0;
			this.padding_right	= 0;
		
			this.color = 0;
			this.background_colour_set = false;
			this.font_size = 9;
			
			// Splitting by the ;
			var arr:Array = txt.split(";");
			
			// Checking all the types of css params we accept and writing to internal variables of the object class
			for( var i:Number = 0; i < arr.length; i++)
			{
				getAttribute(arr[i]);
			}
		}
		
		private function trim( txt:String ):String {
			var l:Number = 0;
			var r:Number = txt.length - 1;
			while(txt.charAt(l) == ' ' || txt.charAt(l) == "\t" ) l++;
			while(txt.charAt(r) == ' ' || txt.charAt(r) == "\t" ) r--;
			return txt.substring( l, r+1 );
		}
		
		private function removeDoubleSpaces( txt:String ):String {
			var aux:String;
			var auxPrev:String;
			aux = txt;
			do {
				auxPrev = aux;
				aux.replace('  ',' '); 
			} while (  auxPrev.length != aux.length  );
			return aux;
		}
		
		private function ToNumber(cad:String):Number {
			
			cad = cad.replace( 'px', '' );
			
			if ( isNaN( Number(cad) )  ) {
				return 0;
			} else {
				return Number(cad);
			}
		}
		
		private function getAttribute( txt:String ):void {
			var arr:Array = txt.split(":");
			if( arr.length==2 )
			{
				this.stop_process = 1;
				this.set( arr[0], trim(arr[1]) );
			}
		}
		/*
		public function get( cad:String ):Number {
			switch (cad) {
				case "text-align"			: return this.text_align;
				case "font-size"			: return ToNumber(this.font_size);
				case "text-decoration"		: return this.text_decoration;
				case "margin-top"			: return this.margin_top;
				case "margin-bottom"		: return this.margin_bottom;
				case "margin-left"			: return this.margin_left;
				case "margin-right"			: return this.margin_right;
				case "padding-top"			: return this.padding_top;
				case "padding-bottom"		: return this.padding_bottom;
				case "padding-left"			: return this.padding_left;
				case "padding-right"		: return this.padding_right;
				case "font-weight"			: return ToNumber(this.font_weight);
				case "font-style"			: return this.font_style;
				case "font-family"			: return this.font_family;
				case "color"				: return this.color;
				case "background-color"		: return this.bg_colour;
				case "display"				: return this.display;
				default						: return 0;
			}
		}
		*/
		// FUCKING!! Flash without By reference String parameters on functions
		public function set( cad:String, val:String ):void {
			cad = trim( cad );
		
			switch( cad )
			{
				case "text-align"			: this.text_align = val;			break;
				case "font-size"			: this.set_font_size(val);			break;
				case "text-decoration"		: this.text_decoration = val;		break;
				
				case "margin"				: this.setMargin(val);			break;
				case "margin-top"			: this.margin_top = ToNumber(val); break;
				case "margin-bottom"		: this.margin_bottom = ToNumber(val); break;
				case "margin-left"			: this.margin_left = ToNumber(val); break;
				case "margin-right"			: this.margin_right = ToNumber(val); break;
				
				case 'padding'				: this.setPadding(val);				break;
				case "padding-top"			: this.padding_top = ToNumber(val); break;
				case "padding-bottom"		: this.padding_bottom = ToNumber(val); break;
				case "padding-left"			: this.padding_left = ToNumber(val); break;
				case "padding-right"		: this.padding_right = ToNumber(val); break;
				
				case "font-weight"			: this.font_weight = val; break;
				case "font-style"			: this.font_style = val; break;
				case "font-family"			: this.font_family = val; break;
				case "color"				: this.set_color(val);				break;
				case "background-color":
					this.background_colour = Utils.get_colour(val);
					this.background_colour_set = true;
					break;
				case "display"				: this.display = val; break;
			}
		}
		
		public function set_color( val:String ):void {
			this.color = Utils.get_colour( val );
		}
		
		public function set_font_size( val:String ):void {
			this.font_size = ToNumber(val);
		}
		
		
		private function setPadding( val:String ):void {

			val = trim( val );
			var arr:Array = val.split(' ');
			
			switch( arr.length )
			{
				
				// margin: 30px;
				case 1:
					this.padding_top	= ToNumber(arr[0]);
					this.padding_right	= ToNumber(arr[0]);
					this.padding_bottom	= ToNumber(arr[0]);
					this.padding_left	= ToNumber(arr[0]);
					break;
					
				// margin: 15px 5px;
				case 2:
					this.padding_top	= ToNumber(arr[0]);
					this.padding_right	= ToNumber(arr[1]);
					this.padding_bottom	= ToNumber(arr[0]);
					this.padding_left	= ToNumber(arr[1]);
					break;
					
				// margin: 15px 5px 10px;
				case 3:
					this.padding_top	= ToNumber(arr[0]);
					this.padding_right	= ToNumber(arr[1]);
					this.padding_bottom	= ToNumber(arr[2]);
					this.padding_left	= ToNumber(arr[1]);
					break;
					
				// margin: 1px 2px 3px 4px;
				default:
					this.padding_top	= ToNumber(arr[0]);
					this.padding_right	= ToNumber(arr[1]);
					this.padding_bottom	= ToNumber(arr[2]);
					this.padding_left	= ToNumber(arr[3]);
			}
		}
		
		private function setMargin( val:String ):void {

			val = trim( val );
			var arr:Array = val.split(' ');
			
			switch( arr.length )
			{
				
				// margin: 30px;
				case 1:
					this.margin_top		= ToNumber(arr[0]);
					this.margin_right	= ToNumber(arr[0]);
					this.margin_bottom	= ToNumber(arr[0]);
					this.margin_left	= ToNumber(arr[0]);
					break;
				
				// margin: 15px 5px;
				case 2:
					this.margin_top		= ToNumber(arr[0]);
					this.margin_right	= ToNumber(arr[1]);
					this.margin_bottom	= ToNumber(arr[0]);
					this.margin_left	= ToNumber(arr[1]);
					break;
					
				// margin: 15px 5px 10px;
				case 3:
					this.margin_top		= ToNumber(arr[0]);
					this.margin_right	= ToNumber(arr[1]);
					this.margin_bottom	= ToNumber(arr[2]);
					this.margin_left	= ToNumber(arr[1]);
					break;
					
				// margin: 1px 2px 3px 4px;
				default:
					this.margin_top		= ToNumber(arr[0]);
					this.margin_right	= ToNumber(arr[1]);
					this.margin_bottom	= ToNumber(arr[2]);
					this.margin_left	= ToNumber(arr[3]);
			}
		}
		
		public function clear():void {
			this.text_align			= undefined;
			this.font_size			= undefined;
			this.text_decoration	= undefined;
			this.margin_top			= undefined;
			this.margin_bottom		= undefined;
			this.margin_left		= undefined;
			this.margin_right		= undefined;
			this.font_weight		= undefined;
			this.font_style			= undefined;
			this.font_family		= undefined;
			this.color				= undefined;
			this.display			= undefined;
		}
	}
}