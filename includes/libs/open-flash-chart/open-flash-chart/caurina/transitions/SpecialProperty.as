package caurina.transitions {
	
	/**
	 * SpecialProperty
	 * A kind of a getter/setter for special properties
	 *
	 * @author		Zeh Fernando
	 * @version		1.0.0
	 * @private
	 */

	public class SpecialProperty {
	
		public var getValue:Function;
		public var setValue:Function;
		public var parameters:Array;

		/**
		 * Builds a new special property object.
		 * 
		 * @param		p_getFunction		Function	Reference to the function used to get the special property value
		 * @param		p_setFunction		Function	Reference to the function used to set the special property value
		 */
		public function SpecialProperty (p_getFunction:Function, p_setFunction:Function, p_parameters:Array = null) {
			getValue = p_getFunction;
			setValue = p_setFunction;
			parameters = p_parameters;
		}
	
		/**
		 * Converts the instance to a string that can be used when trace()ing the object
		 */
		public function toString():String {
			var value:String = "";
			value += "[SpecialProperty ";
			value += "getValue:"+String(getValue); // .toString();
			value += ", ";
			value += "setValue:"+String(setValue); // .toString();
			value += ", ";
			value += "parameters:"+String(parameters); // .toString();
			value += "]";
			return value;
		}
	}
}