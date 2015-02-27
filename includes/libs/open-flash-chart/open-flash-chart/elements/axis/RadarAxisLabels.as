package elements.axis {
	import flash.text.TextField;
	import flash.display.Sprite;
	import flash.text.TextFormat;
	import string.Utils;
	
	public class RadarAxisLabels extends Sprite{

		private var style:Object;
		public var labels:Array;
		
		
		public function RadarAxisLabels( json:Object ) {
			
			// default values
			this.style = {
				colour:			'#784016',
				steps:			1
			};
			
			if( json != null )
				object_helper.merge_2( json, this.style );
				
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
				size:		style.size,
				visible:	true
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

			//
			// inheriting the 'visible' attribute
			// is complext due to the 'steps' value
			// only some labels will be visible
			//
			if( label_style.visible == null )
			{
				//
				// some labels will be invisible due to our parents step value
				//
				if ( ( (this.labels.length - 1) % style.steps ) == 0 )
					label_style.visible = true;
				else
					label_style.visible = false;
			}

			var l:TextField = this.make_label( label_style );
			this.addChild( l );
		}
		
		public function make_label( label_style:Object ):TextField {
			
			// we create the text in its own movie clip
			
			var tf:TextField = new TextField();
            tf.x = 0;
			tf.y = 0;
			tf.text = label_style.text;
			
			var fmt:TextFormat = new TextFormat();
			fmt.color = label_style.colour;
			fmt.font = "Verdana";
			fmt.size = label_style.size;
			fmt.align = "right";
			tf.setTextFormat(fmt);
			
			tf.autoSize = "left";
			tf.visible = label_style.visible;
			return tf;
		}
		
		// move y axis labels to the correct x pos
		public function resize( sc:ScreenCoordsRadar ):void {

			var i:Number;
			var tf:TextField;
			var center:Number = sc.get_center_x();
			
			for( i=0; i<this.numChildren; i++ ) {
				// right align
				tf = this.getChildAt(i) as TextField;
				tf.x = center - tf.width;
			}
			
			// now move it to the correct Y, vertical center align
			for ( i = 0; i < this.numChildren; i++ ) {
				
				tf = this.getChildAt(i) as TextField;
				tf.y = ( sc.get_y_from_val( i, false ) - (tf.height / 2) );
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