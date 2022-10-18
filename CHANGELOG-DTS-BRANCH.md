# Leantime DTS branch

The `dts` branch of the **Leantime&trade;** project implements views and canvas to allow implementing the *Design
Thinking for Strategy* process from the book with the same name [https://inov.at/dts-sn](https://inov.at/dts-sn).


## Major changes

- New canvas:
  - *SWOT Analysis*
  - *Empathy Map*
  - *Strategy Brief* [done]
  - *Risk Analysis* [done]
  - *Environment Analysis*
  - *Lightweight Business Model*, *Osterwalter's Business Model*, and *Detailed Business Model*
  - *Porter's Strategy Questions*
  - *Competitive Positioning Canvas*
  - *Strategy Messaging*
  - *Observe / Learn - Insights* [done]
- Revised canvas: *Lean Canvas* (full version only)
- Generate PDF files from any canvas
- Support for easily configurable muli-layer menu structure (support for project-specific menu structures can be enabled
  via configuration)
- Refactored generic canvas code in `src/domain/canvas` allowing to create a new canvas by simply extending/including
  code


## Version

Leantime DTS Branch 0.0.15


## Author

Dr. Claude Diderich (diderich@yahoo.com)


## Basic principles underlying design decision
1. The UX/UI design is key for software user buy-in. A poor layout will never make up for great content!
2. The definition and layout of canvases is part of the configuration of the system and not part of the day-to-day
   activity of using the system
3. A well-defined and supported process is preferred over an overwhelming choice of options


## Details of changes

### New canvas implementation
- Refactored canvas code into a generic library of extendable classes and includable templates. Generic code is in
  `src/domain/canvas` (with a template for a new canvas in `src/domain/canvas/NEWcanvas`)
- Canvas details are defined in `domain/repository/XXcanvas`, rather than in templates (allowing specification to be
  used for screen layout and PDF generation)
- Added optional second drop-down allowing to relate an element/box to a specific concept (e.g., relating a strength in
  a SWOT analysis to the firms' Capabilities)
- Added separate access to comments from element/box
- Added option to clone/copy existing canvas
- Added selectors that allow to show sub-sets of elements/boxes in a canvas, based on drop-down values (e.g., only
  showing elements/boxes that have been validated)
- Added icons to element/box titles and removed option to change titles by the user
  
### Generating PDF files from  canvas
- Added `yetiforce/yetiforcepdf` library in composer for rendering PDF files (available via *MIT License*)
- Added print-ready `Roboto` and `RobotoCondensed` from (https://fonts.google.com/specimen/Roboto) (avaialable via
  *Apache2* license)
- Added functionality to generate PDF reports from templates (visual canvas report containing summary information and
  detailed list report). Reports can be easily customized via coding on a canvas level 

### Menu structure
- Menu structure has been separated from menu rending/layout. Menu structure is defined in `domain/repositories/menu`.
- Menu structure supports two layers, second layer can be shown/hidden by the user
- Support for project specific menu structures added. It can be enabled through configuration (`$config->enablerMenuType`)

### System related changes
- Added `Makefile` to minify/compile `js` and `css` files on a need to do basis
  

## Open issues
- Due to the lack of support for lists (`<ul>`, ..) in *YetiForcePDF*, rendering lists is limited
- Complex tables may render poorly due to *YetiForcePDF* incorrecly handing some table borders
- If an image cannot be accessed during PDF report generating, the resulting PDF report is compromised
- New language tags have not been translated and are only available in *en-US*
- Improve `Makefile` to support re-generating only those `js` and `css` files where the source has changed (currently
  all-or-nothing approach implemented)


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

# 0.0.9 - 2022-10-15
- New: Added configurable menu structure in `repositories/menu` based on menu type selectable on a project by project
  basis
  
# 0.0.10 - 2022-10-16
- Updated: Added sub-menus to menu structure and allow them be toggled. Added access control to menu structure.
- Bug: Corrected modal related bug in `xxCanvasController`
- Check: Checked `canvas` code using PHPMD and removed unused variables
- Add: Minor adjustments for adding additional templates

# 0.0.11 - 2022-10-16
- Add: *Observe / Learn - Insights* canvas added

# 0.0.12 - 2022-10-16
- Add: *Risk Analysis* canvas added

# 0.0.13 - 2022-10-17
- Add: Make submenus open/close persistent

# 0.0.14 - 2022-10-17
- Add: *Empathy Map*
- Add: *SWOT Analysis*
- Update: Make create/edit/delete board menu option in `insights` consistent with `canvas` boards (but missing `clone`
  and `print`)

# 0.0.15 2022-10-18
- Add: *Environmental Analysis* canvas
- Update: Monor improvements of genric `domain/canvas`
