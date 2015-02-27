package elements.axis {
	import string.Utils;
		
	public class XLabelStyle
	{
		public var size:Number = 10;
		public var colour:Number = 0x000000;
		public var vertical:Boolean = false;
		public var diag:Boolean = false;
		public var step:Number = 1;
		public var show_labels:Boolean;

		public function XLabelStyle( json:Object )
		{
			if( !json )
				return;
				
			if( json.x_label_style == undefined )
				return;
			
			if( json.x_label_style.visible == undefined || json.x_label_style.visible )
			{
				this.show_labels = true;
				
				if( json.x_label_style.size != undefined )
					this.size = json.x_label_style.size;
					
				if( json.x_label_style.colour != undefined )
					this.colour = string.Utils.get_colour(json.x_label_style.colour);
	
				if( json.x_label_style.rotation != undefined )
				{
					this.vertical = (json.x_label_style.rotation==1);
					this.diag = (json.x_label_style.rotation==2);
				}
				
				if( json.x_label_style.step != undefined )
					this.step = json.x_label_style.step;
			}
			else
				this.show_labels = true;
		}
	}
}