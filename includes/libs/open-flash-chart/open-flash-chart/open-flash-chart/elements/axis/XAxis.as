package elements.axis {
	import flash.display.Sprite;
	import flash.geom.Matrix;
	import string.Utils;
	import charts.series.bars.Bar3D;
	import com.serialization.json.JSON;
	import Range;
	
	
	public class XAxis extends Sprite {

		private var steps:Number;
		private var alt_axis_colour:Number;
		private var alt_axis_step:Number;
		private var three_d:Boolean;
		private var three_d_height:Number;
		
		private var stroke:Number;
		private var tick_height:Number;
		private var colour:Number;
		public var offset:Boolean;
		private var grid_colour:Number;
		private var grid_visible:Boolean;
		private var user_ticks:Boolean;
		private var user_labels:Array;
		
		// make this private
		public var labels:XAxisLabels;

		private var style:Object;
		
		function XAxis( json:Object, min:Number, max:Number )
		{
			// default values
			this.style = {
				stroke:			2,
				'tick-height':	3,
				colour:			'#784016',
				offset:			true,
				'grid-colour':	'#F5E1AA',
				'grid-visible':	true,
				'3d':			0,
				steps:			1,
				min:			0,
				max:			null
			};
			
			if( json != null )
				object_helper.merge_2( json.x_axis, this.style );
			
			this.calcSteps();
			
			this.stroke = this.style.stroke;
			this.tick_height = this.style['tick-height'];
			this.colour = this.style.colour;
			// is the axis offset (see ScreenCoords)
			this.offset = this.style.offset;
			this.grid_visible = this.style['grid-visible'];

			this.colour = Utils.get_colour( this.style.colour );
			this.grid_colour = Utils.get_colour( this.style['grid-colour'] );
			
				
			if( style['3d'] > 0 )
			{
				this.three_d = true;
				this.three_d_height = int( this.style['3d'] );
			}
			else
				this.three_d = false;

			// Patch from Will Henry
			if( json )
			{
				if( json.x_label_style != undefined ) {
					if( json.x_label_style.alt_axis_step != undefined )
						this.alt_axis_step = json.x_label_style.alt_axis_step;
						
					if( json.x_label_style.alt_axis_colour != undefined )
						this.alt_axis_colour = Utils.get_colour(json.x_label_style.alt_axis_colour);
				}
			}
			
			this.labels = new XAxisLabels( json );
			this.addChild( this.labels );
						
			// the X Axis labels *may* require info from
			// this.obs
			if( !this.range_set() )
			{
				//
				// the user has not told us how long the X axis
				// is, so we figure it out:
				//
				if( this.labels.need_labels ) {
					//
					// No X Axis labels set:
					//
					
					// tr.aces( 'max x', this.obs.get_min_x(), this.obs.get_max_x() );
					this.set_range( min, max );
				}
				else
				{
					//
					// X Axis labels used, even so, make the chart
					// big enough to show all values
					//
					// tr.aces('x labels', this.obs.get_min_x(), this.x_axis.labels.count(), this.obs.get_max_x());
					if ( this.labels.count() > max ) {
						
						// Data and labesl are OK
						this.set_range( 0, this.labels.count() );
					} else {
						
						// There is more data than labels -- oops
						this.set_range( min, max );
					}
				}
			}
			else
			{
				//range set, but no labels...
				this.labels.auto_label( this.get_range(), this.get_steps() );
			}
			
			// custom ticks:
			this.make_user_ticks();
		}
		
		//
		// a little hacky, but we inspect the labels
		// to see if we need to display user custom ticks
		//
		private function make_user_ticks():void {
			
			if ((this.style.labels != null) &&
				(this.style.labels.labels != null) &&
				(this.style.labels.labels is Array) &&
				(this.style.labels.labels.length > 0))
			{
				this.user_labels = new Array();
				for each( var lbl:Object in this.style.labels.labels )
				{
					if (!(lbl is String)) {
						if (lbl.x != null) 
						{
							var tmpObj:Object = { x: lbl.x };
							if (lbl["grid-colour"])
							{
								tmpObj["grid-colour"] = Utils.get_colour(lbl["grid-colour"]);
							}
							else
							{
								tmpObj["grid-colour"] = this.grid_colour;
							}
							
							this.user_ticks = true;
							this.user_labels.push(tmpObj);
						}
					}
				}
			}
		}
		
		private function calcSteps():void {
			if (this.style.max == null) {
				this.steps = 1;
			}
			else {
				var xRange:Number = this.style.max - this.style.min;
				var rev:Boolean = (this.style.min >= this.style.max); // min-max reversed?
				this.steps = ((this.style.steps != null) && 
											(this.style.steps != 0)) ? this.style.steps : 1;

				// force max of 250 labels and tick marks
				if ((Math.abs(xRange) / this.steps) > 250) this.steps = xRange / 250;

				// guarantee lblSteps is the proper sign
				this.steps = rev ? -Math.abs(this.steps) : Math.abs(this.steps);
			}
		}

		//
		// have we been passed a range? (min and max?)
		//
		public function range_set():Boolean {
			return this.style.max != null;
		}
		
		//
		// We don't have a range, so we need to calculate it.
		// grid lines, depends on number of values,
		// the X Axis labels and X min and X max
		//
		public function set_range( min:Number, max:Number ):void
		{
			this.style.min = min;
			this.style.max = max;
			// Calc new steps
			this.calcSteps();
			
			this.labels.auto_label( this.get_range(), this.get_steps() );
		}
		
		//
		// how many items in the X axis?
		//
		public function get_range():Range {
			return new Range( this.style.min, this.style.max, this.steps, this.offset );
		}
		
		public function get_steps():Number {
			return this.steps;
		}
		
		public function resize( sc:ScreenCoords, yPos:Number ):void
		{
			this.graphics.clear();
			
			//
			// Grid lines
			//
			if (this.user_ticks) 
			{
				for each( var lbl:Object in this.user_labels )
				{
					this.graphics.beginFill(lbl["grid-colour"], 1);
					var xVal:Number = sc.get_x_from_val(lbl.x);
					this.graphics.drawRect( xVal, sc.top, 1, sc.height );
					this.graphics.endFill();
				}
			}
			else if(this.grid_visible)
			{
				var rev:Boolean = (this.style.min >= this.style.max); // min-max reversed?
				var tickMax:Number = /*(rev && this.style.offset) ? this.style.max-2 : */ this.style.max
				
				for( var i:Number=this.style.min; rev ? i >= tickMax : i <= tickMax; i+=this.steps )
				{
					if( ( this.alt_axis_step > 1 ) && ( i % this.alt_axis_step == 0 ) )
						this.graphics.beginFill(this.alt_axis_colour, 1);
					else
						this.graphics.beginFill(this.grid_colour, 1);
					
					xVal = sc.get_x_from_val(i);
					this.graphics.drawRect( xVal, sc.top, 1, sc.height );
					this.graphics.endFill();
				}
			}
			
			if( this.three_d )
				this.three_d_axis( sc );
			else
				this.two_d_axis( sc );
			
			this.labels.resize( sc, yPos );
		}
			
		public function three_d_axis( sc:ScreenCoords ):void
		{
			
			// for 3D
			var h:Number = this.three_d_height;
			var offset:Number = 12;
			var x_axis_height:Number = h+offset;
			
			//
			// ticks
			var item_width:Number = sc.width / this.style.max;
		
			// turn off out lines:
			this.graphics.lineStyle(0,0,0);
			
			var w:Number = 1;

			if (this.user_ticks) 
			{
				for each( var lbl:Object in this.user_labels )
				{
					var xVal:Number = sc.get_x_from_val(lbl.x);
					this.graphics.beginFill(this.colour, 1);
					this.graphics.drawRect( xVal, sc.bottom + x_axis_height, w, this.tick_height );
					this.graphics.endFill();
				}
			}
			else
			{
				for( var i:Number=0; i < this.style.max; i+=this.steps )
				{
					var pos:Number = sc.get_x_tick_pos(i);
					
					this.graphics.beginFill(this.colour, 1);
					this.graphics.drawRect( pos, sc.bottom + x_axis_height, w, this.tick_height );
					this.graphics.endFill();
				}
			}

			
			var lighter:Number = Bar3D.Lighten( this.colour );
			
			// TOP
			var colors:Array = [this.colour,lighter];
			var alphas:Array = [100,100];
			var ratios:Array = [0,255];
		
			var matrix:Matrix = new Matrix();
			matrix.createGradientBox(sc.width_(), offset, (270 / 180) * Math.PI, sc.left-offset, sc.bottom );
			this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
			this.graphics.moveTo(sc.left,sc.bottom);
			this.graphics.lineTo(sc.right,sc.bottom);
			this.graphics.lineTo(sc.right-offset,sc.bottom+offset);
			this.graphics.lineTo(sc.left-offset,sc.bottom+offset);
			this.graphics.endFill();
		
			// front
			colors = [this.colour,lighter];
			alphas = [100,100];
			ratios = [0, 255];
			
			matrix.createGradientBox( sc.width_(), h, (270 / 180) * Math.PI, sc.left - offset, sc.bottom + offset );
			this.graphics.beginGradientFill("linear", colors, alphas, ratios, matrix);
			this.graphics.moveTo(sc.left-offset,sc.bottom+offset);
			this.graphics.lineTo(sc.right-offset,sc.bottom+offset);
			this.graphics.lineTo(sc.right-offset,sc.bottom+offset+h);
			this.graphics.lineTo(sc.left-offset,sc.bottom+offset+h);
			this.graphics.endFill();
			
			// right side
			colors = [this.colour,lighter];
			alphas = [100,100];
			ratios = [0,255];
			
		//	var matrix:Object = { matrixType:"box", x:box.left - offset, y:box.bottom + offset, w:box.width_(), h:h, r:(225 / 180) * Math.PI };
			matrix.createGradientBox( sc.width_(), h, (225 / 180) * Math.PI, sc.left - offset, sc.bottom + offset );
			this.graphics.beginGradientFill("linear", colors, alphas, ratios, matrix);
			this.graphics.moveTo(sc.right,sc.bottom);
			this.graphics.lineTo(sc.right,sc.bottom+h);
			this.graphics.lineTo(sc.right-offset,sc.bottom+offset+h);
			this.graphics.lineTo(sc.right-offset,sc.bottom+offset);
			this.graphics.endFill();
			
		}
		
		// 2D:
		public function two_d_axis( sc:ScreenCoords ):void
		{
			//
			// ticks
			var item_width:Number = sc.width / this.style.max;
			var left:Number = sc.left+(item_width/2);
		
			//this.graphics.clear();
			// Axis line:
			this.graphics.lineStyle( 0, 0, 0 );
			this.graphics.beginFill( this.colour );
			this.graphics.drawRect( sc.left, sc.bottom, sc.width, this.stroke );
			this.graphics.endFill();
			
			
			if (this.user_ticks) 
			{
				for each( var lbl:Object in this.user_labels )
				{
					var xVal:Number = sc.get_x_from_val(lbl.x);
					this.graphics.beginFill(this.colour, 1);
					this.graphics.drawRect( xVal-(this.stroke/2), sc.bottom + this.stroke, this.stroke, this.tick_height );
					this.graphics.endFill();
				}
			}
			else
			{
				for( var i:Number=this.style.min; i <= this.style.max; i+=this.steps )
				{
					xVal = sc.get_x_from_val(i);
					this.graphics.beginFill(this.colour, 1);
					this.graphics.drawRect( xVal-(this.stroke/2), sc.bottom + this.stroke, this.stroke, this.tick_height );
					this.graphics.endFill();
				}
			}
		}
		
		public function get_height():Number {
			if( this.three_d )
			{
				// 12 is the size of the slanty
				// 3D part of the X axis
				return this.three_d_height+12+this.tick_height + this.labels.get_height();
			}
			else
				return this.stroke + this.tick_height + this.labels.get_height();
		}
		
		public function first_label_width() : Number
		{
			return this.labels.first_label_width();
		}
		
		public function last_label_width() : Number
		{
			return this.labels.last_label_width();
		}
		
		public function die(): void {
			
			this.style = null;
		
			this.graphics.clear();
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
			
			if (this.labels != null)
				this.labels.die();
			this.labels = null;
		}
	}
}
