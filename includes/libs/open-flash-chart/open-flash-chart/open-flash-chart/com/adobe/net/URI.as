/*
Adobe Systems Incorporated(r) Source Code License Agreement
Copyright(c) 2005 Adobe Systems Incorporated. All rights reserved.
	
Please read this Source Code License Agreement carefully before using
the source code.
	
Adobe Systems Incorporated grants to you a perpetual, worldwide, non-exclusive,
no-charge, royalty-free, irrevocable copyright license, to reproduce,
prepare derivative works of, publicly display, publicly perform, and
distribute this source code and such derivative works in source or
object code form without any attribution requirements.
	
The name "Adobe Systems Incorporated" must not be used to endorse or promote products
derived from the source code without prior written permission.
	
You agree to indemnify, hold harmless and defend Adobe Systems Incorporated from and
against any loss, damage, claims or lawsuits, including attorney's
fees that arise or result from your use or distribution of the source
code.
	
THIS SOURCE CODE IS PROVIDED "AS IS" AND "WITH ALL FAULTS", WITHOUT
ANY TECHNICAL SUPPORT OR ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING,
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. ALSO, THERE IS NO WARRANTY OF
NON-INFRINGEMENT, TITLE OR QUIET ENJOYMENT. IN NO EVENT SHALL MACROMEDIA
OR ITS SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOURCE CODE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

package com.adobe.net
{
	import flash.utils.ByteArray;
	
	/**
	 * This class implements functions and utilities for working with URI's
	 * (Universal Resource Identifiers).  For technical description of the
	 * URI syntax, please see RFC 3986 at http://www.ietf.org/rfc/rfc3986.txt
	 * or do a web search for "rfc 3986".
	 * 
	 * <p>The most important aspect of URI's to understand is that URI's
	 * and URL's are not strings.  URI's are complex data structures that
	 * encapsulate many pieces of information.  The string version of a
	 * URI is the serialized representation of that data structure.  This
	 * string serialization is used to provide a human readable
	 * representation and a means to transport the data over the network
	 * where it can then be parsed back into its' component parts.</p>
	 * 
	 * <p>URI's fall into one of three categories:
	 * <ul>
	 *  <li>&lt;scheme&gt;:&lt;scheme-specific-part&gt;#&lt;fragment&gt;		(non-hierarchical)</li>
	 *  <li>&lt;scheme&gt;:<authority&gt;&lt;path&gt;?&lt;query&gt;#&lt;fragment&gt;	(hierarchical)</li>
	 *  <li>&lt;path&gt;?&lt;query&gt;#&lt;fragment&gt;						(relative hierarchical)</li>
	 * </ul></p>
	 * 
	 * <p>The query and fragment parts are optional.</p>
	 * 
	 * <p>This class supports both non-hierarchical and hierarchical URI's</p>
	 * 
	 * <p>This class is intended to be used "as-is" for the vast majority
	 * of common URI's.  However, if your application requires a custom
	 * URI syntax (e.g. custom query syntax or special handling of
	 * non-hierarchical URI's), this class can be fully subclassed.  If you
	 * intended to subclass URI, please see the source code for complete
	 * documation on protected members and protected fuctions.</p>
	 * 
	 * @langversion ActionScript 3.0
	 * @playerversion Flash 9.0 
	 */
	public class URI
	{	
		// Here we define which characters must be escaped for each
		// URI part.  The characters that must be escaped for each
		// part differ depending on what would cause ambiguous parsing.
		// RFC 3986 sec. 2.4 states that characters should only be
		// encoded when they would conflict with subcomponent delimiters.
		// We don't want to over-do the escaping.  We only want to escape
		// the minimum needed to prevent parsing problems.
		
		// space and % must be escaped in all cases.  '%' is the delimiter
		// for escaped characters.
		public static const URImustEscape:String =	" %";
		
		// Baseline of what characters must be escaped
		public static const URIbaselineEscape:String = URImustEscape + ":?#/@";
		
		// Characters that must be escaped in the part part.
		public static const URIpathEscape:String = URImustEscape + "?#";
		
		// Characters that must be escaped in the query part, if setting
		// the query as a whole string.  If the query is set by
		// name/value, URIqueryPartEscape is used instead.
		public static const URIqueryEscape:String = URImustEscape + "#";
		
		// This is what each name/value pair must escape "&=" as well
		// so they don't conflict with the "param=value&param2=value2"
		// syntax.
		public static const URIqueryPartEscape:String = URImustEscape + "#&=";
		
		// Non-hierarchical URI's can have query and fragment parts, but
		// we also want to prevent '/' otherwise it might end up looking
		// like a hierarchical URI to the parser.
		public static const URInonHierEscape:String = 	URImustEscape + "?#/";
		
		// Baseline uninitialized setting for the URI scheme.
		public static const UNKNOWN_SCHEME:String = "unknown";
		
		// The following bitmaps are used for performance enhanced
		// character escaping.
		
		// Baseline characters that need to be escaped.  Many parts use
		// this.
		protected static const URIbaselineExcludedBitmap:URIEncodingBitmap =
			new URIEncodingBitmap(URIbaselineEscape);
		
		// Scheme escaping bitmap
		protected static const URIschemeExcludedBitmap:URIEncodingBitmap = 
			URIbaselineExcludedBitmap;
		
		// User/pass escaping bitmap
		protected static const URIuserpassExcludedBitmap:URIEncodingBitmap =
			URIbaselineExcludedBitmap;
		
		// Authority escaping bitmap
		protected static const URIauthorityExcludedBitmap:URIEncodingBitmap =
			URIbaselineExcludedBitmap;
			
		// Port escaping bitmap
		protected static const URIportExludedBitmap:URIEncodingBitmap = 
			URIbaselineExcludedBitmap;
		
		// Path escaping bitmap
		protected static const URIpathExcludedBitmap:URIEncodingBitmap =
		 	new URIEncodingBitmap(URIpathEscape);
			
		// Query (whole) escaping bitmap
		protected static const URIqueryExcludedBitmap:URIEncodingBitmap =
			new URIEncodingBitmap(URIqueryEscape);
			
		// Query (individual parts) escaping bitmap
		protected static const URIqueryPartExcludedBitmap:URIEncodingBitmap =
			new URIEncodingBitmap(URIqueryPartEscape);
			
		// Fragments are the last part in the URI.  They only need to
		// escape space, '#', and '%'.  Turns out that is what query
		// uses too.
		protected static const URIfragmentExcludedBitmap:URIEncodingBitmap =
			URIqueryExcludedBitmap;
			
		// Characters that need to be escaped in the non-hierarchical part
		protected static const URInonHierexcludedBitmap:URIEncodingBitmap =
			new URIEncodingBitmap(URInonHierEscape);
			
		// Values used by getRelation()
		public static const NOT_RELATED:int = 0;
		public static const CHILD:int = 1;
		public static const EQUAL:int = 2;
		public static const PARENT:int = 3;

		//-------------------------------------------------------------------
		// protected class members
		//-------------------------------------------------------------------
		protected var _valid:Boolean = false;
		protected var _relative:Boolean = false;
		protected var _scheme:String = "";
		protected var _authority:String = "";
		protected var _username:String = "";
		protected var _password:String = "";
		protected var _port:String = "";
		protected var _path:String = "";
		protected var _query:String = "";
		protected var _fragment:String = "";
		protected var _nonHierarchical:String = "";
		protected static var _resolver:IURIResolver = null;


		/**
		 *  URI Constructor.  If no string is given, this will initialize
		 *  this URI object to a blank URI.
		 */
		public function URI(uri:String = null) : void	
		{
			if (uri == null)
				initialize();
			else
				constructURI(uri);
		}

		
		/**
		 * @private
		 * Method that loads the URI from the given string.
		 */
		protected function constructURI(uri:String) : Boolean
		{
			if (!parseURI(uri))
				_valid = false;
				
			return isValid();
		}
		
		
		/**
		 * @private Private initializiation.
		 */
		protected function initialize() : void
		{
			_valid = false;
			_relative = false;
		
			_scheme = UNKNOWN_SCHEME;
			_authority = "";
			_username = "";
			_password = "";
			_port = "";
			_path = "";
			_query = "";
			_fragment = "";
		
			_nonHierarchical = "";
		}	
		
		/**
		 * @private Accessor to explicitly set/get the hierarchical
		 * state of the URI.
		 */
		protected function set hierState(state:Boolean) : void
		{
			if (state)
			{
				// Clear the non-hierarchical data
				_nonHierarchical = "";
		
				// Also set the state vars while we are at it
				if (_scheme == "" || _scheme == UNKNOWN_SCHEME)
					_relative = true;
				else
					_relative = false;
		
				if (_authority.length == 0 && _path.length == 0)
					_valid = false;
				else
					_valid = true;
			}
			else
			{
				// Clear the hierarchical data
				_authority = "";
				_username = "";
				_password = "";
				_port = "";
				_path = "";
		
				_relative = false;
		
				if (_scheme == "" || _scheme == UNKNOWN_SCHEME)
					_valid = false;
				else
					_valid = true;
			}
		}
		protected function get hierState() : Boolean
		{
			return (_nonHierarchical.length == 0);
		}
		
		
		/**
		 * @private Functions that performs some basic consistency validation.
		 */
		protected function validateURI() : Boolean
		{
			// Check the scheme
			if (isAbsolute())
			{
				if (_scheme.length <= 1 || _scheme == UNKNOWN_SCHEME)
				{
					// we probably parsed a C:\ type path or no scheme
					return false;
				}
				else if (verifyAlpha(_scheme) == false)
					return false;  // Scheme contains bad characters
			}
			
			if (hierState)
			{
				if (_path.search('\\') != -1)
					return false;  // local path
				else if (isRelative() == false && _scheme == UNKNOWN_SCHEME)
					return false;  // It's an absolute URI, but it has a bad scheme
			}
			else
			{
				if (_nonHierarchical.search('\\') != -1)
					return false;  // some kind of local path
			}
		
			// Looks like it's ok.
			return true;
		}
		
		
		/**
		 * @private
		 *
		 * Given a URI in string format, parse that sucker into its basic
		 * components and assign them to this object.  A URI is of the form:
		 *    <scheme>:<authority><path>?<query>#<fragment>
		 *
		 * For simplicity, we parse the URI in the following order:
		 * 		
		 *		1. Fragment (anchors)
		 * 		2. Query	(CGI stuff)
		 * 		3. Scheme	("http")
		 * 		4. Authority (host name)
		 * 		5. Username/Password (if any)
		 * 		6. Port		(server port if any)
		 *		7. Path		(/homepages/mypage.html)
		 *
		 * The reason for this order is to minimize any parsing ambiguities.
		 * Fragments and queries can contain almost anything (they are parts
		 * that can contain custom data with their own syntax).  Parsing
		 * them out first removes a large chance of parsing errors.  This
		 * method expects well formed URI's, but performing the parse in
		 * this order makes us a little more tolerant of user error.
		 * 
		 * REGEXP
		 * Why doesn't this use regular expressions to parse the URI?  We
		 * have found that in a real world scenario, URI's are not always
		 * well formed.  Sometimes characters that should have been escaped
		 * are not, and those situations would break a regexp pattern.  This
		 * function attempts to be smart about what it is parsing based on
		 * location of characters relative to eachother.  This function has
		 * been proven through real-world use to parse the vast majority
		 * of URI's correctly.
		 *
		 * NOTE
		 * It is assumed that the string in URI form is escaped.  This function
		 * does not escape anything.  If you constructed the URI string by
		 * hand, and used this to parse in the URI and still need it escaped,
		 * call forceEscape() on your URI object.
		 *
		 * Parsing Assumptions
		 * This routine assumes that the URI being passed is well formed.
		 * Passing things like local paths, malformed URI's, and the such
		 * will result in parsing errors.  This function can handle
		 * 	 - absolute hierarchical (e.g. "http://something.com/index.html),
		 *   - relative hierarchical (e.g. "../images/flower.gif"), or
		 *   - non-hierarchical URIs (e.g. "mailto:jsmith@fungoo.com").
		 * 
		 * Anything else will probably result in a parsing error, or a bogus
		 * URI object.
		 * 
		 * Note that non-hierarchical URIs *MUST* have a scheme, otherwise
		 * they will be mistaken for relative URI's.
		 * 
		 * If you are not sure what is being passed to you (like manually
		 * entered text from UI), you can construct a blank URI object and
		 * call unknownToURI() passing in the unknown string.
		 * 
		 * @return	true if successful, false if there was some kind of
		 * parsing error
		 */
		protected function parseURI(uri:String) : Boolean
		{
			var baseURI:String = uri;
			var index:int, index2:int;
		
			// Make sure this object is clean before we start.  If it was used
			// before and we are now parsing a new URI, we don't want any stale
			// info lying around.
			initialize();
		
			// Remove any fragments (anchors) from the URI
			index = baseURI.indexOf("#");
			if (index != -1)
			{
				// Store the fragment piece if any
				if (baseURI.length > (index + 1)) // +1 is to skip the '#'
					_fragment = baseURI.substr(index + 1, baseURI.length - (index + 1)); 
		
				// Trim off the fragment
				baseURI = baseURI.substr(0, index);
			}
		
			// We need to strip off any CGI parameters (eg '?param=bob')
			index = baseURI.indexOf("?");
			if (index != -1)
			{
				if (baseURI.length > (index + 1))
					_query = baseURI.substr(index + 1, baseURI.length - (index + 1)); // +1 is to skip the '?'
		
				// Trim off the query
				baseURI = baseURI.substr(0, index);
			}
		
			// Now try to find the scheme part
			index = baseURI.search(':');
			index2 = baseURI.search('/');
		
			var containsColon:Boolean = (index != -1);
			var containsSlash:Boolean = (index2 != -1);
		
			// This value is indeterminate if "containsColon" is false.
			// (if there is no colon, does the slash come before or
			// after said non-existing colon?)
			var colonBeforeSlash:Boolean = (!containsSlash || index < index2);
		
			// If it has a colon and it's before the first slash, we will treat
			// it as a scheme.  If a slash is before a colon, there must be a
			// stray colon in a path or something.  In which case, the colon is
			// not the separator for the scheme.  Technically, we could consider
			// this an error, but since this is not an ambiguous state (we know
			// 100% that this has no scheme), we will keep going.
			if (containsColon && colonBeforeSlash)
			{
				// We found a scheme
				_scheme = baseURI.substr(0, index);
				
				// Normalize the scheme
				_scheme = _scheme.toLowerCase();
		
				baseURI = baseURI.substr(index + 1);
		
				if (baseURI.substr(0, 2) == "//")
				{
					// This is a hierarchical URI
					_nonHierarchical = "";
		
					// Trim off the "//"
					baseURI = baseURI.substr(2, baseURI.length - 2);
				}
				else
				{
					// This is a non-hierarchical URI like "mailto:bob@mail.com"
					_nonHierarchical = baseURI;
		
					if ((_valid = validateURI()) == false)
						initialize();  // Bad URI.  Clear it.
		
					// No more parsing to do for this case
					return isValid();
				}
			}
			else
			{
				// No scheme.  We will consider this a relative URI
				_scheme = "";
				_relative = true;
				_nonHierarchical = "";
			}
		
			// Ok, what we have left is everything after the <scheme>://
		
			// Now that we have stripped off any query and fragment parts, we
			// need to split the authority from the path
		
			if (isRelative())
			{
				// Don't bother looking for the authority.  It's a relative URI
				_authority = "";
				_port = "";
				_path = baseURI;
			}
			else
			{
				// Check for malformed UNC style file://///server/type/path/
				// By the time we get here, we have already trimmed the "file://"
				// so baseURI will be ///server/type/path.  If baseURI only
				// has one slash, we leave it alone because that is valid (that
				// is the case of "file:///path/to/file.txt" where there is no
				// server - implicit "localhost").
				if (baseURI.substr(0, 2) == "//")
				{
					// Trim all leading slashes
					while(baseURI.charAt(0) == "/")
						baseURI = baseURI.substr(1, baseURI.length - 1);
				}
		
				index = baseURI.search('/');
				if (index == -1)
				{
					// No path.  We must have passed something like "http://something.com"
					_authority = baseURI;
					_path = "";
				}
				else
				{
					_authority = baseURI.substr(0, index);
					_path = baseURI.substr(index, baseURI.length - index);
				}
		
				// Check to see if the URI has any username or password information.
				// For example:  ftp://username:password@server.com
				index = _authority.search('@');
				if (index != -1)
				{
					// We have a username and possibly a password
					_username = _authority.substr(0, index);
		
					// Remove the username/password from the authority
					_authority = _authority.substr(index + 1);  // Skip the '@'
		
					// Now check to see if the username also has a password
					index = _username.search(':');
					if (index != -1)
					{
						_password = _username.substring(index + 1, _username.length);
						_username = _username.substr(0, index);
					}
					else
						_password = "";
				}
				else
				{
					_username = "";
					_password = "";
				}
		
				// Lastly, check to see if the authorty has a port number.
				// This is parsed after the username/password to avoid conflicting
				// with the ':' in the 'username:password' if one exists.
				index = _authority.search(':');
				if (index != -1)
				{
					_port = _authority.substring(index + 1, _authority.length);  // skip the ':'
					_authority = _authority.substr(0, index);
				}
				else
				{
					_port = "";
				}
				
				// Lastly, normalize the authority.  Domain names
				// are case insensitive.
				_authority = _authority.toLowerCase();
			}
		
			if ((_valid = validateURI()) == false)
				initialize();  // Bad URI.  Clear it
		
			return isValid();
		}
		
		
		/********************************************************************
		 * Copy function.
		 */
		public function copyURI(uri:URI) : void
		{
			this._scheme = uri._scheme;
			this._authority = uri._authority;
			this._username = uri._username;
			this._password = uri._password;
			this._port = uri._port;
			this._path = uri._path;
			this._query = uri._query;
			this._fragment = uri._fragment;
			this._nonHierarchical = uri._nonHierarchical;
		
			this._valid = uri._valid;
			this._relative = uri._relative;
		}
		
		
		/**
		 * @private
		 * Checks if the given string only contains a-z or A-Z.
		 */
		protected function verifyAlpha(str:String) : Boolean
		{
			var pattern:RegExp = /[^a-z]/;
			var index:int;
			
			str = str.toLowerCase();
			index = str.search(pattern);
			
			if (index == -1)
				return true;
			else
				return false;
		}
		
		/**
		 * Is this a valid URI?
		 * 
		 * @return true if this object represents a valid URI, false
		 * otherwise.
		 */
		public function isValid() : Boolean
		{ 
			return this._valid;
		}
		
		
		/**
		 * Is this URI an absolute URI?  An absolute URI is a complete, fully
		 * qualified reference to a resource.  e.g. http://site.com/index.htm
		 * Non-hierarchical URI's are always absolute.
		 */
		public function isAbsolute() : Boolean
		{ 
			return !this._relative;
		}
		
		
		/**
		 * Is this URI a relative URI?  Relative URI's do not have a scheme
		 * and only contain a relative path with optional anchor and query
		 * parts.  e.g. "../reports/index.htm".  Non-hierarchical URI's
		 * will never be relative.
		 */
		public function isRelative() : Boolean
		{ 
			return this._relative;
		}
		
		
		/**
		 * Does this URI point to a resource that is a directory/folder?
		 * The URI specification dictates that any path that ends in a slash
		 * is a directory.  This is needed to be able to perform correct path
		 * logic when combining relative URI's with absolute URI's to
		 * obtain the correct absolute URI to a resource.
		 * 
		 * @see URI.chdir
		 * 
		 * @return true if this URI represents a directory resource, false
		 * if this URI represents a file resource.
		 */
		public function isDirectory() : Boolean
		{
			if (_path.length == 0)
				return false;
		
			return (_path.charAt(path.length - 1) == '/');
		}
		
		
		/**
		 * Is this URI a hierarchical URI? URI's can be  
		 */
		public function isHierarchical() : Boolean
		{ 
			return hierState;
		}
				
		
		/**
		 * The scheme of the URI.
		 */
		public function get scheme() : String
		{ 
			return URI.unescapeChars(_scheme);
		}
		public function set scheme(schemeStr:String) : void
		{
			// Normalize the scheme
			var normalized:String = schemeStr.toLowerCase();
			_scheme = URI.fastEscapeChars(normalized, URI.URIschemeExcludedBitmap);
		}
		
		
		/**
		 * The authority (host) of the URI.  Only valid for
		 * hierarchical URI's.  If the URI is relative, this will
		 * be an empty string. When setting this value, the string
		 * given is assumed to be unescaped.  When retrieving this
		 * value, the resulting string is unescaped.
		 */
		public function get authority() : String
		{ 
			return URI.unescapeChars(_authority);
		}
		public function set authority(authorityStr:String) : void
		{
			// Normalize the authority
			authorityStr = authorityStr.toLowerCase();
			
			_authority = URI.fastEscapeChars(authorityStr,
				URI.URIauthorityExcludedBitmap);
			
			// Only hierarchical URI's can have an authority, make
			// sure this URI is of the proper format.
			this.hierState = true;
		}
		
		
		/**
		 * The username of the URI.  Only valid for hierarchical
		 * URI's.  If the URI is relative, this will be an empty
		 * string.
		 * 
		 * <p>The URI specification allows for authentication
		 * credentials to be embedded in the URI as such:</p>
		 * 
		 * <p>http://user:passwd@host/path/to/file.htm</p>
		 * 
		 * <p>When setting this value, the string
		 * given is assumed to be unescaped.  When retrieving this
		 * value, the resulting string is unescaped.</p>
		 */
		public function get username() : String
		{
			return URI.unescapeChars(_username);
		}
		public function set username(usernameStr:String) : void
		{
			_username = URI.fastEscapeChars(usernameStr, URI.URIuserpassExcludedBitmap);
			
			// Only hierarchical URI's can have a username.
			this.hierState = true;
		}
		
		
		/**
		 * The password of the URI.  Similar to username.
		 * @see URI.username
		 */
		public function get password() : String
		{
			return URI.unescapeChars(_password);
		}
		public function set password(passwordStr:String) : void
		{
			_password = URI.fastEscapeChars(passwordStr,
				URI.URIuserpassExcludedBitmap);
			
			// Only hierarchical URI's can have a password.
			this.hierState = true;
		}
		
		
		/**
		 * The host port number.  Only valid for hierarchical URI's.  If
		 * the URI is relative, this will be an empty string. URI's can
		 * contain the port number of the remote host:
		 * 
		 * <p>http://site.com:8080/index.htm</p>
		 */
		public function get port() : String
		{ 
			return URI.unescapeChars(_port);
		}
		public function set port(portStr:String) : void
		{
			_port = URI.escapeChars(portStr);
			
			// Only hierarchical URI's can have a port.
			this.hierState = true;
		}
		
		
		/**
		 * The path portion of the URI.  Only valid for hierarchical
		 * URI's.  When setting this value, the string
		 * given is assumed to be unescaped.  When retrieving this
		 * value, the resulting string is unescaped.
		 * 
		 * <p>The path portion can be in one of two formats. 1) an absolute
		 * path, or 2) a relative path.  An absolute path starts with a
		 * slash ('/'), a relative path does not.</p>
		 * 
		 * <p>An absolute path may look like:</p>
		 * <listing>/full/path/to/my/file.htm</listing>
		 * 
		 * <p>A relative path may look like:</p>
		 * <listing>
		 * path/to/my/file.htm
		 * ../images/logo.gif
		 * ../../reports/index.htm
		 * </listing>
		 * 
		 * <p>Paths can be absolute or relative.  Note that this not the same as
		 * an absolute or relative URI.  An absolute URI can only have absolute
		 * paths.  For example:</p>
		 * 
		 * <listing>http:/site.com/path/to/file.htm</listing>
		 * 
		 * <p>This absolute URI has an absolute path of "/path/to/file.htm".</p>
		 * 
		 * <p>Relative URI's can have either absolute paths or relative paths.
		 * All of the following relative URI's are valid:</p>
		 * 
		 * <listing>
		 * /absolute/path/to/file.htm
		 * path/to/file.htm
		 * ../path/to/file.htm
		 * </listing>
		 */
		public function get path() : String
		{ 
			return URI.unescapeChars(_path);
		}
		public function set path(pathStr:String) : void
		{	
			this._path = URI.fastEscapeChars(pathStr, URI.URIpathExcludedBitmap);
		
			if (this._scheme == UNKNOWN_SCHEME)
			{
				// We set the path.  This is a valid URI now.
				this._scheme = "";
			}
		
			// Only hierarchical URI's can have a path.
			hierState = true;
		}
		
		
		/**
		 * The query (CGI) portion of the URI.  This part is valid for
		 * both hierarchical and non-hierarchical URI's.
		 * 
		 * <p>This accessor should only be used if a custom query syntax
		 * is used.  This URI class supports the common "param=value"
		 * style query syntax via the get/setQueryValue() and
		 * get/setQueryByMap() functions.  Those functions should be used
		 * instead if the common syntax is being used.
		 * 
		 * <p>The URI RFC does not specify any particular
		 * syntax for the query part of a URI.  It is intended to allow
		 * any format that can be agreed upon by the two communicating hosts.
		 * However, most systems have standardized on the typical CGI
		 * format:</p>
		 * 
		 * <listing>http://site.com/script.php?param1=value1&param2=value2</listing>
		 * 
		 * <p>This class has specific support for this query syntax</p>
		 * 
		 * <p>This common query format is an array of name/value
		 * pairs with its own syntax that is different from the overall URI
		 * syntax.  The query has its own escaping logic.  For a query part
		 * to be properly escaped and unescaped, it must be split into its
		 * component parts.  This accessor escapes/unescapes the entire query
		 * part without regard for it's component parts.  This has the
		 * possibliity of leaving the query string in an ambiguious state in
		 * regards to its syntax.  If the contents of the query part are
		 * important, it is recommended that get/setQueryValue() or
		 * get/setQueryByMap() are used instead.</p>
		 * 
		 * If a different query syntax is being used, a subclass of URI
		 * can be created to handle that specific syntax.
		 *  
		 * @see URI.getQueryValue, URI.getQueryByMap
		 */
		public function get query() : String
		{ 
			return URI.unescapeChars(_query);
		}
		public function set query(queryStr:String) : void
		{
			_query = URI.fastEscapeChars(queryStr, URI.URIqueryExcludedBitmap);
			
			// both hierarchical and non-hierarchical URI's can
			// have a query.  Do not set the hierState.
		}
		
		/**
		 * Accessor to the raw query data.  If you are using a custom query
		 * syntax, this accessor can be used to get and set the query part
		 * directly with no escaping/unescaping.  This should ONLY be used
		 * if your application logic is handling custom query logic and
		 * handling the proper escaping of the query part.
		 */
		public function get queryRaw() : String
		{
			return _query;
		}
		public function set queryRaw(queryStr:String) : void
		{
			_query = queryStr;
		}


		/**
		 * The fragment (anchor) portion of the URI.  This is valid for
		 * both hierarchical and non-hierarchical URI's.
		 */
		public function get fragment() : String
		{ 
			return URI.unescapeChars(_fragment);
		}
		public function set fragment(fragmentStr:String) : void
		{
			_fragment = URI.fastEscapeChars(fragmentStr, URIfragmentExcludedBitmap);

			// both hierarchical and non-hierarchical URI's can
			// have a fragment.  Do not set the hierState.
		}
		
		
		/**
		 * The non-hierarchical part of the URI.  For example, if
		 * this URI object represents "mailto:somebody@company.com",
		 * this will contain "somebody@company.com".  This is valid only
		 * for non-hierarchical URI's.  
		 */
		public function get nonHierarchical() : String
		{ 
			return URI.unescapeChars(_nonHierarchical);
		}
		public function set nonHierarchical(nonHier:String) : void
		{
			_nonHierarchical = URI.fastEscapeChars(nonHier, URInonHierexcludedBitmap);
			
			// This is a non-hierarchical URI.
			this.hierState = false;
		}
		
		
		/**
		 * Quick shorthand accessor to set the parts of this URI.
		 * The given parts are assumed to be in unescaped form.  If
		 * the URI is non-hierarchical (e.g. mailto:) you will need
		 * to call SetScheme() and SetNonHierarchical().
		 */
		public function setParts(schemeStr:String, authorityStr:String,
				portStr:String, pathStr:String, queryStr:String,
				fragmentStr:String) : void
		{
			this.scheme = schemeStr;
			this.authority = authorityStr;
			this.port = portStr;
			this.path = pathStr;
			this.query = queryStr;
			this.fragment = fragmentStr;

			hierState = true;
		}
		
		
		/**
		 * URI escapes the given character string.  This is similar in function
		 * to the global encodeURIComponent() function in ActionScript, but is
		 * slightly different in regards to which characters get escaped.  This
		 * escapes the characters specified in the URIbaselineExluded set (see class
		 * static members).  This is needed for this class to work properly.
		 * 
		 * <p>If a different set of characters need to be used for the escaping,
		 * you may use fastEscapeChars() and specify a custom URIEncodingBitmap
		 * that contains the characters your application needs escaped.</p>
		 * 
		 * <p>Never pass a full URI to this function.  A URI can only be properly
		 * escaped/unescaped when split into its component parts (see RFC 3986
		 * section 2.4).  This is due to the fact that the URI component separators
		 * could be characters that would normally need to be escaped.</p>
		 * 
		 * @param unescaped character string to be escaped.
		 * 
		 * @return	escaped character string
		 * 
		 * @see encodeURIComponent
		 * @see fastEscapeChars
		 */
		static public function escapeChars(unescaped:String) : String
		{
			// This uses the excluded set by default.
			return fastEscapeChars(unescaped, URI.URIbaselineExcludedBitmap);
		}
		

		/**
		 * Unescape any URI escaped characters in the given character
		 * string.
		 * 
		 * <p>Never pass a full URI to this function.  A URI can only be properly
		 * escaped/unescaped when split into its component parts (see RFC 3986
		 * section 2.4).  This is due to the fact that the URI component separators
		 * could be characters that would normally need to be escaped.</p>
		 * 
		 * @param escaped the escaped string to be unescaped.
		 * 
		 * @return	unescaped string.
		 */
		static public function unescapeChars(escaped:String /*, onlyHighASCII:Boolean = false*/) : String
		{
			// We can just use the default AS function.  It seems to
			// decode everything correctly
			var unescaped:String;
			unescaped = decodeURIComponent(escaped);
			return unescaped;
		}
		
		/**
		 * Performance focused function that escapes the given character
		 * string using the given URIEncodingBitmap as the rule for what
		 * characters need to be escaped.  This function is used by this
		 * class and can be used externally to this class to perform
		 * escaping on custom character sets.
		 * 
		 * <p>Never pass a full URI to this function.  A URI can only be properly
		 * escaped/unescaped when split into its component parts (see RFC 3986
		 * section 2.4).  This is due to the fact that the URI component separators
		 * could be characters that would normally need to be escaped.</p>
		 * 
		 * @param unescaped		the unescaped string to be escaped
		 * @param bitmap		the set of characters that need to be escaped
		 * 
		 * @return	the escaped string.
		 */
		static public function fastEscapeChars(unescaped:String, bitmap:URIEncodingBitmap) : String
		{
			var escaped:String = "";
			var c:String;
			var x:int, i:int;
			
			for (i = 0; i < unescaped.length; i++)
			{
				c = unescaped.charAt(i);
				
				x = bitmap.ShouldEscape(c);
				if (x)
				{
					c = x.toString(16);
					if (c.length == 1)
						c = "0" + c;
						
					c = "%" + c;
					c = c.toUpperCase();
				}
				
				escaped += c;
			}
			
			return escaped;
		}

		
		/**
		 * Is this URI of a particular scheme type?  For example,
		 * passing "http" to a URI object that represents the URI
		 * "http://site.com/" would return true.
		 * 
		 * @param scheme	scheme to check for
		 * 
		 * @return true if this URI object is of the given type, false
		 * otherwise.
		 */
		public function isOfType(scheme:String) : Boolean
		{
			// Schemes are never case sensitive.  Ignore case.
			scheme = scheme.toLowerCase();
			return (this._scheme == scheme);
		}


		/**
		 * Get the value for the specified named in the query part.  This
		 * assumes the query part of the URI is in the common
		 * "name1=value1&name2=value2" syntax.  Do not call this function
		 * if you are using a custom query syntax.
		 * 
		 * @param name	name of the query value to get.
		 * 
		 * @return the value of the query name, empty string if the
		 * query name does not exist.
		 */
		public function getQueryValue(name:String) : String
		{
			var map:Object;
			var item:String;
			var value:String;
		
			map = getQueryByMap();
		
			for (item in map)
			{
				if (item == name)
				{
					value = map[item];
					return value;
				}
			}
		
			// Didn't find the specified key
			return new String("");
		}
		

		/**
		 * Set the given value on the given query name.  If the given name
		 * does not exist, it will automatically add this name/value pair
		 * to the query.  If null is passed as the value, it will remove
		 * the given item from the query.
		 * 
		 * <p>This automatically escapes any characters that may conflict with
		 * the query syntax so that they are "safe" within the query.  The
		 * strings passed are assumed to be literal unescaped name and value.</p>
		 * 
		 * @param name	name of the query value to set
		 * @param value	value of the query item to set.  If null, this will
		 * force the removal of this item from the query.
		 */
		public function setQueryValue(name:String, value:String) : void
		{
			var map:Object;

			map = getQueryByMap();
		
			// If the key doesn't exist yet, this will create a new pair in
			// the map.  If it does exist, this will overwrite the previous
			// value, which is what we want.
			map[name] = value;
		
			setQueryByMap(map);
		}

		
		/**
		 * Get the query of the URI in an Object class that allows for easy
		 * access to the query data via Object accessors.  For example:
		 * 
		 * <listing>
		 * var query:Object = uri.getQueryByMap();
		 * var value:String = query["param"];    // get a value
		 * query["param2"] = "foo";   // set a new value
		 * </listing>
		 * 
		 * @return Object that contains the name/value pairs of the query.
		 * 
		 * @see #setQueryByMap
		 * @see #getQueryValue
		 * @see #setQueryValue
		 */
		public function getQueryByMap() : Object
		{
			var queryStr:String;
			var pair:String;
			var pairs:Array;
			var item:Array;
			var name:String, value:String;
			var index:int;
			var map:Object = new Object();
		
		
			// We need the raw query string, no unescaping.
			queryStr = this._query;
			
			pairs = queryStr.split('&');
			for each (pair in pairs)
			{
				if (pair.length == 0)
				  continue;
				  
				item = pair.split('=');
				
				if (item.length > 0)
					name = item[0];
				else
					continue;  // empty array
				
				if (item.length > 1)
					value = item[1];
				else
					value = "";
					
				name = queryPartUnescape(name);
				value = queryPartUnescape(value);
				
				map[name] = value;
			}
	
			return map;
		}
		

		/**
		 * Set the query part of this URI using the given object as the
		 * content source.  Any member of the object that has a value of
		 * null will not be in the resulting query.
		 * 
		 * @param map	object that contains the name/value pairs as
		 *    members of that object.
		 * 
		 * @see #getQueryByMap
		 * @see #getQueryValue
		 * @see #setQueryValue
		 */
		public function setQueryByMap(map:Object) : void
		{
			var item:String;
			var name:String, value:String;
			var queryStr:String = "";
			var tmpPair:String;
			var foo:String;
		
			for (item in map)
			{
				name = item;
				value = map[item];
		
				if (value == null)
					value = "";
				
				// Need to escape the name/value pair so that they
				// don't conflict with the query syntax (specifically
				// '=', '&', and <whitespace>).
				name = queryPartEscape(name);
				value = queryPartEscape(value);
				
				tmpPair = name;
				
				if (value.length > 0)
				{
					tmpPair += "=";
					tmpPair += value;
				}

				if (queryStr.length != 0)
					queryStr += '&';  // Add the separator
		
				queryStr += tmpPair;
			}
		
			// We don't want to escape.  We already escaped the
			// individual name/value pairs.  If we escaped the
			// query string again by assigning it to "query",
			// we would have double escaping.
			_query = queryStr;
		}
		
		
		/**
		 * Similar to Escape(), except this also escapes characters that
		 * would conflict with the name/value pair query syntax.  This is
		 * intended to be called on each individual "name" and "value"
		 * in the query making sure that nothing in the name or value
		 * strings contain characters that would conflict with the query
		 * syntax (e.g. '=' and '&').
		 * 
		 * @param unescaped		unescaped string that is to be escaped.
		 * 
		 * @return escaped string.
		 * 
		 * @see #queryUnescape
		 */
		static public function queryPartEscape(unescaped:String) : String
		{
			var escaped:String = unescaped;
			escaped = URI.fastEscapeChars(unescaped, URI.URIqueryPartExcludedBitmap);
			return escaped;
		}
		

		/**
		 * Unescape the individual name/value string pairs.
		 * 
		 * @param escaped	escaped string to be unescaped
		 * 
		 * @return unescaped string
		 * 
		 * @see #queryEscape
		 */
		static public function queryPartUnescape(escaped:String) : String
		{
			var unescaped:String = escaped;
			unescaped = unescapeChars(unescaped);
			return unescaped;
		}
		
		/**
		 * Output this URI as a string.  The resulting string is properly
		 * escaped and well formed for machine processing.
		 */
		public function toString() : String
		{
			if (this == null)
				return "";
			else
				return toStringInternal(false);
		}
		
		/**
		 * Output the URI as a string that is easily readable by a human.
		 * This outputs the URI with all escape sequences unescaped to
		 * their character representation.  This makes the URI easier for
		 * a human to read, but the URI could be completely invalid
		 * because some unescaped characters may now cause ambiguous parsing.
		 * This function should only be used if you want to display a URI to
		 * a user.  This function should never be used outside that specific
		 * case.
		 * 
		 * @return the URI in string format with all escape sequences
		 * unescaped.
		 * 
		 * @see #toString
		 */
		public function toDisplayString() : String
		{
			return toStringInternal(true);
		}
		
		
		/**
		 * @private
		 * 
		 * The guts of toString()
		 */
		protected function toStringInternal(forDisplay:Boolean) : String
		{
			var uri:String = "";
			var part:String = "";
		
			if (isHierarchical() == false)
			{
				// non-hierarchical URI
		
				uri += (forDisplay ? this.scheme : _scheme);
				uri += ":";
				uri += (forDisplay ? this.nonHierarchical : _nonHierarchical);
			}
			else
			{
				// Hierarchical URI
		
				if (isRelative() == false)
				{
					// If it is not a relative URI, then we want the scheme and
					// authority parts in the string.  If it is relative, we
					// do NOT want this stuff.
		
					if (_scheme.length != 0)
					{
						part = (forDisplay ? this.scheme : _scheme);
						uri += part + ":";
					}
		
					if (_authority.length != 0 || isOfType("file"))
					{
						uri += "//";
		
						// Add on any username/password associated with this
						// authority
						if (_username.length != 0)
						{
							part = (forDisplay ? this.username : _username);
							uri += part;
		
							if (_password.length != 0)
							{
								part = (forDisplay ? this.password : _password);
								uri += ":" + part;
							}
		
							uri += "@";
						}
		
						// add the authority
						part = (forDisplay ? this.authority : _authority);
						uri += part;
		
						// Tack on the port number, if any
						if (port.length != 0)
							uri += ":" + port;
					}
				}
		
				// Tack on the path
				part = (forDisplay ? this.path : _path);
				uri += part;
		
			} // end hierarchical part
		
			// Both non-hier and hierarchical have query and fragment parts
		
			// Add on the query and fragment parts
			if (_query.length != 0)
			{
				part = (forDisplay ? this.query : _query);
				uri += "?" + part;
			}
		
			if (fragment.length != 0)
			{
				part = (forDisplay ? this.fragment : _fragment);
				uri += "#" + part;
			}
		
			return uri;
		}
	
		/**
		 * Forcefully ensure that this URI is properly escaped.
		 * 
		 * <p>Sometimes URI's are constructed by hand using strings outside
		 * this class.  In those cases, it is unlikely the URI has been
		 * properly escaped.  This function forcefully escapes this URI
		 * by unescaping each part and then re-escaping it.  If the URI
		 * did not have any escaping, the first unescape will do nothing
		 * and then the re-escape will properly escape everything.  If
		 * the URI was already escaped, the unescape and re-escape will
		 * essentally be a no-op.  This provides a safe way to make sure
		 * a URI is in the proper escaped form.</p>
		 */
		public function forceEscape() : void
		{
			// The accessors for each of the members will unescape
			// and then re-escape as we get and assign them.
			
			// Handle the parts that are common for both hierarchical
			// and non-hierarchical URI's
			this.scheme = this.scheme;
			this.setQueryByMap(this.getQueryByMap());
			this.fragment = this.fragment;
			
			if (isHierarchical())
			{
				this.authority = this.authority;
				this.path = this.path;
				this.port = this.port;
				this.username = this.username;
				this.password = this.password;
			}
			else
			{
				this.nonHierarchical = this.nonHierarchical;
			}
		}
		
		
		/**
		 * Does this URI point to a resource of the given file type?
		 * Given a file extension (or just a file name, this will strip the
		 * extension), check to see if this URI points to a file of that
		 * type.
		 * 
		 * @param extension 	string that contains a file extension with or
		 * without a dot ("html" and ".html" are both valid), or a file
		 * name with an extension (e.g. "index.html").
		 * 
		 * @return true if this URI points to a resource with the same file
		 * file extension as the extension provided, false otherwise.
		 */
		public function isOfFileType(extension:String) : Boolean
		{
			var thisExtension:String;
			var index:int;
		
			index = extension.lastIndexOf(".");
			if (index != -1)
			{
				// Strip the extension
				extension = extension.substr(index + 1);
			}
			else
			{
				// The caller passed something without a dot in it.  We
				// will assume that it is just a plain extension (e.g. "html").
				// What they passed is exactly what we want
			}
		
			thisExtension = getExtension(true);
		
			if (thisExtension == "")
				return false;
		
			// Compare the extensions ignoring case
			if (compareStr(thisExtension, extension, false) == 0)
				return true;
			else
				return false;
		}
		
		
		/**
		 * Get the ".xyz" file extension from the filename in the URI.
		 * For example, if we have the following URI:
		 * 
		 * <listing>http://something.com/path/to/my/page.html?form=yes&name=bob#anchor</listing>
		 * 
		 * <p>This will return ".html".</p>
		 * 
		 * @param minusDot   If true, this will strip the dot from the extension.
		 * If true, the above example would have returned "html".
		 * 
		 * @return  the file extension
		 */
		public function getExtension(minusDot:Boolean = false) : String
		{
			var filename:String = getFilename();
			var extension:String;
			var index:int;
		
			if (filename == "")
				return String("");
		
			index = filename.lastIndexOf(".");
		
			// If it doesn't have an extension, or if it is a "hidden" file,
			// it doesn't have an extension.  Hidden files on unix start with
			// a dot (e.g. ".login").
			if (index == -1 || index == 0)
				return String("");
		
			extension = filename.substr(index);
		
			// If the caller does not want the dot, remove it.
			if (minusDot && extension.charAt(0) == ".")
				extension = extension.substr(1);
		
			return extension;
		}
		
		/**
		 * Quick function to retrieve the file name off the end of a URI.
		 * 
		 * <p>For example, if the URI is:</p>
		 * <listing>http://something.com/some/path/to/my/file.html</listing>
		 * <p>this function will return "file.html".</p>
		 * 
		 * @param minusExtension true if the file extension should be stripped
		 * 
		 * @return the file name.  If this URI is a directory, the return
		 * value will be empty string.
		 */
		public function getFilename(minusExtension:Boolean = false) : String
		{
			if (isDirectory())
				return String("");
		
			var pathStr:String = this.path;
			var filename:String;
			var index:int;
		
			// Find the last path separator.
			index = pathStr.lastIndexOf("/");
		
			if (index != -1)
				filename = pathStr.substr(index + 1);
			else
				filename = pathStr;
		
			if (minusExtension)
			{
				// The caller has requested that the extension be removed
				index = filename.lastIndexOf(".");
		
				if (index != -1)
					filename = filename.substr(0, index);
			}
		
			return filename;
		}
		
		
		/**
		 * @private
		 * Helper function to compare strings.
		 * 
		 * @return true if the two strings are identical, false otherwise.
		 */
		static protected function compareStr(str1:String, str2:String,
			sensitive:Boolean = true) : Boolean
		{
			if (sensitive == false)
			{
				str1 = str1.toLowerCase();
				str2 = str2.toLowerCase();
			}
			
			return (str1 == str2)
		}
		
		/**
		 * Based on the type of this URI (http, ftp, etc.) get
		 * the default port used for that protocol.  This is
		 * just intended to be a helper function for the most
		 * common cases.
		 */
		public function getDefaultPort() : String
		{
			if (_scheme == "http")
				return String("80");
			else if (_scheme == "ftp")
				return String("21");
			else if (_scheme == "file")
				return String("");
			else if (_scheme == "sftp")
				return String("22"); // ssh standard port
			else
			{
				// Don't know the port for this URI type
				return String("");
			}
		}
		
		/**
		 * @private
		 * 
		 * This resolves the given URI if the application has a
		 * resolver interface defined.  This function does not
		 * modify the passed in URI and returns a new URI.
		 */
		static protected function resolve(uri:URI) : URI
		{
			var copy:URI = new URI();
			copy.copyURI(uri);
			
			if (_resolver != null)
			{
				// A resolver class has been registered.  Call it.
				return _resolver.resolve(copy);
			}
			else
			{
				// No resolver.  Nothing to do, but we don't
				// want to reuse the one passed in.
				return copy;
			}
		}
		
		/**
		 * Accessor to set and get the resolver object used by all URI
		 * objects to dynamically resolve URI's before comparison.
		 */
		static public function set resolver(resolver:IURIResolver) : void
		{
			_resolver = resolver;
		}
		static public function get resolver() : IURIResolver
		{
			return _resolver;
		}
		
		/**
		 * Given another URI, return this URI object's relation to the one given.
		 * URI's can have 1 of 4 possible relationships.  They can be unrelated,
		 * equal, parent, or a child of the given URI.
		 * 
		 * @param uri	URI to compare this URI object to.
		 * @param caseSensitive  true if the URI comparison should be done
		 * taking case into account, false if the comparison should be
		 * performed case insensitive.
		 * 
		 * @return URI.NOT_RELATED, URI.CHILD, URI.PARENT, or URI.EQUAL
		 */
		public function getRelation(uri:URI, caseSensitive:Boolean = true) : int
		{
			// Give the app a chance to resolve these URI's before we compare them.
			var thisURI:URI = URI.resolve(this);
			var thatURI:URI = URI.resolve(uri);
			
			if (thisURI.isRelative() || thatURI.isRelative())
			{
				// You cannot compare relative URI's due to their lack of context.
				// You could have two relative URI's that look like:
				//		../../images/
				//		../../images/marketing/logo.gif
				// These may appear related, but you have no overall context
				// from which to make the comparison.  The first URI could be
				// from one site and the other URI could be from another site.
				return URI.NOT_RELATED;
			}
			else if (thisURI.isHierarchical() == false || thatURI.isHierarchical() == false)
			{
				// One or both of the URI's are non-hierarchical.
				if (((thisURI.isHierarchical() == false) && (thatURI.isHierarchical() == true)) ||
					((thisURI.isHierarchical() == true) && (thatURI.isHierarchical() == false)))
				{
					// XOR.  One is hierarchical and the other is
					// non-hierarchical.  They cannot be compared.
					return URI.NOT_RELATED;
				}
				else
				{
					// They are both non-hierarchical
					if (thisURI.scheme != thatURI.scheme)
						return URI.NOT_RELATED;
		
					if (thisURI.nonHierarchical != thatURI.nonHierarchical)
						return URI.NOT_RELATED;
						
					// The two non-hierarcical URI's are equal.
					return URI.EQUAL;
				}
			}
			
			// Ok, this URI and the one we are being compared to are both
			// absolute hierarchical URI's.
		
			if (thisURI.scheme != thatURI.scheme)
				return URI.NOT_RELATED;
		
			if (thisURI.authority != thatURI.authority)
				return URI.NOT_RELATED;
		
			var thisPort:String = thisURI.port;
			var thatPort:String = thatURI.port;
			
			// Different ports are considered completely different servers.
			if (thisPort == "")
				thisPort = thisURI.getDefaultPort();
			if (thatPort == "")
				thatPort = thatURI.getDefaultPort();
		
			// Check to see if the port is the default port.
			if (thisPort != thatPort)
				return URI.NOT_RELATED;
		
			if (compareStr(thisURI.path, thatURI.path, caseSensitive))
				return URI.EQUAL;
		
			// Special case check.  If we are here, the scheme, authority,
			// and port match, and it is not a relative path, but the
			// paths did not match.  There is a special case where we
			// could have:
			//		http://something.com/
			//		http://something.com
			// Technically, these are equal.  So lets, check for this case.
			var thisPath:String = thisURI.path;
			var thatPath:String = thatURI.path;
		
			if ( (thisPath == "/" || thatPath == "/") &&
				 (thisPath == "" || thatPath == "") )
			{
				// We hit the special case.  These two are equal.
				return URI.EQUAL;
			}
		
			// Ok, the paths do not match, but one path may be a parent/child
			// of the other.  For example, we may have:
			//		http://something.com/path/to/homepage/
			//		http://something.com/path/to/homepage/images/logo.gif
			// In this case, the first is a parent of the second (or the second
			// is a child of the first, depending on which you compare to the
			// other).  To make this comparison, we must split the path into
			// its component parts (split the string on the '/' path delimiter).
			// We then compare the 
			var thisParts:Array, thatParts:Array;
			var thisPart:String, thatPart:String;
			var i:int;
		
			thisParts = thisPath.split("/");
			thatParts = thatPath.split("/");
		
			if (thisParts.length > thatParts.length)
			{
				thatPart = thatParts[thatParts.length - 1];
				if (thatPart.length > 0)
				{
					// if the last part is not empty, the passed URI is
					// not a directory.  There is no way the passed URI
					// can be a parent.
					return URI.NOT_RELATED;
				}
				else
				{
					// Remove the empty trailing part
					thatParts.pop();
				}
				
				// This may be a child of the one passed in
				for (i = 0; i < thatParts.length; i++)
				{
					thisPart = thisParts[i];
					thatPart = thatParts[i];
						
					if (compareStr(thisPart, thatPart, caseSensitive) == false)
						return URI.NOT_RELATED;
				}
		
				return URI.CHILD;
			}
			else if (thisParts.length < thatParts.length)
			{
				thisPart = thisParts[thisParts.length - 1];
				if (thisPart.length > 0)
				{
					// if the last part is not empty, this URI is not a
					// directory.  There is no way this object can be
					// a parent.
					return URI.NOT_RELATED;
				}
				else
				{
					// Remove the empty trailing part
					thisParts.pop();
				}
				
				// This may be the parent of the one passed in
				for (i = 0; i < thisParts.length; i++)
				{
					thisPart = thisParts[i];
					thatPart = thatParts[i];
		
					if (compareStr(thisPart, thatPart, caseSensitive) == false)
						return URI.NOT_RELATED;
				}
				
				return URI.PARENT;
			}
			else
			{
				// Both URI's have the same number of path components, but
				// it failed the equivelence check above.  This means that
				// the two URI's are not related.
				return URI.NOT_RELATED;
			}
			
			// If we got here, the scheme and authority are the same,
			// but the paths pointed to two different locations that
			// were in different parts of the file system tree
			return URI.NOT_RELATED;
		}
		
		/**
		 * Given another URI, return the common parent between this one
		 * and the provided URI.
		 * 
		 * @param uri the other URI from which to find a common parent
		 * @para caseSensitive true if this operation should be done
		 * with case sensitive comparisons.
		 * 
		 * @return the parent URI if successful, null otherwise.
		 */
		public function getCommonParent(uri:URI, caseSensitive:Boolean = true) : URI
		{
			var thisURI:URI = URI.resolve(this);
			var thatURI:URI = URI.resolve(uri);
		
			if(!thisURI.isAbsolute() || !thatURI.isAbsolute() ||
				thisURI.isHierarchical() == false ||
				thatURI.isHierarchical() == false)
			{
				// Both URI's must be absolute hierarchical for this to
				// make sense.
				return null;
			}
			
			var relation:int = thisURI.getRelation(thatURI);
			if (relation == URI.NOT_RELATED)
			{
				// The given URI is not related to this one.  No
				// common parent.
				return null;
			}
		
			thisURI.chdir(".");
			thatURI.chdir(".");
			
			var strBefore:String, strAfter:String;
			do
			{
				relation = thisURI.getRelation(thatURI, caseSensitive);
				if(relation == URI.EQUAL || relation == URI.PARENT)
					break;
		
				// If strBefore and strAfter end up being the same,
				// we know we are at the root of the path because
				// chdir("..") is doing nothing.
				strBefore = thisURI.toString();
				thisURI.chdir("..");
				strAfter = thisURI.toString();
			}
			while(strBefore != strAfter);
		
			return thisURI;
		}
		
		
		/**
		 * This function is used to move around in a URI in a way similar
		 * to the 'cd' or 'chdir' commands on Unix.  These operations are
		 * completely string based, using the context of the URI to
		 * determine the position within the path.  The heuristics used
		 * to determine the action are based off Appendix C in RFC 2396.
		 * 
		 * <p>URI paths that end in '/' are considered paths that point to
		 * directories, while paths that do not end in '/' are files.  For
		 * example, if you execute chdir("d") on the following URI's:<br/>
		 *    1.  http://something.com/a/b/c/  (directory)<br/>
		 *    2.  http://something.com/a/b/c  (not directory)<br/>
		 * you will get:<br/>
		 *    1.  http://something.com/a/b/c/d<br/>
		 *    2.  http://something.com/a/b/d<br/></p>
		 * 
		 * <p>See RFC 2396, Appendix C for more info.</p>
		 * 
		 * @param reference	the URI or path to "cd" to.
		 * @param escape true if the passed reference string should be URI
		 * escaped before using it.
		 * 
		 * @return true if the chdir was successful, false otherwise.
		 */
		public function chdir(reference:String, escape:Boolean = false) : Boolean
		{
			var uriReference:URI;
			var ref:String = reference;
		
			if (escape)
				ref = URI.escapeChars(reference);
		
			if (ref == "")
			{
				// NOOP
				return true;
			}
			else if (ref.substr(0, 2) == "//")
			{
				// Special case.  This is an absolute URI but without the scheme.
				// Take the scheme from this URI and tack it on.  This is
				// intended to make working with chdir() a little more
				// tolerant.
				var final:String = this.scheme + ":" + ref;
				
				return constructURI(final);
			}
			else if (ref.charAt(0) == "?")
			{
				// A relative URI that is just a query part is essentially
				// a "./?query".  We tack on the "./" here to make the rest
				// of our logic work.
				ref = "./" + ref;
			}
		
			// Parse the reference passed in as a URI.  This way we
			// get any query and fragments parsed out as well.
			uriReference = new URI(ref);
		
			if (uriReference.isAbsolute() ||
				uriReference.isHierarchical() == false)
			{
				// If the URI given is a full URI, it replaces this one.
				copyURI(uriReference);
				return true;
			}
		
		
			var thisPath:String, thatPath:String;
			var thisParts:Array, thatParts:Array;
			var thisIsDir:Boolean = false, thatIsDir:Boolean = false;
			var thisIsAbs:Boolean = false, thatIsAbs:Boolean = false;
			var lastIsDotOperation:Boolean = false;
			var curDir:String;
			var i:int;
		
			thisPath = this.path;
			thatPath = uriReference.path;
		
			if (thisPath.length > 0)
				thisParts = thisPath.split("/");
			else
				thisParts = new Array();
				
			if (thatPath.length > 0)
				thatParts = thatPath.split("/");
			else
				thatParts = new Array();
			
			if (thisParts.length > 0 && thisParts[0] == "")
			{
				thisIsAbs = true;
				thisParts.shift(); // pop the first one off the array
			}
			if (thisParts.length > 0 && thisParts[thisParts.length - 1] == "")
			{
				thisIsDir = true;
				thisParts.pop();  // pop the last one off the array
			}
				
			if (thatParts.length > 0 && thatParts[0] == "")
			{
				thatIsAbs = true;
				thatParts.shift(); // pop the first one off the array
			}
			if (thatParts.length > 0 && thatParts[thatParts.length - 1] == "")
			{
				thatIsDir = true;
				thatParts.pop();  // pop the last one off the array
			}
				
			if (thatIsAbs)
			{
				// The reference is an absolute path (starts with a slash).
				// It replaces this path wholesale.
				this.path = uriReference.path;
		
				// And it inherits the query and fragment
				this.queryRaw = uriReference.queryRaw;
				this.fragment = uriReference.fragment;
		
				return true;
			}
			else if (thatParts.length == 0 && uriReference.query == "")
			{
				// The reference must have only been a fragment.  Fragments just
				// get appended to whatever the current path is.  We don't want
				// to overwrite any query that may already exist, so this case
				// only takes on the new fragment.
				this.fragment = uriReference.fragment;
				return true;
			}
			else if (thisIsDir == false && thisParts.length > 0)
			{
				// This path ends in a file.  It goes away no matter what.
				thisParts.pop();
			}
		
			// By default, this assumes the query and fragment of the reference
			this.queryRaw = uriReference.queryRaw;
			this.fragment = uriReference.fragment;
		
			// Append the parts of the path from the passed in reference
			// to this object's path.
			thisParts = thisParts.concat(thatParts);
					
			for(i = 0; i < thisParts.length; i++)
			{
				curDir = thisParts[i];
				lastIsDotOperation = false;
		
				if (curDir == ".")
				{
					thisParts.splice(i, 1);
					i = i - 1;  // account for removing this item
					lastIsDotOperation = true;
				}
				else if (curDir == "..")
				{
					if (i >= 1)
					{
						if (thisParts[i - 1] == "..")
						{
							// If the previous is a "..", we must have skipped
							// it due to this URI being relative.  We can't
							// collapse leading ".."s in a relative URI, so
							// do nothing.
						}
						else
						{
							thisParts.splice(i - 1, 2);
							i = i - 2;  // move back to account for the 2 we removed
						}
					}
					else
					{
						// This is the first thing in the path.
		
						if (isRelative())
						{
							// We can't collapse leading ".."s in a relative
							// path.  Do noting.
						}
						else
						{
							// This is an abnormal case.  We have dot-dotted up
							// past the base of our "file system".  This is a
							// case where we had a /path/like/this.htm and were
							// given a path to chdir to like this:
							// ../../../../../../mydir
							// Obviously, it has too many ".." and will take us
							// up beyond the top of the URI.  However, according
							// RFC 2396 Appendix C.2, we should try to handle
							// these abnormal cases appropriately.  In this case,
							// we will do what UNIX command lines do if you are
							// at the root (/) of the filesystem and execute:
							// # cd ../../../../../bin
							// Which will put you in /bin.  Essentially, the extra
							// ".."'s will just get eaten.
		
							thisParts.splice(i, 1);
							i = i - 1;  // account for the ".." we just removed
						}
					}
		
					lastIsDotOperation = true;
				}
			}
			
			var finalPath:String = "";
		
			// If the last thing in the path was a "." or "..", then this thing is a
			// directory.  If the last thing isn't a dot-op, then we don't want to 
			// blow away any information about the directory (hence the "|=" binary
			// assignment).
			thatIsDir = thatIsDir || lastIsDotOperation;
		
			// Reconstruct the path with the abs/dir info we have
			finalPath = joinPath(thisParts, thisIsAbs, thatIsDir);
		
			// Set the path (automatically escaping it)
			this.path = finalPath;
		
			return true;
		}
		
		/**
		 * @private
		 * Join an array of path parts back into a URI style path string.
		 * This is used by the various path logic functions to recombine
		 * a path.  This is different than the standard Array.join()
		 * function because we need to take into account the starting and
		 * ending path delimiters if this is an absolute path or a
		 * directory.
		 * 
		 * @param parts	the Array that contains strings of each path part.
		 * @param isAbs		true if the given path is absolute
		 * @param isDir		true if the given path is a directory
		 * 
		 * @return the combined path string.
		 */
		protected function joinPath(parts:Array, isAbs:Boolean, isDir:Boolean) : String
		{
			var pathStr:String = "";
			var i:int;
		
			for (i = 0; i < parts.length; i++)
			{
				if (pathStr.length > 0)
					pathStr += "/";
		
				pathStr += parts[i];
			}
		
			// If this path is a directory, tack on the directory delimiter,
			// but only if the path contains something.  Adding this to an
			// empty path would make it "/", which is an absolute path that
			// starts at the root.
			if (isDir && pathStr.length > 0)
				pathStr += "/";
		
			if (isAbs)
				pathStr = "/" + pathStr;
		
			return pathStr;
		}
		
		/**
		 * Given an absolute URI, make this relative URI absolute using
		 * the given URI as a base.  This URI instance must be relative
		 * and the base_uri must be absolute.
		 * 
		 * @param base_uri	URI to use as the base from which to make
		 * this relative URI into an absolute URI.
		 * 
		 * @return true if successful, false otherwise.
		 */
		public function makeAbsoluteURI(base_uri:URI) : Boolean
		{
			if (isAbsolute() || base_uri.isRelative())
			{
				// This URI needs to be relative, and the base needs to be
				// absolute otherwise we won't know what to do!
				return false;
			}
		
			// Make a copy of the base URI.  We don't want to modify
			// the passed URI.
			var base:URI = new URI();
			base.copyURI(base_uri);
		
			// ChDir on the base URI.  This will preserve any query
			// and fragment we have.
			if (base.chdir(toString()) == false)
				return false;
		
			// It worked, so copy the base into this one
			copyURI(base);
		
			return true;
		}
		
		
		/**
		 * Given a URI to use as a base from which this object should be
		 * relative to, convert this object into a relative URI.  For example,
		 * if you have:
		 * 
		 * <listing>
		 * var uri1:URI = new URI("http://something.com/path/to/some/file.html");
		 * var uri2:URI = new URI("http://something.com/path/to/another/file.html");
		 * 
		 * uri1.MakeRelativePath(uri2);</listing>
		 * 
		 * <p>uri1 will have a final value of "../some/file.html"</p>
		 * 
		 * <p>Note! This function is brute force.  If you have two URI's
		 * that are completely unrelated, this will still attempt to make
		 * the relative URI.  In that case, you will most likely get a
		 * relative path that looks something like:</p>
		 * 
		 * <p>../../../../../../some/path/to/my/file.html</p>
		 * 
		 * @param base_uri the URI from which to make this URI relative
		 * 
		 * @return true if successful, false if the base_uri and this URI
		 * are not related, of if error.
		 */
		public function makeRelativeURI(base_uri:URI, caseSensitive:Boolean = true) : Boolean
		{
			var base:URI = new URI();
			base.copyURI(base_uri);
			
			var thisParts:Array, thatParts:Array;
			var finalParts:Array = new Array();
			var thisPart:String, thatPart:String, finalPath:String;
			var pathStr:String = this.path;
			var queryStr:String = this.queryRaw;
			var fragmentStr:String = this.fragment;
			var i:int;
			var diff:Boolean = false;
			var isDir:Boolean = false;
		
			if (isRelative())
			{
				// We're already relative.
				return true;
			}
		
			if (base.isRelative())
			{
				// The base is relative.  A relative base doesn't make sense.
				return false;
			}
		
		
			if ( (isOfType(base_uri.scheme) == false) ||
				(this.authority != base_uri.authority) )
			{
				// The schemes and/or authorities are different.  We can't
				// make a relative path to something that is completely
				// unrelated.
				return false;
			}
		
			// Record the state of this URI
			isDir = isDirectory();
		
			// We are based of the directory of the given URI.  We need to
			// make sure the URI is pointing to a directory.  Changing
			// directory to "." will remove any file name if the base is
			// not a directory.
			base.chdir(".");
		
			thisParts = pathStr.split("/");
			thatParts = base.path.split("/");
			
			if (thisParts.length > 0 && thisParts[0] == "")
				thisParts.shift();
			
			if (thisParts.length > 0 && thisParts[thisParts.length - 1] == "")
			{
				isDir = true;
				thisParts.pop();
			}
			
			if (thatParts.length > 0 && thatParts[0] == "")
				thatParts.shift();
			if (thatParts.length > 0 && thatParts[thatParts.length - 1] == "")
				thatParts.pop();
		
		
			// Now that we have the paths split into an array of directories,
			// we can compare the two paths.  We start from the left of side
			// of the path and start comparing.  When we either run out of
			// directories (one path is longer than the other), or we find
			// a directory that is different, we stop.  The remaining parts
			// of each path is then used to determine the relative path.  For
			// example, lets say we have:
			//    path we want to make relative: /a/b/c/d/e.txt
			//    path to use as base for relative: /a/b/f/
			//
			// This loop will start at the left, and remove directories
			// until we get a mismatch or run off the end of one of them.
			// In this example, the result will be:
			//    c/d/e.txt
			//    f
			//
			// For every part left over in the base path, we prepend a ".."
			// to the relative to get the final path:
			//   ../c/d/e.txt
			while(thatParts.length > 0)
			{
				if (thisParts.length == 0)
				{
					// we matched all there is to match, we are done.
					// This is the case where "this" object is a parent
					// path of the given URI.  eg:
					//   this.path = /a/b/				(thisParts)
					//   base.path = /a/b/c/d/e/		(thatParts)
					break;
				}
		
				thisPart = thisParts[0];
				thatPart = thatParts[0];
		
				if (compareStr(thisPart, thatPart, caseSensitive))
				{
					thisParts.shift();
					thatParts.shift();
				}
				else
					break;
			}
		
			// If there are any path info left from the base URI, that means
			// **this** object is above the given URI in the file tree.  For
			// each part left over in the given URI, we need to move up one
			// directory to get where we are.
			var dotdot:String = "..";
			for (i = 0; i < thatParts.length; i++)
			{
				finalParts.push(dotdot);
			}
		
			// Append the parts of this URI to any dot-dot's we have
			finalParts = finalParts.concat(thisParts);
		
			// Join the parts back into a path
			finalPath = joinPath(finalParts, false /* not absolute */, isDir);
		
			if (finalPath.length == 0)
			{
				// The two URI's are exactly the same.  The proper relative
				// path is:
				finalPath = "./";
			}
		
			// Set the parts of the URI, preserving the original query and
			// fragment parts.
			setParts("", "", "", finalPath, queryStr, fragmentStr);
		
			return true;
		}
		
		/**
		 * Given a string, convert it to a URI.  The string could be a
		 * full URI that is improperly escaped, a malformed URI (e.g.
		 * missing a protocol like "www.something.com"), a relative URI,
		 * or any variation there of.
		 * 
		 * <p>The intention of this function is to take anything that a
		 * user might manually enter as a URI/URL and try to determine what
		 * they mean.  This function differs from the URI constructor in
		 * that it makes some assumptions to make it easy to import user
		 * entered URI data.</p>
		 * 
		 * <p>This function is intended to be a helper function.
		 * It is not all-knowning and will probably make mistakes
		 * when attempting to parse a string of unknown origin.  If
		 * your applicaiton is receiving input from the user, your
		 * application should already have a good idea what the user
		 * should  be entering, and your application should be
		 * pre-processing the user's input to make sure it is well formed
		 * before passing it to this function.</p>
		 * 
		 * <p>It is assumed that the string given to this function is
		 * something the user may have manually entered.  Given this,
		 * the URI string is probably unescaped or improperly escaped.
		 * This function will attempt to properly escape the URI by
		 * using forceEscape().  The result is that a toString() call
		 * on a URI that was created from unknownToURI() may not match
		 * the input string due to the difference in escaping.</p>
		 *
		 * @param unknown	a potental URI string that should be parsed
		 * and loaded into this object.
		 * @param defaultScheme	if it is determined that the passed string
		 * looks like a URI, but it is missing the scheme part, this
		 * string will be used as the missing scheme.
		 * 
		 * @return	true if the given string was successfully parsed into
		 * a valid URI object, false otherwise.
		 */
		public function unknownToURI(unknown:String, defaultScheme:String = "http") : Boolean
		{
			var temp:String;
			
			if (unknown.length == 0)
			{
				this.initialize();
				return false;
			}
			
			// Some users love the backslash key.  Fix it.
			unknown = unknown.replace(/\\/g, "/");
			
			// Check for any obviously missing scheme.
			if (unknown.length >= 2)
			{
				temp = unknown.substr(0, 2);
				if (temp == "//")
					unknown = defaultScheme + ":" + unknown;
			}
			
			if (unknown.length >= 3)
			{
				temp = unknown.substr(0, 3);
				if (temp == "://")
					unknown = defaultScheme + unknown;
			}

			// Try parsing it as a normal URI
			var uri:URI = new URI(unknown);
		
			if (uri.isHierarchical() == false)
			{
				if (uri.scheme == UNKNOWN_SCHEME)
				{
					this.initialize();
					return false;
				}
		
				// It's a non-hierarchical URI
				copyURI(uri);
				forceEscape();
				return true;
			}
			else if ((uri.scheme != UNKNOWN_SCHEME) &&
				(uri.scheme.length > 0))
			{
				if ( (uri.authority.length > 0) ||
					(uri.scheme == "file") )
				{
					// file://... URI
					copyURI(uri);
					forceEscape();  // ensure proper escaping
					return true;
				}
				else if (uri.authority.length == 0 && uri.path.length == 0)
				{
					// It's is an incomplete URI (eg "http://")
					
					setParts(uri.scheme, "", "", "", "", "");
					return false;
				}
			}
			else
			{
				// Possible relative URI.  We can only detect relative URI's
				// that start with "." or "..".  If it starts with something
				// else, the parsing is ambiguous.
				var path:String = uri.path;
		
				if (path == ".." || path == "." || 
					(path.length >= 3 && path.substr(0, 3) == "../") ||
					(path.length >= 2 && path.substr(0, 2) == "./") )
				{
					// This is a relative URI.
					copyURI(uri);
					forceEscape();
					return true;
				}
			}
		
			// Ok, it looks like we are just a normal URI missing the scheme.  Tack
			// on the scheme.
			uri = new URI(defaultScheme + "://" + unknown);
		
			// Check to see if we are good now
			if (uri.scheme.length > 0 && uri.authority.length > 0)
			{
				// It was just missing the scheme.
				copyURI(uri);
				forceEscape();  // Make sure we are properly encoded.
				return true;
			}
		
			// don't know what this is
			this.initialize();
			return false;
		}
		
	} // end URI class
} // end package