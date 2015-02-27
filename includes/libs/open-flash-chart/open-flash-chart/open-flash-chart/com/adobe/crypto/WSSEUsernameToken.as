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

package com.adobe.crypto
{
	import mx.formatters.DateFormatter;
	import mx.utils.Base64Encoder;
	
	/**
	 * Web Services Security Username Token
	 *
	 * Implementation based on algorithm description at 
	 * http://www.oasis-open.org/committees/wss/documents/WSS-Username-02-0223-merged.pdf
	 */
	public class WSSEUsernameToken
	{
		/**
		 * Generates a WSSE Username Token.
		 *
		 * @param username The username
		 * @param password The password
		 * @param nonce A cryptographically random nonce (if null, the nonce
		 * will be generated)
		 * @param timestamp The time at which the token is generated (if null,
		 * the time will be set to the moment of execution)
		 * @return The generated token
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 9.0
		 * @tiptext
		 */
		public static function getUsernameToken(username:String, password:String, nonce:String=null, timestamp:Date=null):String
		{
			if (nonce == null)
			{
				nonce = generateNonce();
			}
			nonce = base64Encode(nonce);
		
			var created:String = generateTimestamp(timestamp);
		
			var password64:String = getBase64Digest(nonce,
				created,
				password);
		
			var token:String = new String("UsernameToken Username=\"");
			token += username + "\", " +
					 "PasswordDigest=\"" + password64 + "\", " +
					 "Nonce=\"" + nonce + "\", " +
					 "Created=\"" + created + "\"";
			return token;
		}
		
		private static function generateNonce():String
		{
			// Math.random returns a Number between 0 and 1.  We don't want our
			// nonce to contain invalid characters (e.g. the period) so we
			// strip them out before returning the result.
			var s:String =  Math.random().toString();
			return s.replace(".", "");
		}
		
		internal static function base64Encode(s:String):String
		{
			var encoder:Base64Encoder = new Base64Encoder();
			encoder.encode(s);
			return encoder.flush();
		}
		
		internal static function generateTimestamp(timestamp:Date):String
		{
			if (timestamp == null)
			{
				timestamp = new Date();
			}
			var dateFormatter:DateFormatter = new DateFormatter();
			dateFormatter.formatString = "YYYY-MM-DDTJJ:NN:SS"
			return dateFormatter.format(timestamp) + "Z";
		}
		
		internal static function getBase64Digest(nonce:String, created:String, password:String):String
		{
			return SHA1.hashToBase64(nonce + created + password);
		}
	}
}