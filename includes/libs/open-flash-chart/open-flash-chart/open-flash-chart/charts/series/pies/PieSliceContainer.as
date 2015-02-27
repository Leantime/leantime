package charts.series.pies {
	
	import charts.series.Element;
	import flash.events.Event;
	import caurina.transitions.Tweener;
	import caurina.transitions.Equations;
	import flash.geom.Point;
	//import flash.events.Event;
	import flash.events.MouseEvent;
	
	public class PieSliceContainer extends Element {
		
		private var TO_RADIANS:Number = Math.PI / 180;
		
		private var animating:Boolean;
		private var pieSlice:PieSlice;
		private var pieLabel:PieLabel;
		private var pieRadius:Number;
		private var tick_size:Number = 10;
		private var tick_extension_size:Number = 4;
		private var label_margin:Number = 2;
		private var animationOffset:Number = 30;
		
		private var saveX:Number;
		private var saveY:Number;
		private var moveToX:Number;
		private var moveToY:Number;
		
		private var original_alpha:Number;
		
		

		//
		// this holds the slice and the text.
		// we want to rotate the slice, but not the text, so
		// this container holds both
		//
		public function PieSliceContainer( index:Number, value:Properties )
		{
			//
			// replace magic in the label:
			//
			// value.set('label', this.replace_magic_values( value.get('label') ) );
			
			
			tr.aces( 'pie', value.get('animate') );
			
			this.pieSlice = new PieSlice( index, value );
			this.addChild( this.pieSlice );
			var textlabel:String = value.get('label');
			
			//
			// we set the alpha of the parent container
			//
			this.alpha = this.original_alpha = value.get('alpha');
			//
			if ( !value.has('label-colour') )
				value.set('label-colour', value.get('colour'));
			
			var l:String = value.get('no-labels') ? '' : value.get('label');
			
			this.pieLabel = new PieLabel(
				{
					label:			l,
					colour:			value.get('label-colour'),
					'font-size':	value.get('font-size'),
					'on-click':		value.get('on-click') } )
			this.addChild( this.pieLabel );
			
			this.attach_events__(value);
			this.animating = false;
		}
		
		public function is_over():Boolean {
			return this.pieSlice.is_over;
		}
		
		public function get_slice():Element {
			return this.pieSlice;
		}
		
		public function get_label():PieLabel {
			return this.pieLabel;
		}
		
		
		//
		// the axis makes no sense here, let's override with null and write our own.
		//
		public override function resize( sc:ScreenCoordsBase ): void {}
		
		public function is_label_on_screen( sc:ScreenCoordsBase, slice_radius:Number ): Boolean {
			
			return this.pieLabel.move_label(
				slice_radius + 10,
				sc.get_center_x(),
				sc.get_center_y(),
				this.pieSlice.angle+(this.pieSlice.slice_angle/2) );
		}
		
		public function pie_resize( sc:ScreenCoordsBase, slice_radius:Number ): void {
			
			this.pieRadius = slice_radius;  // save off value for later use
			this.pieSlice.pie_resize(sc, slice_radius);

			var ticAngle:Number = this.getTicAngle();

			this.saveX = this.x;
			this.saveY = this.y;
			this.moveToX = this.x + (animationOffset * Math.cos(ticAngle * TO_RADIANS));
			this.moveToY = this.y + (animationOffset * Math.sin(ticAngle * TO_RADIANS));

			if (this.pieLabel.visible)
			{
				var lblRadius:Number = slice_radius + this.tick_size;
				var lblAngle:Number = ticAngle * TO_RADIANS;

				this.pieLabel.x = this.pieSlice.x + lblRadius * Math.cos(lblAngle);
				this.pieLabel.y = this.pieSlice.y + lblRadius * Math.sin(lblAngle);

				if (this.isRightSide())
				{
					this.pieLabel.x += this.tick_extension_size + this.label_margin;
				}
				else
				{
					//if legend stands to the left side of the pie
					this.pieLabel.x =
						this.pieLabel.x -
						this.pieLabel.width -
						this.tick_extension_size -
						this.label_margin -
						4;
				}
				this.pieLabel.y -= this.pieLabel.height / 2;

				this.drawTicLines();
			}
		}
		
		public override function get_tooltip():String {
			return this.pieSlice.get_tooltip();
		}
		
		public override function get_tip_pos():Object {
			var p:flash.geom.Point = this.localToGlobal( new flash.geom.Point(this.mouseX, this.mouseY) );
			return {x:p.x,y:p.y};
		}
		
		//
		// override this. I think this needs to be moved into an
		// animation manager?
		//
		// BTW this is called attach_events__ because Element has an
		//     attach_events already. I guess we need to fix one of them
		//
		protected function attach_events__(value:Properties):void {
			
			//
			// TODO: either move this into properties
			//       props.as(Array).get('moo');
			//       or get rid of type checking
			//
			
			var animate:Object = value.get('animate');
			if (!(animate is Array)) {
				if ((animate == null) || (animate)) {
					animate = [{"type":"bounce","distance":5}];
				}
				else {
					animate = new Array();
				}
			}
			
			var anims:Array = animate as Array;
			//
			// end to do
			//
			
			this.addEventListener(MouseEvent.MOUSE_OVER, this.mouseOver_first, false, 0, true);
			this.addEventListener(MouseEvent.MOUSE_OUT, this.mouseOut_first, false, 0, true);
						
			for each( var a:Object in anims ) {
				switch( a.type ) {
					
					case "bounce":
						// weak references so the garbage collector will kill them:
						this.addEventListener(MouseEvent.MOUSE_OVER, this.mouseOver_bounce_out, false, 0, true);
						this.addEventListener(MouseEvent.MOUSE_OUT, this.mouseOut_bounce_out, false, 0, true);
						this.animationOffset = a.distance;
						break;
						
					default:
						// weak references so the garbage collector will kill them:
						this.addEventListener(MouseEvent.MOUSE_OVER, this.mouseOver_alpha, false, 0, true);
						this.addEventListener(MouseEvent.MOUSE_OUT, this.mouseOut_alpha, false, 0, true);
						break;
				}
			}
		}
		
		//
		// stop multiple tweens from running
		//
		public function mouseOver_first(event:Event):void {
			
			if ( this.animating ) return;
			
			this.animating = true;
			Tweener.removeTweens(this);
		}
		
		public function mouseOut_first(event:Event):void {
			Tweener.removeTweens(this);
			this.animating = false;
		}
		
		public function mouseOver_bounce_out(event:Event):void {
			Tweener.addTween(this, {x:this.moveToX, y:this.moveToY, time:0.4, transition:"easeOutBounce"} );
		}
		
		public function mouseOut_bounce_out(event:Event):void {
			Tweener.addTween(this, {x:this.saveX, y:this.saveY, time:0.4, transition:"easeOutBounce"} );
		}
		
		public function mouseOver_alpha(event:Event):void {
			Tweener.addTween(this, { alpha:1, time:0.6, transition:Equations.easeOutCirc } );
		}

		public function mouseOut_alpha(event:Event):void {
			Tweener.addTween(this, { alpha:this.original_alpha, time:0.8, transition:Equations.easeOutElastic } );
		}

		public function getLabelTopY():Number
		{
			return this.pieLabel.y;
		}

		public function getLabelBottomY():Number
		{
			return this.pieLabel.y + this.pieLabel.height;
		}
		
		// Y value is from 0 to sc.Height from top to bottom
		public function moveLabelDown( sc:ScreenCoordsBase, minY:Number ):Number
		{
			if (this.pieLabel.visible)
			{
				var bAdjustToBottom:Boolean = false;
				var lblTop:Number = this.getLabelTopY();
				
				if (lblTop < minY)
				{
					// adjustment is positive
					var adjust:Number = minY - lblTop;
					if ((this.pieLabel.height + minY) > (sc.bottom - 1))
					{
						// calc adjust so label bottom is at bottom of screen
						adjust = sc.bottom - this.pieLabel.height - lblTop;
						bAdjustToBottom = true;
					}
					// Adjust the Y value
					this.pieLabel.y += adjust;

					if (!bAdjustToBottom)
					{
						var lblRadius:Number = this.pieRadius + this.tick_size;
						var calcSin:Number = ((this.pieLabel.y + this.pieLabel.height / 2) - this.pieSlice.y) / lblRadius;
						calcSin = Math.max( -1, Math.min(1, calcSin));
						var newAngle:Number = Math.asin(calcSin) / TO_RADIANS;

						if ((this.getTicAngle() > 90) && (this.getTicAngle() < 270))
						{
							newAngle = 180 - newAngle;
						}
						else if (this.getTicAngle() >= 270) 
						{
							newAngle = 360 + newAngle;
						}
						
						var newX:Number = this.pieSlice.x + lblRadius * Math.cos(newAngle * TO_RADIANS);
						if (this.isRightSide())
						{
							this.pieLabel.x = newX + this.tick_extension_size + this.label_margin;
						}
						else
						{
							//if legend stands to the left side of the pie
							this.pieLabel.x = newX - this.pieLabel.width -
												this.tick_extension_size - this.label_margin - 4;
						}
					}
				}
				this.drawTicLines();
				
				return this.pieLabel.y + this.pieLabel.height; 
			}
			else
			{
				return minY;
			}
		}
		
		// Y value is from 0 to sc.Height from top to bottom
		public function moveLabelUp( sc:ScreenCoordsBase, maxY:Number ):Number
		{
			if (this.pieLabel.visible)
			{
				var sign:Number = 1;
				var bAdjustToTop:Boolean = false;
				var lblBottom:Number = this.getLabelBottomY();
				if (lblBottom > maxY)
				{
					// adjustment is negative here
					var adjust:Number = maxY - lblBottom;
					if ((maxY - this.pieLabel.height) < (sc.top + 1))
					{
						// calc adjust so label top is at top of screen
						adjust = sc.top - this.getLabelTopY();
						bAdjustToTop = true;
					}
					// Adjust the Y value
					this.pieLabel.y += adjust;

					if (!bAdjustToTop)
					{
						var lblRadius:Number = this.pieRadius + this.tick_size;
						var calcSin:Number = ((this.pieLabel.y + this.pieLabel.height / 2) - this.pieSlice.y) / lblRadius;
						calcSin = Math.max( -1, Math.min(1, calcSin));
						var newAngle:Number = Math.asin(calcSin) / TO_RADIANS;

						if ((this.getTicAngle() > 90) && (this.getTicAngle() < 270))
						{
							newAngle = 180 - newAngle;
							sign = -1;
						}
						else if (this.getTicAngle() >= 270) 
						{
							newAngle = 360 + newAngle;
						}
						
						var newX:Number = this.pieSlice.x + lblRadius * Math.cos(newAngle * TO_RADIANS);
						if (this.isRightSide())
						{
							this.pieLabel.x = newX + this.tick_extension_size + this.label_margin;
						}
						else
						{
							//if legend stands to the left side of the pie
							this.pieLabel.x = newX - this.pieLabel.width -
										this.tick_extension_size - this.label_margin - 4;
						}
					}
				}
				this.drawTicLines();
				
				return this.pieLabel.y; 
			}
			else
			{
				return maxY;
			}
		}

		public function get_radius_offsets() :Object {
			// Update the label text here in case pie slices change dynamically
			//var lblText:String = this.getText();
			//this.myPieLabel.setText(lblText);
			
			var offset:Object = { top:animationOffset, right:animationOffset, 
									bottom:animationOffset, left:animationOffset };
			if (this.pieLabel.visible)
			{
				var ticAngle:Number = this.getTicAngle();
				var offset_threshold:Number = 20;
				var ticLength:Number = this.tick_size;
				
				if ((ticAngle >= 0) && (ticAngle <= 90)) 
				{
					offset.bottom = (ticAngle / 90) * ticLength + this.pieLabel.height / 2 + 1;
					offset.right = ((90 - ticAngle) / 90) * ticLength + this.tick_extension_size + this.label_margin + this.pieLabel.width;
				}
				else if ((ticAngle > 90) && (ticAngle <= 180)) 
				{
					offset.bottom = ((180 - ticAngle) / 90) * ticLength + this.pieLabel.height / 2 + 1;
					offset.left = ((ticAngle - 90) / 90) * ticLength + this.tick_extension_size + this.label_margin + this.pieLabel.width + 4;
				}
				else if ((ticAngle > 180) && (ticAngle < 270)) 
				{
					offset.top = ((ticAngle - 180) / 90) * ticLength + this.pieLabel.height / 2 + 1;
					offset.left = ((270 - ticAngle) / 90) * ticLength + this.tick_extension_size + this.label_margin + this.pieLabel.width + 4;
				}
				else // if ((ticAngle >= 270) && (ticAngle <= 360)) 
				{
					offset.top = ((360 - ticAngle) / 90) * ticLength + this.pieLabel.height / 2 + 1;
					offset.right = ((ticAngle - 270) / 90) * ticLength + this.tick_extension_size + this.label_margin + this.pieLabel.width;
				}
			}
			return offset;
		}
		protected function drawTicLines():void
		{
			if ((this.pieLabel.text != '') && (this.pieLabel.visible))
			{
				var ticAngle:Number = this.getTicAngle();
				
				var lblRadius:Number = this.pieRadius + this.tick_size;
				var lblAngle:Number = ticAngle * TO_RADIANS;

				var ticLblX:Number;
				var ticLblY:Number;
				if (this.pieSlice.isRightSide())
				{
					ticLblX = this.pieLabel.x - this.label_margin;
				}
				else
				{
					//if legend stands to the left side of the pie
					ticLblX = this.pieLabel.x + this.pieLabel.width + this.label_margin + 4;
				}
				ticLblY = this.pieLabel.y + this.pieLabel.height / 2;

				var ticArcX:Number = this.pieSlice.x + this.pieRadius * Math.cos(lblAngle);
				var ticArcY:Number = this.pieSlice.y + this.pieRadius * Math.sin(lblAngle);
				
				// Draw the line from the slice to the label
				this.graphics.clear();
				this.graphics.lineStyle( 1, this.pieSlice.get_colour(), 1 );
				
				// move to the end of the tic closest to the label
				this.graphics.moveTo(ticLblX, ticLblY);
				// draw a line the length of the tic extender
				if (this.pieSlice.isRightSide())
				{
					this.graphics.lineTo(ticLblX - this.tick_extension_size, ticLblY);
				}
				else
				{
					this.graphics.lineTo(ticLblX + this.tick_extension_size, ticLblY);
				}
				// Draw a line from the end of the tic extender to the arc
				this.graphics.lineTo(ticArcX, ticArcY);
			}
		}

		public function getTicAngle():Number
		{
			return this.pieSlice.getTicAngle();
		}

		public function isRightSide():Boolean
		{
			return this.pieSlice.isRightSide();
		}
	}
}
