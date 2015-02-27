**Table of Contents**

- [TimelineJS](#timelinejs)
	- [Document history with TimelineJS](#document-history-with-timelinejs)
	- [Add it to your site](#add-it-to-your-site)
		- [Using Inline (easiest)](#using-inline-easiest)
		- [Using a method (advanced)](#using-a-method-advanced)
	- [Config Options](#config-options)
		- [Language](#language)
		- [Start at End](#start-at-end)
		- [Start at Slide](#start-at-slide)
		- [Start Zoom Adjust](#start-zoom-adjust)
		- [Hash Bookmark](#hash-bookmark)
		- [Debug](#debug)
		- [Map Style Types](#map-style-types)
		- [Font Options](#font-options)
			- [Font Combination Preview:](#font-combination-preview)
	- [File Formats](#file-formats)
		- [JSON:](#json)
		- [JSONP :](#jsonp-)
		- [Google Docs:](#google-docs)
		- [Storify:](#storify)
	- [Media](#media)
	- [Best practices](#best-practices)
	- [License](#license)
	
# TimelineJS 
## Document history with TimelineJS

There are lots of timeline tools on the web but they are almost all either
hard on the eyes or hard to use. Create timelines that are at the same time
beautiful and intuitive for users

TimelineJS is great for pulling in media from different sources. Just throw in a
link from Twitter, YouTube, Flickr, Vimeo, Google Maps or SoundCloud and
TimelineJS will format it to fit perfectly. More media types will be supported
in the future.

Creating one is as easy as filling in a Google spreadsheet or as detailed as
JSON.
 
## Add it to your site
### Using Inline (*easiest*)
Place the embed code where you want the timeline to show in the `<body>` of your site. See [Config Options](#config-options) for a full list of what you can set in the config.

```html
	<div id="timeline-embed"></div>
	<script type="text/javascript">
	    var timeline_config = {
			width:				'100%',
			height:				'600',
			source:				'path_to_json/or_link_to_googlespreadsheet',
			embed_id:			'timeline-embed',				//OPTIONAL USE A DIFFERENT DIV ID FOR EMBED
			start_at_end: 		false,							//OPTIONAL START AT LATEST DATE
			start_at_slide:		'4',							//OPTIONAL START AT SPECIFIC SLIDE
			start_zoom_adjust:	'3',							//OPTIONAL TWEAK THE DEFAULT ZOOM LEVEL
			hash_bookmark:		true,							//OPTIONAL LOCATION BAR HASHES
			font:				'Bevan-PotanoSans',				//OPTIONAL FONT
			debug:				true,							//OPTIONAL DEBUG TO CONSOLE
			lang:				'fr',							//OPTIONAL LANGUAGE
			maptype:			'watercolor',					//OPTIONAL MAP STYLE
			css:				'path_to_css/timeline.css',		//OPTIONAL PATH TO CSS
			js:					'path_to_js/timeline-min.js'	//OPTIONAL PATH TO JS
		}
	</script>
	<script type="text/javascript" src="path_to_js/storyjs-embed.js"></script>
```
### Using a method (*advanced*)
You could also initialize a new timeline using the `createStoryJS` method after `storyjs-embed.js` has been loaded
```javascript
	createStoryJS({
		type:		'timeline',
		width:		'800',
		height:		'600',
		source:		'path_to_json/or_link_to_googlespreadsheet',
		embed_id:	'my-timeline'			// ID of the DIV you want to load the timeline into
	});
```

Here's a simple example:

```html
	<head>
		<!-- jQuery -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<!-- BEGIN TimelineJS -->
		<script type="text/javascript" src="path_to_js/storyjs-embed.js"></script>
		<script>
			$(document).ready(function() {
				createStoryJS({
					type:		'timeline',
					width:		'800',
					height:		'600',
					source:		'path_to_json/or_link_to_googlespreadsheet',
					embed_id:	'my-timeline'
				});
			});
		</script>
		<!-- END TimelineJS -->
	</head>
	<body>	
		<div id="my-timeline"></div>
	</body>
```

	
## Config Options
Here are some of the options you can set in the config.

### Source
`source` Should be either the path to the JSON resource to load, or a JavaScript
object corresponding to the Timeline model.

Here is an example using a data object:

```javascript

	var dataObject = {timeline: {headline: "Headline", type: ... }}
	createStoryJS({
		type:		'timeline',
		width:		'800',
		height:		'600',
		source:		dataObject,
		embed_id:	'my-timeline'
	});
```

If source is a string, we will try to automatically recognize resources that are
Twitter searches, Google Spreadsheets or Storify stories. Failing that, we assume
the source is either JSON or JSONP. If string matches on `.jsonp`, we will treat it
as JSONP, otherwise, we will append `?callback=onJSONP_Data`. See more details below.

### Language
`lang`
Localization
*default is `en` English*
Languages available:
* `af` *Afrikaans*
* `ar` *Arabic (beta)*
* `bg` *Bulgarian*
* `ca` *Catalan*
* `cz` *Czech*
* `da` *Danish*
* `de` *German / Deutsch*
* `el` *Greek*
* `en` *English*
* `en-24hr` *English using 24hr time*
* `es` *Spanish*
* `eu` *Basque/ Euskara*
* `fi` *Finnish*
* `fo` *Faroese*
* `fr` *French*
* `gl` *Galician*
* `hu` *Hungarian*
* `hy` *Armenian*
* `id` *Indonesian*
* `is` *Icelandic*
* `it` *Italian*
* `iw` *Hebrew (beta)*
* `ja` *Japanese*
* `ka` *Georgian*
* `ko` *Korean*
* `lv` *Latvian*
* `nl` *Dutch*
* `no` *Norwegian*
* `pl` *Polish*
* `pt-br` *Brazilian Portuguese*
* `pt` *Portuguese*
* `ru` *Russian*
* `sk` *Slovak*
* `sl` *Slovenian*
* `sr-cy` *Serbian (Cyrillic)*
* `sr` *Serbian (Latin)*
* `sv` *Swedish*
* `ta` *Tamil*
* `tl` *Tagalog*
* `tr` *Turkish*
* `zh-cn` *Chinese*
* `zh-tw` *Taiwanese*


Help us add more. Grab a copy of a language file and replace it with your language [Example language file](https://github.com/VeriteCo/StoryJS-Core/blob/master/Language/locale/en.js) and find your language's [two letter code here](http://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1)

###Start at End 
`start_at_end`
set to true to start the timeline on the last date.
*default is false*

###Start at Slide 
`start_at_slide`
You can tell TimelineJS to start at a specific slide number
*default is 0*

###Start Zoom Adjust
`start_zoom_adjust`
This will tweak the default zoom level. Equivilent to pressing the zoom in or zoom out button the specified number of times. Negative numbers zoom out.
*default is 0*

###Hash Bookmark 
`hash_bookmark`
set to true to allow bookmarking slides using the hash tag
*default is false*

###Debug 
`debug`
Will log events etc to the console.
*default is false*


###Map Style Types 
Due to recent changes to the Google Maps API, you need a [API Key](https://developers.google.com/places/documentation/#Authentication) in order to use custom map types.
`gmap_key:`
*required in order to use maptype*

`maptype:`
* [Stamen Maps ](http://maps.stamen.com)
	* `toner`
	* `toner-lines`
	* `toner-labels`
	* `watercolor`
	* `sterrain`
		
* Google Maps
	* `ROADMAP`
	* `TERRAIN`
	* `HYBRID`
	* `SATELLITE`

###Font Options 
`font:`
* `Arvo-PTSans`
* `Merriweather-NewsCycle`
* `PoiretOne-Molengo`
* `PTSerif-PTSans`
* `DroidSerif-DroidSans`
* `Lekton-Molengo`
* `NixieOne-Ledger`
* `AbrilFatface-Average`
* `PlayfairDisplay-Muli`
* `Rancho-Gudea`
* `Bevan-PotanoSans`
* `BreeSerif-OpenSans`
* `SansitaOne-Kameron`
* `Pacifico-Arimo`
* Or make your own

####Font Combination Preview:
![Font Combination Preview](http://timeline.verite.co/gfx/font-options.png)

## File Formats

### JSON:

JSON is the native data format for TimelineJS.

Remember, JSON is picky. A misplaced comma or quotation mark can
prevent the timeline from loading properly. 

Here is the full model:
```javascript

{
	"timeline":
	{
		"headline":"The Main Timeline Headline Goes here",
		"type":"default",
		"text":"<p>Intro body text goes here, some HTML is ok</p>",
		"asset": {
			"media":"http://yourdomain_or_socialmedialink_goes_here.jpg",
			"credit":"Credit Name Goes Here",
			"caption":"Caption text goes here"
		},
		"date": [
			{
				"startDate":"2011,12,10",
				"endDate":"2011,12,11",
				"headline":"Headline Goes Here",
				"text":"<p>Body text goes here, some HTML is OK</p>",
				"tag":"This is Optional",
				"classname":"optionaluniqueclassnamecanbeaddedhere",
				"asset": {
					"media":"http://twitter.com/ArjunaSoriano/status/164181156147900416",
					"thumbnail":"optional-32x32px.jpg",
					"credit":"Credit Name Goes Here",
					"caption":"Caption text goes here"
				}
			}
		],
		"era": [
			{
				"startDate":"2011,12,10",
				"endDate":"2011,12,11",
				"headline":"Headline Goes Here",
				"text":"<p>Body text goes here, some HTML is OK</p>",
				"tag":"This is Optional"
			}
			
		]
	}
}
```

### JSONP :

Timeline can use a variation of JSONP to allow you to easily load data across different domains.

To allow this to happen, the file must end with the extension `.jsonp`

Here is the full model:
```javascript
storyjs_jsonp_data = {
	"timeline":
	{
		"headline":"The Main Timeline Headline Goes here",
		"type":"default",
		"text":"<p>Intro body text goes here, some HTML is ok</p>",
		"asset": {
			"media":"http://yourdomain_or_socialmedialink_goes_here.jpg",
			"credit":"Credit Name Goes Here",
			"caption":"Caption text goes here"
		},
		"date": [
			{
				"startDate":"2011,12,10",
				"endDate":"2011,12,11",
				"headline":"Headline Goes Here",
				"text":"<p>Body text goes here, some HTML is OK</p>",
				"tag":"This is Optional",
				"classname":"optionaluniqueclassnamecanbeaddedhere",
				"asset": {
					"media":"http://twitter.com/ArjunaSoriano/status/164181156147900416",
					"thumbnail":"optional-32x32px.jpg",
					"credit":"Credit Name Goes Here",
					"caption":"Caption text goes here"
				}
			}
		],
		"era": [
			{
				"startDate":"2011,12,10",
				"endDate":"2011,12,11",
				"headline":"Headline Goes Here",
				"tag":"This is Optional"
			}
			
		],
		"chart": [
			{
				"startDate":"2011,12,10",
				"endDate":"2011,12,11",
				"headline":"Headline Goes Here",
				"value":"28"
			}
			
		]
		
	}
}
```

### Google Docs:

If you don’t want to mess with JSON, fire up Google Docs and build your
timeline in a spreadsheet. It’s as simple as dropping a date, text, and links
into the appropriate columns in TimelineJS’s template.

You can find the template here: [TimelineJS Google Spreadsheet Template](https://drive.google.com/previewtemplate?id=0AppSVxABhnltdEhzQjQ4MlpOaldjTmZLclQxQWFTOUE&mode=public&pli=1#)

There are only a couple things you need to know in order to create a timeline
using Google Docs:

  1. Make the spreadsheet public:   
	Google Docs are automatically set to private but the spreadsheet must be
	public.
	
	Click the blue “Share” button on the top right-hand corner. In the “Share
	settings” window, you’ll see the private setting of the spreadsheet: click
	“Change...”. In the Visibility options window, choose “Public on the Web” and
	save.

  2. Publish to the Web  
	Under the File menu, select “Publish to the Web.”
	
	In the next window, check the box next to “Automatically republish when
	changes are made.” Uncheck all other boxes. Click “start publishing.” This
	will give you the URL to embed in your HTML file.

  3. Copy/paste the Web URL into your TimelineJS HTML file  
	After you publish the spreadsheet, Google Docs will generate a link to the
	file. Copy the link for the Web Page option (as opposed to PDF, HTML, XLS,
	etc.), then paste it into the timeline’s HTML file (see [Add it to your site](#add-it-to-your-site) )


	
### Storify:

Support for Storify is still in it's early stages. It works though. Just paste a link to the storify story as the source.

## Media

Included in the zip file is a kitchen sink example. This timeline shows how to
incorporate the different media types from different services like Twitter,
YouTube, Flickr, Instagram, TwitPic, Wikipedia, Dailymotion, SoundCloud and Vimeo.

Just copy and paste the address of the media from the browser bar
into the media parameter. TimelineJS will auto-magically pull in the media via their api and
format it.

## Best practices

Tips and tricks to best utilize TimelineJS

  1. Keep it light - don’t get bogged down by text or other elements
  2. Pick stories that have a strong chronological narrative. It does not work well for stories that need to jump around in the timeline.
  3. Include events that build up to major occurrences, not just the major events.
  4. Don't overwhelm the user. A timeline with hundreds of events is probably not the best use of the format.
  
## License
This Source Code Form is subject to the terms of the Mozilla Public
License, v. 2.0. If a copy of the MPL was not distributed with this
file, You can obtain one at http://mozilla.org/MPL/2.0/.


