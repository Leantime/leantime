/*
 * File:        ColumnFilterWidgets.js
 * Version:     1.0.3
 * Description: Controls for filtering based on unique column values in DataTables
 * Author:      Dylan Kuhn (www.cyberhobo.net)
 * Language:    Javascript
 * License:     GPL v2 or BSD 3 point style
 * Contact:     cyberhobo@cyberhobo.net
 * 
 * Copyright 2011 Dylan Kuhn (except fnGetColumnData by Benedikt Forchhammer), all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD style license, available at:
 *   http://datatables.net/license_gpl2
 *   http://datatables.net/license_bsd
 */

(function($) {
	/*
	 * Function: fnGetColumnData
	 * Purpose:  Return an array of table values from a particular column.
	 * Returns:  array string: 1d data array 
	 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
	 *           int:iColumn - the id of the column to extract the data from
	 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
	 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
	 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
	 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
	 */
	
	$.fn.dataTableExt.oApi.fnResetAllFilters = function (oSettings, bDraw/*default true*/) {
		for(iCol = 0; iCol < oSettings.aoPreSearchCols.length; iCol++) {
		oSettings.aoPreSearchCols[ iCol ].sSearch = '';
		}
		oSettings.oPreviousSearch.sSearch = '';
		if(typeof bDraw === 'undefined') bDraw = true;
		if(bDraw) this.fnDraw();
		};


	$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
		// check that we have a column id
		if ( typeof iColumn == "undefined" ) return [];
		
		// by default we only wany unique data
		if ( typeof bUnique == "undefined" ) bUnique = true;
		
		// by default we do want to only look at filtered data
		if ( typeof bFiltered == "undefined" ) bFiltered = true;
		
		// by default we do not wany to include empty values
		if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
		
		// list of rows which we're going to loop through
		var aiRows;
		
		// use only filtered rows
		if (bFiltered == true) aiRows = oSettings.aiDisplay; 
		// use all rows
		else aiRows = oSettings.aiDisplayMaster; // all row numbers
	
		// set up data array	
		var asResultData = [];
		
		for (var i=0,c=aiRows.length; i<c; i++) {
			var iRow = aiRows[i];
			var sValue = this.fnGetData(iRow, iColumn);
			
			// ignore empty values?
			if (bIgnoreEmpty == true && sValue.length == 0)
	
			// ignore unique values?
			else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1)
			
			// else push the value onto the result data array
			else asResultData.push(sValue);
		}
		
		return asResultData;
	};

	/**
	* Add backslashes to regular expression symbols in a string.
	* 
	* Allows a regular expression to be constructed to search for 
	* variable text.
	* 
	* @param string sText The text to escape.
	* @return string The escaped string.
	*/
	var fnRegExpEscape = function( sText ) { 
		return sText.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&"); 
	};

	/**
	* Menu-based filter widgets based on distinct column values for a table.
	*
	* @class ColumnFilterWidgets 
	* @constructor
	* @param {object} oDataTableSettings Settings for the target table.
	*/
	var ColumnFilterWidgets = function( oDataTableSettings ) {
		var me = this;
		var sExcludeList = '';
		me.$WidgetContainer = $( '<div class="column-filter-widgets"></div>' );
		me.$MenuContainer = me.$WidgetContainer;
		me.$TermContainer = null;
		me.aoWidgets = [];
		me.sSeparator = '';
		if ( 'oColumnFilterWidgets' in oDataTableSettings.oInit ) {
			if ( 'aiExclude' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				sExcludeList = '|' + oDataTableSettings.oInit.oColumnFilterWidgets.aiExclude.join( '|' ) + '|';
			}
			if ( 'bGroupTerms' in oDataTableSettings.oInit.oColumnFilterWidgets && oDataTableSettings.oInit.oColumnFilterWidgets.bGroupTerms ) {
				me.$MenuContainer = $( '<div class="column-filter-widget-menus"></div>' );
				me.$TermContainer = $( '<div class="column-filter-widget-selected-terms"></div>' ).hide();
			}
		}

		// Add a widget for each visible and filtered column
		$.each( oDataTableSettings.aoColumns, function ( i, oColumn ) {
			var $WidgetElem = $( '<div class="column-filter-widget"></div>' );
			if ( sExcludeList.indexOf( '|' + i + '|' ) < 0 ) {
				me.aoWidgets.push( new ColumnFilterWidget( $WidgetElem, oDataTableSettings, i, me ) );
				me.$MenuContainer.append( $WidgetElem );
			}
		} );
		if ( me.$TermContainer ) {
			me.$WidgetContainer.append( me.$MenuContainer );
			me.$WidgetContainer.append( me.$TermContainer );
		}
		oDataTableSettings.aoDrawCallback.push( {
			name: 'ColumnFilterWidgets',
			fn: function() {
				$.each( me.aoWidgets, function( i, oWidget ) {
					oWidget.fnDraw();
				} );
			}
		} );

		return me;
	};

	/**
	* Get the container node of the column filter widgets.
	* 
	* @method
	* @return {Node} The container node.
	*/
	ColumnFilterWidgets.prototype.getContainer = function() {
		return this.$WidgetContainer.get( 0 );
	};

	/**
	* A filter widget based on data in a table column.
	* 
	* @class ColumnFilterWidget
	* @constructor
	* @param {object} $Container The jQuery object that should contain the widget.
	* @param {object} oSettings The target table's settings.
	* @param {number} i The numeric index of the target table column.
	* @param {object} widgets The ColumnFilterWidgets instance the widget is a member of.
	*/
	var ColumnFilterWidget = function( $Container, oDataTableSettings, i, widgets ) {
		var widget = this, sTargetList;
		widget.iColumn = i;
		widget.oColumn = oDataTableSettings.aoColumns[i];
		widget.$Container = $Container;
		widget.oDataTable = oDataTableSettings.oInstance;
		widget.asFilters = [];
		widget.sSeparator = '';
		widget.bSort = true;
		widget.iMaxSelections = -1;
		if ( 'oColumnFilterWidgets' in oDataTableSettings.oInit ) {
			if ( 'sSeparator' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				widget.sSeparator = oDataTableSettings.oInit.oColumnFilterWidgets.sSeparator;
			}
			if ( 'iMaxSelections' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				widget.iMaxSelections = oDataTableSettings.oInit.oColumnFilterWidgets.iMaxSelections;
			}
			if ( 'aoColumnDefs' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				$.each( oDataTableSettings.oInit.oColumnFilterWidgets.aoColumnDefs, function( iIndex, oColumnDef ) {
					var sTargetList = '|' + oColumnDef.aiTargets.join( '|' ) + '|';
					if ( sTargetList.indexOf( '|' + i + '|' ) >= 0 ) {
						$.each( oColumnDef, function( sDef, oDef ) {
							widget[sDef] = oDef;
						} );
					}
				} );
			}
		}
		widget.$Select = $( '<select></select>' ).change( function() {
			var sSelected = widget.$Select.val(), sText, $TermLink, $SelectedOption; 
			if ( '' === sSelected ) {
				// The blank option is a default, not a filter, and is re-selected after filtering
				return;
			}
			sText = $( '<div>' + sSelected + '</div>' ).text();
			$TermLink = $( '<a class="filter-term" href="#"></a>' )
				.addClass( 'filter-term-' + sText.toLowerCase().replace( /\W/g, '' ) )
				.text( sText )
				.click( function() {
					// Remove from current filters array
					widget.asFilters = $.grep( widget.asFilters, function( sFilter ) {
						return sFilter != sSelected;
					} );
					$TermLink.remove();
					if ( widgets.$TermContainer && 0 === widgets.$TermContainer.find( '.filter-term' ).length ) {
						widgets.$TermContainer.hide();
					}
					// Add it back to the select
					widget.$Select.append( $( '<option></option>' ).attr( 'value', sSelected ).text( sText ) );
					if ( widget.iMaxSelections > 0 && widget.iMaxSelections > widget.asFilters.length ) {
						widget.$Select.attr( 'disabled', false );
					}
					widget.fnFilter();
					return false;
				} );
			widget.asFilters.push( sSelected );
			if ( widgets.$TermContainer ) {
				widgets.$TermContainer.show();
				widgets.$TermContainer.prepend( $TermLink );
			} else {
				widget.$Select.after( $TermLink );
			}
			$SelectedOption = widget.$Select.children( 'option:selected' );
			widget.$Select.val( '' );
			$SelectedOption.remove();
			if ( widget.iMaxSelections > 0 && widget.iMaxSelections <= widget.asFilters.length ) {
				widget.$Select.attr( 'disabled', true );
			}
			widget.fnFilter();
		} );
		widget.$Container.append( widget.$Select );
		widget.fnDraw();
	};

	/**
	* Perform filtering on the target column.
	* 
	* @method fnFilter
	*/
	ColumnFilterWidget.prototype.fnFilter = function() {
		var widget = this;
		var asEscapedFilters = [];
		var sFilterStart, sFilterEnd;
		if ( widget.asFilters.length > 0 ) {
			// Filters must have RegExp symbols escaped
			$.each( widget.asFilters, function( i, sFilter ) {
				asEscapedFilters.push( fnRegExpEscape( sFilter ) );
			} );
			// This regular expression filters by either whole column values or an item in a comma list
			sFilterStart = widget.sSeparator ? '(^|' + widget.sSeparator + ')(' : '^(';
			sFilterEnd = widget.sSeparator ? ')(' + widget.sSeparator + '|$)' : ')$';
			widget.oDataTable.fnFilter( sFilterStart + asEscapedFilters.join('|') + sFilterEnd, widget.iColumn, true, false );
		} else { 
			// Clear any filters for this column
			widget.oDataTable.fnFilter( '', widget.iColumn );
		}
	};

	/**
	* On each table draw, update filter menu items as needed. This allows any process to
	* update the table's column visiblity and menus will still be accurate.
	* 
	* @method fnDraw
	*/
	ColumnFilterWidget.prototype.fnDraw = function() {
		var widget = this;
		var oDistinctOptions = {};
		var aDistinctOptions = [];
		var aData;
		if ( widget.asFilters.length === 0 ) {
			// Find distinct column values
			aData = widget.oDataTable.fnGetColumnData( widget.iColumn );
			$.each( aData, function( i, sValue ) {
				var asValues = widget.sSeparator ? sValue.split( new RegExp( widget.sSeparator ) ) : [ sValue ];
				$.each( asValues, function( j, sOption ) {
					if ( !oDistinctOptions.hasOwnProperty( sOption ) ) {
						oDistinctOptions[sOption] = true;
						aDistinctOptions.push( sOption );
					}
				} );
			} );
			// Build the menu
			widget.$Select.empty().append( $( '<option></option>' ).attr( 'value', '' ).text( widget.oColumn.sTitle ) );
			if ( widget.bSort ) { 
				if ( widget.hasOwnProperty( 'fnSort' ) ) {
					aDistinctOptions.sort( widget.fnSort );
				} else {
					aDistinctOptions.sort();
				}
			}
			$.each( aDistinctOptions, function( i, sOption ) {
				var sText; 
				sText = $( '<div>' + sOption + '</div>' ).text();
				widget.$Select.append( $( '<option></option>' ).attr( 'value', sOption ).text( sText ) );
			} );
			if ( aDistinctOptions.length > 1 ) {
				// Enable the menu 
				widget.$Select.attr( 'disabled', false );
			} else {
				// One option is not a useful menu, disable it
				widget.$Select.attr( 'disabled', true );
			}
		}
	};

	/*
	 * Register a new feature with DataTables
	 */
	if ( typeof $.fn.dataTable === 'function' && typeof $.fn.dataTableExt.fnVersionCheck === 'function' && $.fn.dataTableExt.fnVersionCheck('1.7.0') ) {

		$.fn.dataTableExt.aoFeatures.push( {
			'fnInit': function( oDTSettings ) {
				var oWidgets = new ColumnFilterWidgets( oDTSettings );
				return oWidgets.getContainer();
			},
			'cFeature': 'W',
			'sFeature': 'ColumnFilterWidgets'
		} );

	} else {
		throw 'Warning: ColumnFilterWidgets requires DataTables 1.7 or greater - www.datatables.net/download';
	}


}(jQuery));
