package elements.menu {
	
	public class menu_item_factory {
		
		public static function make(chartID:String, style:Properties ):menuItem {
			
			switch( style.get('type') )
			{
				case 'camera-icon':
					return new CameraIcon(chartID, style);
					break;
					
				default:
					return new menuItem(chartID, style);
					break;
			}
		}
	}
}