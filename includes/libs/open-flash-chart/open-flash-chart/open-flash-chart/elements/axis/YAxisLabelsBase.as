package elements.axis {
	import flash.display.Sprite;
	import elements.axis.YTextField;
	import flash.text.TextFormat;
	import org.flashdevelop.utils.FlashConnect;
	import br.com.stimuli.string.printf;
	import string.Utils;
	
	public class YAxisLabelsBase extends Sprite {
		
		private var steps:Number;
		private var right:Boolean;
		protected var style:Object;
		public var i_need_labels:Boolean;
		protected var lblText:String;
		public var y_max:Number;
		
		public function YAxisLabelsBase(json:Object, axis_name:String) {
			var i:Number;
			var s:String;
			var values:Array;
			var steps:Number;
			
			// TODO: calculate Y max from the data
			this.y_max = 10;
			
			if( json[axis_name] )
			{
				//
				// Old crufty JSON, refactor out at some point,
				// 
				//
				if( json[axis_name].labels is Array )
				{
					values = [];
					
					// use passed in min if provided else zero
					i = (json[axis_name] && json[axis_name].min) ? json[axis_name].min : 0;
					for each( s in json[axis_name].labels )
					{
						values.push( { val:s, pos:i } );
						i++;
					}
					//
					// alter the MinMax object:
					//
					// use passed in max if provided else the number of values less 1
					this.y_max = (json[axis_name] && json[axis_name].max) ? json[axis_name].max : values.length - 1;
					this.i_need_labels = false;
				}
			}

			//
			// an object, that contains an array of objects:
			//
			if( json[axis_name] )
			{
				if ( json[axis_name].labels is Object ) 
				{
					if ( json[axis_name].labels.text is String )
						this.lblText = json[axis_name].labels.text;

					var visibleSteps:Number = 1;
					if( json[axis_name].steps is Number )
						visibleSteps = json[axis_name].steps;
						
					if( json[axis_name].labels.steps is Number )
						visibleSteps = json[axis_name].labels.steps;
					
					if ( json[axis_name].labels.labels is Array )
					{
						values = [];
						// use passed in min if provided else zero
						var label_pos:Number = (json[axis_name] && json[axis_name].min) ? json[axis_name].min : 0;
						
						for each( var obj:Object in json[axis_name].labels.labels )
						{
							if(obj is Number)
							{
								values.push( { val:lblText, pos:obj } );
								//i = (obj > i) ? obj as Number : i;
							}
							else if(obj is String)
							{
								values.push( {
									val:	obj,
									pos:	label_pos,
									visible:	((label_pos % visibleSteps) == 0)
									} );
								//i = (obj > i) ? obj as Number : i;
							}
							else if (obj.y is Number)
							{
								s = (obj.text is String) ? obj.text : lblText;
								var style:Object = { val:s, pos:obj.y }
								if (obj.colour != null)
									style.colour = obj.colour;
									
								if (obj.size != null)
									style.size = obj.size;
									
								if (obj.rotate != null)
									style.rotate = obj.rotate;
									
								values.push( style );
								//i = (obj.y > i) ? obj.y : i;
							}
							
							label_pos++;
						}
						this.i_need_labels = false;
					}
				}				
			}
			
			this.steps = steps;
			
			var lblStyle:YLabelStyle = new YLabelStyle(json, name);
			this.style = lblStyle.style;
			
			//
			// TODO: hack, if the user has not defined either left or right
			//       by default set left axis to show and right to hide.
			//
			if ( !json[axis_name] && axis_name!='y_axis' )
				this.style.show_labels = false;
			//
			//
			
			// Default to using "rotate" from the y_axis level
			if ( json[axis_name] && json[axis_name].rotate ) {
				this.style.rotate = json[axis_name].rotate;
			}

			// Next override with any values at the y_axis.labels level
			if (( json[axis_name] != null ) && 
				( json[axis_name].labels != null ) ) {
				object_helper.merge_2( json[axis_name].labels, this.style );
			}
			
			this.add_labels(values);
		}
		
		private function add_labels(values:Array): void {
			
			// are the Y Labels visible?
			if( !this.style.show_labels )
				return;
			
			// labels
			var pos:Number = 0;
			
			for each ( var v:Object in values )
			{
				var lblStyle:Object = { };
				object_helper.merge_2( this.style, lblStyle );
				object_helper.merge_2( v, lblStyle );
			
				if ( lblStyle.visible )
				{
					var tmp:YTextField = this.make_label( lblStyle );
					tmp.y_val = v.pos;
					this.addChild(tmp);
				
					pos++;
				}
			}
		}

		/**
		 * This is called from the init function, because it is only after the Sprite
		 * is added to the stagethat we know the size of the flash window and know
		 * how many ticks/labelswe auto generate
		 */
		public function make_labels(min:Number, max:Number, steps:Number): void {

			tr.aces('make_labels', this.i_need_labels, min, max, false, steps, this.lblText);
			tr.aces(this.style.show_labels);
			
			if ( !this.i_need_labels )
				return;
				
			this.i_need_labels = false;
			this.make_labels_(min, max, false, steps, this.lblText);
		}
		
		//
		// use Y Min, Y Max and Y Steps to create an array of
		// Y labels:
		//
		protected function make_labels_(min:Number, max:Number, right:Boolean, steps:Number, lblText:String):void {
			var values:Array = [];
			
			var min_:Number = Math.min( min, max );
			var max_:Number = Math.max( min, max );
			
			// hack: hack: http://kb.adobe.com/selfservice/viewContent.do?externalId=tn_13989&sliceId=1
			max_ += 0.000004;
			
			var eek:Number = 0;
			for( var i:Number = min_; i <= max_; i+=steps ) {
				values.push( { val:lblText, pos:i } );
				
				// make sure we don't generate too many labels:
				if( eek++ > 250 ) break;
			}
			
			this.add_labels(values);
		}
		
		private function make_label( lblStyle:Object ):YTextField
		{
			
			lblStyle.colour = string.Utils.get_colour(lblStyle.colour);
			
			var tf:YTextField = new YTextField();
			//tf.border = true;
			tf.text = this.replace_magic_values(lblStyle.val, lblStyle.pos);
			var fmt:TextFormat = new TextFormat();
			fmt.color = lblStyle.colour;
			fmt.font = lblStyle.rotate == "vertical" ? "spArial" : "Verdana";
			fmt.size = lblStyle.size;
			fmt.align = "right";
			tf.setTextFormat(fmt);
			tf.autoSize = "right";
			if (lblStyle.rotate == "vertical")
			{
				tf.rotation = 270;
				tf.embedFonts = true;
				tf.antiAliasType = flash.text.AntiAliasType.ADVANCED;
			} 
			return tf;
		}

		// move y axis labels to the correct x pos
		public function resize( left:Number, sc:ScreenCoords ):void
		{
		}


		public function get_width():Number{
			var max:Number = 0;
			for( var i:Number=0; i<this.numChildren; i++ )
			{
				var tf:YTextField = this.getChildAt(i) as YTextField;
				max = Math.max( max, tf.width );
			}
			return max;
		}
		
		public function die(): void {
			
			while ( this.numChildren > 0 )
				this.removeChildAt(0);
		}

		private function replace_magic_values(labelText:String, yVal:Number):String {
			labelText = labelText.replace('#val#', NumberUtils.formatNumber(yVal));
			return labelText;
		}
	}
}