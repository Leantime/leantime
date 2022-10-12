# Leantime DTS branch

The `dts` branch of the **Leantime&trade;** project implements views and canvas to allow implementing the *Design
Thinking for Strategy* process from the book with the same name [https://inov.at/dts-sn](https://inov.at/dts-sn).

## Major changes

- Added new canvas: *Strategy Brief*, *Risk Analysis*, *PESTE Analysis*, *Business Model Canvas* (3 version), *Porter's
  Strategy Questions*, *Competitive Positioning Canvas*, *Strategy Messaging*, *Insights*, *Ideation*
- Added functionality to generate PDF files from canvas
- Refactored canvas code and moved it into `src/library/canvas` allowing to create a new class by simply extending/including
  the code


## Version

Leantime DTS Branch 0.0.1


## Author

Dr. Claude Diderich (diderich@yahoo.com)


## Details of changes

### System related changes
- Added `Makefile` to minify/compile `js` and `css` files on a need to do basis
	  
### Canvas and kanbans specific for implementing *Design Thinking for Strategy*
		  
### Generating PDF files from  canvas
- Added `yetiforce/yetiforcepdf` library in composer
- Added print-ready `Roboto` and `RobotoCondensed` from (https://fonts.google.com/specimen/Roboto)


## To do items and open issues

### System related

## Printing canvcas and kanbans by generating PDF files


## Change log

## 0.0.1 - 2022-10-12
- Added `Makefile` to only compile/minify `js` and `css` files when changed
- Added `yetiforce/yetiforcepdf` library in composer
- Added print-ready `Roboto` and `RobotoCondensed` fonts in `public/fonts/roboto/`from (https://fonts.google.com/specimen/Roboto)
