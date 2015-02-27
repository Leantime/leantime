package elements.labels {
	import org.flashdevelop.utils.FlashConnect;
	import string.Css;
	
	public class XLegend extends BaseLabel {

		public function XLegend( json:Object )
		{
			super();
			
			if( !json )
				return;
			
			object_helper.merge_2( json, this );
			
			this.css = new Css( this.style );
			
			// call our parent constructor:
			this.build( this.text );
		}
		
		
		public function resize( sc:ScreenCoords ):void {
			if ( this.text == null )
				return;
				
			// this will center it in the X
			// this will align bottom:
			this.x = sc.left + ( (sc.width/2) - (this.get_width()/2) );
			//this.getChildAt(0).width = this.stage.stageWidth;
			this.getChildAt(0).y = this.stage.stageHeight - this.getChildAt(0).height;
		}
		
		//
		// this is only here while title has CSS and x legend does not.
		// remove this when we put css in this object
		//
		public function get_height():Number{
			// the title may be turned off:
			return this.height;
		}
	
	}
}