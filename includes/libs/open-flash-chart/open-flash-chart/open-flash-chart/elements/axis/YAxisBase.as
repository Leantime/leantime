package elements.axis {
	import flash.display.Sprite;
	import string.Utils;
	
	public class YAxisBase extends Sprite {
		
		protected var stroke:Number;
		protected var tick_length:Number;
		protected var colour:Number;
		protected var grid_colour:Number;
		
		public var style:Object;
		
		protected var labels:YAxisLabelsBase;
		private var user_labels:Array;
		private var user_ticks:Boolean;
		
		function YAxisBase() {}
		
		public function init(json:Object): void {}
		
		// called once the sprite has been added to the stage
		// so now it has access to the stage
		protected function _init(json:Object, name:String, style:Object): void {
			
			this.style = style;
			
			if( json[name] )
				object_helper.merge_2( json[name], this.style );
				
			
			this.colour = Utils.get_colour( style.colour );
			this.grid_colour = Utils.get_colour( style['grid-colour'] );
			this.stroke = style.stroke;
			this.tick_length = style['tick-length'];
			
			tr.aces('YAxisBase auto', this.auto_range( 50001 ));
			tr.aces('YAxisBase min, max', this.style.min, this.style.max);
			
			
			if ( this.style.max == null ) {
				// we have labels, so use the number of
				// labels as Y MAX
				this.style.max = this.labels.y_max;
			}
			// make sure we don't have 1,000,000 steps
			var min:Number = Math.min(this.style.min, this.style.max);
			var max:Number = Math.max(this.style.min, this.style.max);
			this.style.steps = this.get_steps(min, max, this.stage.stageHeight);
			
			if ( this.labels.i_need_labels )
				this.labels.make_labels(min, max, this.style.steps);
			
			//
			// colour the grid lines
			//
			// TODO: remove this and
			//       this.user_ticks
			//       this.user_labels
			//
			if ((this.style.labels != null) &&
				(this.style.labels.labels != null) &&
				(this.style.labels.labels is Array) &&
				(this.style.labels.labels.length > 0))
			{
				this.user_labels = new Array();
				for each( var lbl:Object in this.style.labels.labels )
				{
					if (!(lbl is String)) {
						if (lbl.y != null) 
						{
							var tmpObj:Object = { y: lbl.y };
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
		
		public function auto_range(max:Number): Number {
			
			var maxValue:Number = Math.max(max) * 1.07;
			var l:Number = Math.round(Math.log(maxValue)/Math.log(10));
			var p:Number = Math.pow(10, l) / 2;
			maxValue = Math.round((maxValue * 1.1) / p) * p;
			return maxValue;
			
			
			/*
			var maxValue:Number = Math.max($bar_1->data) * 1.07;
			$l = round(log($maxValue)/log(10));
			$p = pow(10, $l) / 2;
			$maxValue = round($maxValue * 1.1 / $p) * $p;
			*/
			
			/*
			 * http://forums.openflashchart.com/viewtopic.php?f=5&t=617&start=0
			 * cdcarson
			    // y axis data...
    $counts = array_values($data);
    $ymax = max($counts);
    // add a bit of padding to the top, not strictly necessary...
    $ymax += ceil(.1 * $ymax);
    //$max_steps could be anything,depending on the height of the chart, font-size, etc..
    $max_steps = 10;
    /**
    * The step sizes to test are created using an
    * array of multipliers and a power of 10, starting at 0.
    * $step_size = $multiplier * pow(10, $exponent);
    * Assuming $multipliers = array(1, 2, 5) this would give us...
    * 1, 2, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000,...
    * /
    $n = 0;
    $multipliers = array(1, 2, 5);
    $num_multipliers = count($multipliers);
    $exponent = floor($n / $num_multipliers);
    $multiplier = $multipliers[$n % $num_multipliers];
    $step_size = $multiplier * pow(10, $exponent);
    $num_steps = ceil($ymax/$step_size);
    //keep testing until we have the right step_size...
    while ($num_steps >= $max_steps){
       $n ++;
       $exponent = floor($n / $num_multipliers);
       $multiplier = $multipliers[$n % $num_multipliers];
       $step_size = $multiplier * pow(10, $exponent);
       $num_steps = ceil($ymax/$step_size);
    }
    $yaxis = new y_axis();
    $yaxis->set_range(0, $ymax, $step_size);

			 */

		}
		
		public function get_style():Object { return null;  }
		
		//
		// may be called by the labels
		//
		public function set_y_max( m:Number ):void {
			this.style.max = m;
		}
		
		public function get_range():Range {
			return new Range( this.style.min, this.style.max, this.style.steps, this.style.offset );
		}
		
		public function get_width():Number {
			return this.stroke + this.tick_length + this.labels.width;
		}
		
		public function die(): void {
			
			//this.offset = null;
			this.style = null;
			if (this.labels != null) this.labels.die();
			this.labels = null;
			
			this.graphics.clear();
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}
		
		private function get_steps(min:Number, max:Number, height:Number):Number {
			// try to avoid infinite loops...
			if ( this.style.steps == 0 )
				this.style.steps = 1;
				
			if ( this.style.steps < 0 )
				this.style.steps *= -1;
			
			// how many steps (grid lines) do we have?
			var s:Number = (max - min) / this.style.steps;

			if ( s > (height/2) ) {
				// either no steps are set, or they are wrong and
				// we have more grid lines than pixels to show them.
				// E.g: 
				//      max = 1,001,000
				//      min =     1,000
				//      s   =   200,000
				return (max - min) / 5;
			}
			
			return this.style.steps;
		}
		
		public function resize(label_pos:Number, sc:ScreenCoords):void { }
		
		protected function resize_helper(label_pos:Number, sc:ScreenCoords, right:Boolean):void {
			
			// Set opacity for the first line to 0 (otherwise it overlaps the x-axel line)
			//
			// Bug? Does this work on graphs with minus values?
			//
			var i2:Number = 0;
			var i:Number;
			var y:Number;
			var lbl:Object;
			
			var min:Number = Math.min(this.style.min, this.style.max);
			var max:Number = Math.max(this.style.min, this.style.max);
		
			if( !right )
				this.labels.resize( label_pos, sc );
			else
				this.labels.resize( sc.right + this.stroke + this.tick_length, sc );
			
			if ( !this.style.visible )
				return;
			
			this.graphics.clear();
			this.graphics.lineStyle( 0, 0, 0 );
			
			if ( this.style['grid-visible'] )
				this.draw_grid_lines(this.style.steps, min, max, right, sc);
			
			var pos:Number;
			
			if (!right)
				pos = sc.left - this.stroke;
			else
				pos = sc.right;
			
			// Axis line:
			this.graphics.beginFill( this.colour, 1 );
			this.graphics.drawRect(
				int(pos),	// <-- pixel align
				sc.top,
				this.stroke,
				sc.height );
			this.graphics.endFill();
			
			// ticks..
			var width:Number;
			if (this.user_ticks) 
			{
				for each( lbl in this.user_labels )
				{
					y = sc.get_y_from_val(lbl.y, right);
					
					if ( !right )
						tick_pos = sc.left - this.stroke - this.tick_length;
					else
						tick_pos = sc.right + this.stroke;
					
					this.graphics.beginFill( this.colour, 1 );
					this.graphics.drawRect( tick_pos, y - (this.stroke / 2), this.tick_length, this.stroke );
					this.graphics.endFill();
				}
			}
			else
			{
				for(i=min; i<=max; i+=this.style.steps) {
					
					// start at the bottom and work up:
					y = sc.get_y_from_val(i, right);
					
					var tick_pos:Number;
					if ( !right )
						tick_pos = sc.left - this.stroke - this.tick_length;
					else
						tick_pos = sc.right + this.stroke;
					
					this.graphics.beginFill( this.colour, 1 );
					this.graphics.drawRect( tick_pos, y - (this.stroke / 2), this.tick_length, this.stroke );
					this.graphics.endFill();
				}
			}
		}
		
		private function draw_grid_lines(steps:Number, min:Number, max:Number, right:Boolean, sc:ScreenCoords): void {
			
			var y:Number;
			var lbl:Object;
			//
			// draw GRID lines
			//
			if (this.user_ticks) 
			{
				for each(lbl in this.user_labels )
				{
					y = sc.get_y_from_val(lbl.y, right);
					this.graphics.beginFill(lbl["grid-colour"], 1);
					this.graphics.drawRect( sc.left, y, sc.width, 1 );
					this.graphics.endFill();
				}
			}
			else
			{
				//
				// hack: http://kb.adobe.com/selfservice/viewContent.do?externalId=tn_13989&sliceId=1
				//
				max += 0.000004;
				
				for( var i:Number = min; i<=max; i+=steps ) {
					
					y = sc.get_y_from_val(i, right);
					this.graphics.beginFill( this.grid_colour, 1 );
					this.graphics.drawRect(
						int(sc.left),
						int(y),		// <-- make sure they are pixel aligned (2.5 - 3.5 == fuzzy lines)
						sc.width,
						1 );
					this.graphics.endFill();
				}
			}
		}
	}
}