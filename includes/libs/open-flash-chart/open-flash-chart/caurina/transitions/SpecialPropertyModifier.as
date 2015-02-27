package caurina.transitions {

	/**
	 * SpecialPropertyModifier
	 * A special property which actually acts on other properties
	 *
	 * @author		Zeh Fernando
	 * @version		1.0.0
	 * @private
	 */

	public class SpecialPropertyModifier {

		public var modifyValues:Function;
		public var getValue:Function;

		/**
		 * Builds a new special property modifier object.
		 * 
		 * @param		p_modifyFunction		Function		Function that returns the modifider parameters.
		 */
		public function SpecialPropertyModifier (p_modifyFunction:Function, p_getFunction:Function) {
			modifyValues = p_modifyFunction;
			getValue = p_getFunction;
		}

	/**
	 * Converts the instance to a string that can be used when trace()ing the object
	 */
	public function toString():String {
		var value:String = "";
		value += "[SpecialPropertyModifier ";
		value += "modifyValues:"+String(modifyValues);
		value += ", ";
		value += "getValue:"+String(getValue);
		value += "]";
		return value;
	}

	}

}
