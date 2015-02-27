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
	FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  ALSO, THERE IS NO WARRANTY OF 
	NON-INFRINGEMENT, TITLE OR QUIET ENJOYMENT.  IN NO EVENT SHALL MACROMEDIA
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
	/**
	 * The URI class cannot know about DNS aliases, virtual hosts, or
	 * symbolic links that may be involved.  The application can provide
	 * an implementation of this interface to resolve the URI before the
	 * URI class makes any comparisons.  For example, a web host has
	 * two aliases:
	 * 
	 * <p><code>
	 *    http://www.site.com/
	 *    http://www.site.net/
	 * </code></p>
	 * 
	 * <p>The application can provide an implementation that automatically
	 * resolves site.net to site.com before URI compares two URI objects.
	 * Only the application can know and understand the context in which
	 * the URI's are being used.</p>
	 * 
	 * <p>Use the URI.resolver accessor to assign a custom resolver to
	 * the URI class.  Any resolver specified is global to all instances
	 * of URI.</p>
	 * 
	 * <p>URI will call this before performing URI comparisons in the
	 * URI.getRelation() and URI.getCommonParent() functions.
	 * 
	 * @see URI.getRelation
	 * @see URI.getCommonParent
	 * 
	 * @langversion ActionScript 3.0
	 * @playerversion Flash 9.0
	 */
	public interface IURIResolver
	{
		/**
		 * Implement this method to provide custom URI resolution for
		 * your application.
		 * 
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 9.0
		 */
		function resolve(uri:URI) : URI;
	}
}