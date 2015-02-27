package elements.axis {
	import string.Utils;
	
	public class YLabelStyle
	{
		public var style:Object;

		public function YLabelStyle( json:Object, name:String )
		{

			this.style = {	size: 10,
							colour: 0x000000,
							show_labels: true,
							visible: true
						 };

			var comma:Number;
			var none:Number;
			var tmp:Array;
			
			if( json[name+'_label_style'] == undefined )
				return;
					
			// is it CSV?
			comma = json[name+'_label_style'].lastIndexOf(',');
				
			if( comma<0 )
			{
				none = json[name+'_label_style'].lastIndexOf('none',0);
				if( none>-1 )
				{
					this.style.show_labels = false;
				}
			}
			else
			{
				tmp = json[name+'_label_style'].split(',');
				if( tmp.length > 0 )
					this.style.size = tmp[0];
					
				if( tmp.length > 1 )
					this.style.colour = Utils.get_colour(tmp[1]);
			}
		}
	}
}