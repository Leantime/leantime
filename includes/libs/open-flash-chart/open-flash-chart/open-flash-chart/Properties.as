package {

	import flash.utils.Dictionary;
	import string.Utils;
	
	public class Properties extends Object
	{
		private var _props:Dictionary;
		private var _parent:Properties;
		
		public function Properties( json:Object, parent:Properties=null ) {
		
			// Dictionary can use an object as a key
			this._props = new Dictionary();
			this._parent = parent;
			
			// tr.ace(json);
			
			for (var prop:String in json ) {
				
				// tr.ace( prop +' = ' + json[prop]);
				this._props[prop] = json[prop];
			}
		}
		
		public function get(name:String):* {
			
			// is this key in the dictionary?
			if ( name in this._props )
				return this._props[name];
			
			// test the parent
			if ( this._parent != null )
				return this._parent.get( name );
				
			//
			// key/property not found, report and dump the stack trace
			//
			var e:Error = new Error();
			var str:String = e.getStackTrace();
			
			tr.aces( 'ERROR: property not found', name, str);
			return Number.NEGATIVE_INFINITY;
		}
		
		//
		// this is a bit dirty, I wish I could do something like:
		//   props.get('colour').as_colour()
		//
		public function get_colour(name:String):Number {
			return Utils.get_colour(this.get(name));
		}
			
		// set does not recurse down, we don't want to set
		// our parents property
		public function set(name:String, value:Object):void {
			this._props[name] = value;
		}
		
		public function has(name:String):Boolean {
			if ( this._props[name] == null ) {
				if ( this._parent != null )
					return this._parent.has(name);
				else
					return false;
			}
			else
				return true;
		}
		
		public function set_parent(p:Properties):void {
			if ( this._parent != null )
				p.set_parent( this._parent );
		
			this._parent = p;
		}
		
		//
		// recurse and kill everything
		//
		public function die(): void {
			if ( this._parent )
				this._parent.die();
			
			for (var key:Object in this._props) {
				// iterates through each object key
				this._props[key] = null;
			}
			this._parent = null;
		}
	}
}