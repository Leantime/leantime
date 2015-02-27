package elements.axis {
	import flash.text.TextField;
	import flash.display.Sprite;
	import flash.text.TextFormat;
	import string.Utils;
	import flash.geom.Point;
	
	public class RadarSpokeLabels extends Sprite{

		private var style:Object;
		public var labels:Array;
		
		
		public function RadarSpokeLabels( json:Object ) {
			
			// default values
			this.style = {
				colour:			'#784016'
			};
			
			if( json != null )
				object_helper.merge_2( json, this.style );
				
			// tr.ace_json(this.style);
				
			this.style.colour = Utils.get_colour( this.style.colour );
			
			// cache the text for tooltips
			this.labels = new Array();
			var values:Array;
			var ok:Boolean = false;
			
			if( ( this.style.labels is Array ) && ( this.style.labels.length > 0 ) )
			{
				
				for each( var s:Object in this.style.labels )
					this.add( s, this.style );
			}
			
		}

		public function add( label:Object, style:Object ) : void
		{
			var label_style:Object = {
				colour:		style.colour,
				text:		'',
				size:       11
			};

			if( label is String )
				label_style.text = label as String;
			else {
				object_helper.merge_2( label, label_style );
			}

			// our parent colour is a number, but
			// we may have our own colour:
			if( label_style.colour is String )
				label_style.colour = Utils.get_colour( label_style.colour );

			this.labels.push( label_style.text );

			var l:TextField = this.make_label( label_style );
			this.addChild( l );
		}
		
		public function make_label( label_style:Object ):TextField {
			
			// we create the text in its own movie clip
			
			var tf:TextField = new TextField();
            tf.x = 0;
			tf.y = 0;
			
			var tmp:Array = label_style.text.split( '<br>' );
			var text:String = tmp.join('\n');
			
			tf.text = text;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = label_style.colour;
			fmt.font = "Verdana";
			fmt.size = label_style.size;
			fmt.align = "right";
			
			tf.setTextFormat(fmt);
			tf.autoSize = "left";
			tf.visible = true;
			
			return tf;
		}
		
		// move y axis labels to the correct x pos
		public function resize( sc:ScreenCoordsRadar ):void {

			var tf:TextField;
			//
			// loop over the lables and make sure they are on the screen,
			// reduce the radius until they fit
			//
			var i:Number = 0;
			var outside:Boolean;
			do
			{
				outside = false;
				this.resize_2( sc );
				
				for ( i = 0; i < this.numChildren; i++ )
				{
					tf = this.getChildAt(i) as TextField;
					if( (tf.x < sc.left) ||
						(tf.y < sc.top) ||
						(tf.y + tf.height > sc.bottom ) ||
						(tf.x + tf.width > sc.right)
					)
						outside = true;
				
				}
				sc.reduce_radius();
			}
			while ( outside && sc.get_radius() > 10 );
			//
			//
			//
		}
		
		private function resize_2( sc:ScreenCoordsRadar ):void {
			
			var i:Number;
			var tf:TextField;
			var mid_x:Number = sc.get_center_x();
			
			// now move it to the correct Y, vertical center align
			for ( i = 0; i < this.numChildren; i++ ) {
				
				tf = this.getChildAt(i) as TextField;

				var p:flash.geom.Point = sc.get_get_x_from_pos_and_y_from_val( i, sc.get_max() );
				if ( p.x > mid_x )
					tf.x = p.x;					// <-- right align the text
				else
					tf.x = p.x - tf.width;		// <-- left align the text
				
				if ( i == 0 ) {
					//
					// this is the top label and will overwrite
					// the radius label -- so we right align it
					// because the radius labels are left aligned
					//
					tf.y = p.y - tf.height;
					tf.x = p.x;
				}
				else
					tf.y = p.y;
			}
		}
		
		public function die(): void {
			
			this.style = null;
			this.labels = null;
			
			this.graphics.clear();
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}
	}
}