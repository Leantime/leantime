# FlexSlider 2
http://www.woothemes.com/flexslider/ - Copyright (c) 2012 WooThemes

Documentation guides for properties and theming are coming soon. Shortly thereafter, the download builder will be released, where you can create minified FlexSlider scripts that contain only the properties you need. It's a brave new world.

## General Notes
FlexSlider is no longer licensed under the MIT license. FlexSlider now shares the common licensed used for all WooThemes themes, GPLv2.

In an effort to move the plugin forward, support for jQuery 1.3.2 has been dropped. The plugin now requires jQuery 1.4.2+. If you don't have access to the later versions of jQuery, [FlexSlider 1.8](https://github.com/woothemes/FlexSlider/tree/flexslider1) should be a perfectly suitable substitute for your needs!

Your old styles and properties *might not work out of the box*. Some property names have been changed, noted below, as well as namespacing prefixes being applied to all elements. This means that `.flex-direction-nav .next` is now `.flex-direction-nav .flex-next` by default. The namespacing property is exposed, free for you to change.

## Updates
No more overflow hidden woes! The plugin now generates a viewport element to handle the tedious task of working around overflow hidden. Yay!

The slider element is now accessible outside of the callback API via the jQuery .data() method. Example use: `$('#slider').data('flexslider')`

Helper strings have been added for performing actions quickly on FlexSlider elements. Example uses:

- `$('#slider').flexslider("play")  //Play slideshow`
- `$('#slider').flexslider("pause") //Pause slideshow`
- `$('#slider').flexslider("next")  //Go to next slide`
- `$('#slider').flexslider("prev")  //Go to previous slide`
- `$('#slider').flexslider(3)       //Go fourth slide`

Two new methods are available for adding/removing slides, `slider.addSlide()` and `slider.removeSlide()`. More details about this coming soon.

- `slider.addSlide(obj, pos)` accepts two parameters, a string/jQuery object and an index.
- `slider.removeSlide(obj)` accepts one parameter, either an object to be removed, or an index.

## Examples

- [Basic Slider](http://flexslider.woothemes.com/)
- [Slider w/thumbnail controlNav pattern](http://flexslider.woothemes.com/thumbnail-controlnav.html)
- [Slider w/thumbnail slider](http://flexslider.woothemes.com/thumbnail-slider.html)
- [Basic Carousel](http://flexslider.woothemes.com/basic-carousel.html)
- [Carousel with min and max ranges](http://flexslider.woothemes.com/carousel-min-max.html)
- [Video with Vimeo API](http://flexslider.woothemes.com/video.html)


## Properties

### namespace: *{new}*
`namespace` controls the prefixes attached to elements created by the plugin. In previous releases, only certain elements were tagged with a prefix class, which was causing class generalization issues for some users. FlexSlider now prefixes all generated elements with the appropriate namespace.

*Hint: `namespace` can be an empty string.*

### selector: *{new}*
The markup structure for FlexSlider has been limited to a "ul.slide li" pattern in previous versions of FlexSlider; no longer. You can now take full control of the markup structure used for your FlexSlider. The `selector` pattern "{container} > {slide}" is mandatory, allowing the plugin to predictably interpret the selector property. Omitting the ">" from the selector is not suggested, but is possible if your markup doesn't follow the immediate descendant pattern.

*Examples: "section > article", ".slides > .slide", "#hero .slide"*

### easing: *{new}*
`easing` allows support for jQuery easing! Default options provided by jQuery are "swing" and "linear," but more can be used by included the jQuery Easing plugin. *If you chose a non-existent easing method, the slider will break.*

*Note: You need to set `useCSS: false` to force transitions in browsers that support translate3d.*
*Optional: [jQuery Easing Plugin](http://gsgd.co.uk/sandbox/jquery/easing/)*

### direction: *{changed}*
Previously called "slideDirection" in v1.8 and below.

### reverse: *{new}*
`reverse` will reverse the animation direction of the slider. Meaning, horizontal sliders can move from right to left, and vertical sliders can move bottom to top.

### smoothHeight: *{new}*
`smoothHeight` allows for smooth height transitions between slides. This property currently works for the fade and horizontal slide animation. The property has no effect on horizontal sliding carousels, however.

### startAt: *{changed}*
Previously called "slideToStart" in v1.8 and below.

### animationSpeed: *{changed}*
Previously called "animationDuration" in v1.8 and below.

### initDelay: *{new}*
`initDelay` will delay the initial slideshow of a slider, given in milliseconds. The slider will still initialize, generating controls and displaying the first image, but the slideshow will wait until the `initDelay` time has completed before starting the slideshow.

### useCSS: *{new}*
`useCSS` allow users to override using CSS3 for animation. Translate3d still has numerous bugs that can crop up and wreak havoc, so this is a great property to play with if you are experiencing unexplainable issues in Webkit browsers.

*Hint: Use conditionals to enable/disable the use of CSS3 on desktops and mobile devices. Mobile devices, in my experience, do not share many of the translate3d bugs seen on desktop browsers.*

### touch: *{new}*
`touch` allows users to exclude touch swipe functionality from their sliders.

### keyboard: *{changed}*
Previously called "keyboardNav" in v1.8 and below.

### multipleKeyboard *{new}*
`multipleKeyboard` allows users to override the default plugin keyboard behavior, enabling keyboard control of more than one slider on the page. This means that all visible sliders will animate, at the same time, via keyboard input.

*Hint: You can use `multipleKeyboard` to allow keyboard navigation on pages where multiple sliders are present, but only one is visible.*

### mousewheel: *{updated}*
`mousewheel` now requires the jQuery Mousewheel plugin. There are a few reasons for this, but primarily because there is no need for FlexSlider itself to reinvent the awkward complexity of mousewheel interactivity that is handled perfectly by the Mousewheel plugin.

*Required: [jQuery Mousewheel Plugin](https://github.com/brandonaaron/jquery-mousewheel)*

### controlsContainer: *{updated}*
`controlsContainer` is one of the more painstaking, potentially confusing properties within FlexSlider. First, the property is no longer required to workaround `overflow: hidden` on slide animation. Second, the property now accepts a **jQuery object**, giving you precise control over the object you want. The plugin no longer attempts to guess what element you are selecting.

### sync: *{new}*
`sync` is a new property that will allow other slider(s) to hook into the current slider via a given selector. The selector should describe an object that has already been initialized as a FlexSlider. Right now, `sync` will synchronize animation, play, and pause behaviors. More behaviors can be added in the future as the property matures.

*[Example of sync being used](http://flex.madebymufffin.com/examples/basic-carousel.html)*

### asNavFor: *{new}*
Description to be added.

### itemWidth: *{new}*
`itemWidth` is the primary property for the new carousel options. Without this property, your slider is not considered a carousel. To use `itemWidth`, give an integer value of the width of your individual slides. This should include borders and paddings applied to your slides; a total width measurement.

### itemMargin: *{new}*
`itemMargin` describes the gutter between the slide elements. If each slide has a margin-left of 10px, your itemMargin value would be 10. If elements have margin: 0 10px, your itemMargin would be 20.

### minItems: *{new}*
`minItems` describes the minimum number of slide elements that should be visible in the carousel. When the slider reaches the minimum item count, the slides will resize fluidly with the slider.

### maxItems: *{new}*
`maxItems` describes the maximum number of slide elements that should be visible in the carousel. When the slider reaches the maximum item count, the slides will resize fluidly with the sider.

### move: *{new}*
`move` determines how many slides should be animated within the carousel. When left at 0, the slider will animate the number of visible slides. If any value greater than 0 is given, the slider will animate that number of slides in the carousel on each animation interval.

*Hint: The move property will be ignored if the value is higher than the number of visible slides, which can be utilized in responsive design.*

### added: *{new}*
`added()` is a new callback event fired in the new slider.addSlide() function.

### removed: *{new}*
`removed()` is a new callback event fired in the new slider.removeSlide() function.