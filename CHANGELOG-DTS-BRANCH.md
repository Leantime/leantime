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

Leantime DTS Branch 0.0.9


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
- New: Library for extendable classes and includable templates for `canvas` and `pdf` as `src/library`
- New: Engine to generate PDF files from templates (`public.pdf`)
- New: Repositoriy `dts` for creating default templates and milestones recommended by DTS process (section 5.1.)

# 0.0.4 - 2022-10-13
- Update: Prepared `api` and `helper` files for new templates
- New: Added *Strategy Brief* canvas and pdf functionality

# 0.0.5 - 2022-10-13
- New: Added *Business Model Canvas* canvas and pdf functionality

# 0.0.6 - 2022-10-13
- New: Added *Porter's Five Strategic Questions* and *Strategy Message* canvas and pdf functionality

# 0.0.7 - 2022-10-15
- Update: Make use of project type configurable (`$config->enableProjectType`)
- New: Added `relates` field to database table `zp_canvas_items` to allow relating an element
- New: Added `relatesLabels` to canvas repository class
- Updated: Moved library of extendable classes from `src/library` to `src/domain/canvas` and sib-directories
- Updated: Made canvas and label definition variables in canvas repository class ony accessible through functions

# 0.0.8 - 2022-10-15
- Added: Template to be used for creating new canvas as `src/domain/canvas/NEwcanvas/...`
- Update: Only the *Strategy Brief* canvas has been updated for the new structure

# 0.0.9 - 2022-01-15
- New: Added configurable menu structure in `repositories/menu` based on menu type selectable on a project by project
  basis
  
