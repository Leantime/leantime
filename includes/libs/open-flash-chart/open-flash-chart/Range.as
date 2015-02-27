package {
	
	public class Range
	{
		public var min:Number;
		public var max:Number;
		public var step:Number;
		public var offset:Boolean;
		
		public function Range( min:Number, max:Number, step:Number, offset:Boolean )
		{
			this.min = min;
			this.max = max;
			this.step = step;
			this.offset = offset;
		}
		
		public function count():Number {
			//
			// range, 5 - 10 = 10 - 5 = 5
			// range -5 - 5 = 5 - -5 = 10
			//
			//
			//  x_offset:
			//
			//   False            True
			//
			//  |               |
			//  |               |
			//  |               |
			//  +--+--+--+      |-+--+--+--+-+
			//  0  1  2  3        0  1  2  3
			//
			// Don't forget this is also used in radar axis
			//
			if( this.offset )
				return (this.max - this.min) + 1;
			else
				return this.max - this.min;			
		}
		
		public function toString():String {
			return 'Range : ' + this.min +', ' + this.max;
		}
	}
}