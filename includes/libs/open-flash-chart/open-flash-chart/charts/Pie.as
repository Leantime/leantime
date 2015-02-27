package charts {
	import charts.series.pies.PieLabel;
	import flash.external.ExternalInterface;
	import string.Utils;
	import charts.series.Element;
	import charts.series.pies.PieSliceContainer;
	import charts.series.pies.DefaultPieProperties;
	import global.Global;
	
	import flash.display.Sprite;

	public class Pie extends Base
	{
		
		private var labels:Array;
		private var links:Array;
		private var colours:Array;
		private var gradientFill:String = 'true'; //toggle gradients
		private var border_width:Number = 1;
		private var label_line:Number;
		private var easing:Function;
		public var style:Object;
		public var total_value:Number = 0;
		
		// new:
		private var props:Properties;
		//
		
		public function Pie( json:Object )
		{
			this.labels = new Array();
			this.links = new Array();
			this.colours = new Array();
			
			this.style = {
				colours:			["#900000", "#009000"]	// slices colours
			}
			
			object_helper.merge_2( json, this.style );			
			
			for each( var colour:String in this.style.colours )
				this.colours.push( string.Utils.get_colour( colour ) );
				
			//
			//
			//
			this.props = new DefaultPieProperties(json);
			//
			//
			//
			
			this.label_line = 10;

			this.values = json.values;
			this.add_values();
		}
		
		
		//
		// Pie chart make is quite different to a normal make
		//
		public override function add_values():void {
//			this.Elements= new Array();
			
			//
			// Warning: this is our global singleton
			//
			var g:Global = Global.getInstance();
			
			var total:Number = 0;
			var slice_start:Number = this.props.get('start-angle');
			var i:Number;
			var val:Object;
			
			for each ( val in this.values ) {
				if( val is Number )
					total += val;
				else
					total += val.value;
			}
			this.total_value = total;
			
			i = 0;
			for each ( val in this.values ) {
				
				var value:Number = val is Number ? val as Number : val.value;
				var slice_angle:Number = value*360/total;
				
				if( slice_angle >= 0 )
				{
					
					var t:String = this.props.get('tip').replace('#total#', NumberUtils.formatNumber( this.total_value ));
					t = t.replace('#percent#', NumberUtils.formatNumber( value / this.total_value * 100 ) + '%');
				
					this.addChild(
						this.add_slice(
							i,
							slice_start,
							slice_angle,
							val,		// <-- NOTE: val (object) NOT value (a number)
							t,
							this.colours[(i % this.colours.length)]
							)
						);

					// TODO: fix this and remove
					// tmp.make_tooltip( this.key );
				}
				i++;
				slice_start += slice_angle;
			}
		}
		
		private function add_slice( index:Number, start:Number, angle:Number, value:Object, tip:String, colour:String ): PieSliceContainer {
			
				
			// Properties chain:
			//   pie-slice -> calculated-stuff -> pie
			//
			// calculated-stuff:
			var calculated_stuff:Properties = new Properties(
				{
					colour:				colour,		// <-- from the colour cycle array
					tip:				tip,		// <-- replaced the #total# & #percent# for this slice
					start:				start,		// <-- calculated
					angle:				angle		// <-- calculated
				},
				this.props );
			
			var tmp:Object = {};			
			if ( value is Number )
				tmp.value = value;
			else
				tmp = value;
				
			var p:Properties = new Properties( tmp, calculated_stuff );
			
			// no user defined label?
			if ( !p.has('label') )
				p.set('label', p.get('value').toString());
			
			// tr.aces( 'value', p.get('value'), p.get('label'), p.get('colour') );
			return new PieSliceContainer( index, p );
		}
		
		
		public override function closest( x:Number, y:Number ): Object {
			// PIE charts don't do closest to mouse tooltips
			return { Element:null, distance_x:0, distance_y:0 };
		}


		public override function resize( sc:ScreenCoordsBase ): void {
			var radius:Number = this.style.radius;
			if (isNaN(radius)){
				radius = ( Math.min( sc.width, sc.height ) / 2.0 );
				var offsets:Object = {top:0, right:0, bottom:0, left:0};
				trace("sc.width, sc.height, radius", sc.width, sc.height, radius);

				var i:Number;
				var sliceContainer:PieSliceContainer;

				// loop to gather and merge offsets
				for ( i = 0; i < this.numChildren; i++ ) {
					sliceContainer = this.getChildAt(i) as PieSliceContainer;
					var pie_offsets:Object = sliceContainer.get_radius_offsets();
					for (var key:Object in offsets) {
						if ( pie_offsets[key] > offsets[key] ) {
							offsets[key] = pie_offsets[key];
						}
					}
				}
				var vRadius:Number = radius;
				// Calculate minimum radius assuming the contraint is vertical
				// Shrink radius by the largest top/bottom offset
				vRadius -= Math.max(offsets.top, offsets.bottom);
				// check to see if the left/right labels will fit
				if ((vRadius + offsets.left) > (sc.width / 2))
				{
					//radius -= radius + offsets.left - (sc.width / 2);
					vRadius = (sc.width / 2) - offsets.left;
				}
				if ((vRadius + offsets.right) > (sc.width / 2))
				{
					//radius -= radius + offsets.right - (sc.width / 2);
					vRadius = (sc.width / 2) - offsets.right;
				}

				// Make sure the radius is at least 10
				radius = Math.max(vRadius, 10);
			}

			var rightTopTicAngle:Number		= 720;
			var rightTopTicIdx:Number		= -1;
			var rightBottomTicAngle:Number	= -720;
			var rightBottomTicIdx:Number	= -1;

			var leftTopTicAngle:Number		= 720;
			var leftTopTicIdx:Number		= -1;
			var leftBottomTicAngle:Number	= -720;
			var leftBottomTicIdx:Number		= -1;

			// loop and resize
			for ( i = 0; i < this.numChildren; i++ )
			{
				sliceContainer = this.getChildAt(i) as PieSliceContainer;
				sliceContainer.pie_resize(sc, radius);

				// While we are looping through the children, we determine which
				// labels are the starting points in each quadrant so that we
				// move the labels around to prevent overlaps
				var ticAngle:Number = sliceContainer.getTicAngle();
				if (ticAngle >= 270)
				{
					// Right side - Top
					if ((ticAngle < rightTopTicAngle) || (rightTopTicAngle <= 90))
					{
						rightTopTicAngle = ticAngle;
						rightTopTicIdx = i;
					}
					// Just in case no tics in Right-Bottom
					if ((rightBottomTicAngle < 0) ||
						((rightBottomTicAngle > 90) && (rightBottomTicAngle < ticAngle)))
					{
						rightBottomTicAngle = ticAngle;
						rightBottomTicIdx = i;
					}
				}
				else if (ticAngle <= 90)
				{
					// Right side - Bottom
					if ((ticAngle > rightBottomTicAngle) || (rightBottomTicAngle > 90))
					{
						rightBottomTicAngle = ticAngle;
						rightBottomTicIdx = i;
					}
					// Just in case no tics in Right-Top
					if ((rightTopTicAngle > 360) ||
						((rightTopTicAngle <= 90) && (ticAngle < rightBottomTicAngle)))
					{
						rightTopTicAngle = ticAngle;
						rightTopTicIdx = i;
					}
				}
				else if (ticAngle <= 180)
				{
				// Left side - Bottom
				if ((leftBottomTicAngle < 0) || (ticAngle < leftBottomTicAngle))
				{
					leftBottomTicAngle = ticAngle;
					leftBottomTicIdx = i;
				}
				// Just in case no tics in Left-Top
				if ((leftTopTicAngle > 360) || (leftTopTicAngle < ticAngle))
				{
					leftTopTicAngle = ticAngle;
					leftTopTicIdx = i;
				}
				}
				else
				{
					// Left side - Top
					if ((leftTopTicAngle > 360) || (ticAngle > leftTopTicAngle))
					{
						leftTopTicAngle = ticAngle;
						leftTopTicIdx = i;
					}
					// Just in case no tics in Left-Bottom
					if ((leftBottomTicAngle < 0) || (leftBottomTicAngle > ticAngle))
					{
						leftBottomTicAngle = ticAngle;
						leftBottomTicIdx = i;
					}
				}
			}

			// Make a clockwise pass on right side of pie trying to move
			// the labels so that they do not overlap
			var childIdx:Number = rightTopTicIdx;
			var yVal:Number = sc.top;
			var bDone:Boolean = false;
			while ((childIdx >= 0) && (!bDone))
			{
				sliceContainer = this.getChildAt(childIdx) as PieSliceContainer;
				ticAngle = sliceContainer.getTicAngle();
				if ((ticAngle >= 270) || (ticAngle <= 90))
				{
					yVal = sliceContainer.moveLabelDown(sc, yVal);
	
					childIdx++;
					if (childIdx >= this.numChildren) childIdx = 0;

					bDone = (childIdx == rightTopTicIdx);
				}
				else
				{
					bDone = true;
				}
			}

			// Make a counter-clockwise pass on right side of pie trying to move
			// the labels so that they do not overlap
			childIdx = rightBottomTicIdx;
			yVal = sc.bottom;
			bDone = false;
			while ((childIdx >= 0) && (!bDone))
			{
				sliceContainer = this.getChildAt(childIdx) as PieSliceContainer;
				ticAngle = sliceContainer.getTicAngle();
				if ((ticAngle >= 270) || (ticAngle <= 90))
				{
					yVal = sliceContainer.moveLabelUp(sc, yVal);

					childIdx--;
					if (childIdx < 0) childIdx = this.numChildren - 1;

					bDone = (childIdx == rightBottomTicIdx);
				}
				else
				{
					bDone = true;
				}
			}

			// Make a clockwise pass on left side of pie trying to move
			// the labels so that they do not overlap
			childIdx = leftBottomTicIdx;
			yVal = sc.bottom;
			bDone = false;
			while ((childIdx >= 0) && (!bDone))
			{
				sliceContainer = this.getChildAt(childIdx) as PieSliceContainer;
				ticAngle = sliceContainer.getTicAngle();
				if ((ticAngle > 90) && (ticAngle < 270))
				{
					yVal = sliceContainer.moveLabelUp(sc, yVal);

					childIdx++;
					if (childIdx >= this.numChildren) childIdx = 0;

					bDone = (childIdx == leftBottomTicIdx);
				}
				else
				{
					bDone = true;
				}
			}

			// Make a counter-clockwise pass on left side of pie trying to move
			// the labels so that they do not overlap
			childIdx = leftTopTicIdx;
			yVal = sc.top;
			bDone = false;
			while ((childIdx >= 0) && (!bDone))
			{
				sliceContainer = this.getChildAt(childIdx) as PieSliceContainer;
				ticAngle = sliceContainer.getTicAngle();
				if ((ticAngle > 90) && (ticAngle < 270))
				{
					yVal = sliceContainer.moveLabelDown(sc, yVal);

					childIdx--;
					if (childIdx < 0) childIdx = this.numChildren - 1;

					bDone = (childIdx == leftTopTicIdx);
				}
				else
				{
					bDone = true;
				}
			}
		}

		
		public override function toString():String {
			return "Pie with "+ this.numChildren +" children";
		}
	}
}
