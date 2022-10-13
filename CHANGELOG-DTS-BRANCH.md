# Leantime DTS branch

The `dts` branch of the **Leantime&trade;** project implements views and canvas to allow implementing the *Design
Thinking for Strategy* process from the book with the same name [https://inov.at/dts-sn](https://inov.at/dts-sn).


## Major changes

- Added new canvas: *SWOT Analysis*, *Strategy Brief*, *Risk Analysis*, *Environment Analysis*, *Business Model Canvas*
  (3 version), *Porter's Strategy Questions*, *Competitive Positioning Canvas*, *Strategy Messaging*, *Insights*,
  *Ideation*, and *SWOT Analysis*
- Added functionality to generate PDF files from canvas
- Refactored canvas code and moved it into `src/library/canvas` allowing to create a new class by simply extending/including
  the code


## Version

Leantime DTS Branch 0.0.4


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
- New: Documented changes in `CHANGELOG_DTS_BRANCH.md` (commits are numbered with version)
- New: Added `Makefile` to only compile/minify `js` and `css` files when changed
- New: Added `yetiforce/yetiforcepdf` library in composer
- New: Added print-ready `Roboto` and `RobotoCondensed` fonts in `public/fonts/roboto/`from (https://fonts.google.com/specimen/Roboto)

## 0.0.2 - 2022-10-12
- New: Added column `type MEDIUMTEXT` to table `zp_projects`. Column allows storing which process is associated with a
  given project. Supported values are `NULL` (Generic), `lean` (Lean), and `dts` (DTS).
- New: Added functionality to associated a process type to a project when creating a new or modifying an existing
  project. Currently *Generic*, *Lean Startup*, and *Design Thinking for Strategy* are supported.

# 0.0.3 - 2022-10-12
- Update: Update menu structure, adding new canvas and boards
- Add: Library for extendable classes and includable templates for `canvas` and `pdf` as `src/library`
- Add: Engine to generate PDF files from templates (`public.pdf`)
- Add: Repositoriy `dts` for creating default templates and milestones recommended by DTS process (section 5.1.)

# 0.0.4 - 2022-10-13
- Update: Prepared `api` and `helper` files for new templates
- Add: Added *Strategy Brief* canvas and pdf functionality
