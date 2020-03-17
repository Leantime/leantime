/*
 * Natural Sort algorithm for Javascript - Version 0.6 - Released under MIT license
 * Author: Jim Palmer (based on chunking idea from Dave Koelle)
 * Contributors: Mike Grier (mgrier.com), Clint Priest, Kyle Adams, guillermo
 */
function naturalSort (a, b) {
	var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
		sre = /(^[ ]*|[ ]*$)/g,
		dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
		hre = /^0x[0-9a-f]+$/i,
		ore = /^0/,
		// convert all to strings and trim()
		x = a.toString().replace(sre, '') || '',
		y = b.toString().replace(sre, '') || '',
		// chunk/tokenize
		xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
		yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
		// numeric, hex or date detection
		xD = parseInt(x.match(hre)) || (xN.length != 1 && x.match(dre) && Date.parse(x)),
		yD = parseInt(y.match(hre)) || xD && y.match(dre) && Date.parse(y) || null;
	// first try and sort Hex codes or Dates
	if (yD)
		if ( xD < yD ) return -1;
		else if ( xD > yD )	return 1;
	// natural sorting through split numeric strings and default strings
	for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
		// find floats not starting with '0', string or 0 if not defined (Clint Priest)
		oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
		oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0;
		// handle numeric vs string comparison - number < string - (Kyle Adams)
		if (isNaN(oFxNcL) !== isNaN(oFyNcL)) return (isNaN(oFxNcL)) ? 1 : -1; 
		// rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
		else if (typeof oFxNcL !== typeof oFyNcL) {
			oFxNcL += ''; 
			oFyNcL += ''; 
		}
		if (oFxNcL < oFyNcL) return -1;
		if (oFxNcL > oFyNcL) return 1;
	}
	return 0;
}

/* This script and many more are available free online at
The JavaScript Source!! http://javascript.internet.com
Created by: Robert Nyman | http://robertnyman.com/ */
function removeHTMLTags(string){
 	
 		var strInputCode = string;
 		/* 
  			This line is optional, it replaces escaped brackets with real ones, 
  			i.e. < is replaced with < and > is replaced with >
 		*/	
 	 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
 		 	return (p1 == "lt")? "<" : ">";
 		});
 		
 		var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");

 		return strTagStrippedText;
 			

}

jQuery(function($) {
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
			iRow = aiRows[i];
			var aData = this.fnGetData(iRow);
			var sValue = removeHTMLTags(aData[iColumn]);
			
			// ignore empty values?
			if (bIgnoreEmpty == true && sValue.length == 0) {

                // ignore unique values?
            } else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) {

                // else push the value onto the result data array
            }else asResultData.push(sValue);
		}
		
		return asResultData;
	};
}(jQuery));


	function fnCreateSelect( aData )
	{
		
		aData.sort(naturalSort);
		
		var r='<select style="width:100%"><option value=""></option>', i, iLen=aData.length;
		for ( i=0 ; i<iLen ; i++ )
		{
			r += '<option value="'+aData[i]+'">'+aData[i]+'</option>';
		}
		return r+'</select>';
	}

	jQuery.fn.dataTableExt.oSort['natural-asc']  = function(a,b) {
		return naturalSort(a,b);
	};

	jQuery.fn.dataTableExt.oSort['natural-desc'] = function(a,b) {
		return naturalSort(a,b) * -1;
	};
	
	var oTable = '';
	var oTable2 = '';
	
	jQuery(document).ready(function($) 
    	{ 
		
		var size = 10;
		
				
		var asInitVals = [];
        	
		//Table 1
		oTable = $("#resultTable").dataTable({
        	   
        	   "sPaginationType": "full_numbers",
        	   "bFilter": true,
        	    "fnDrawCallback": function(oSettings) {

            	},
        	   "sDom": '<"H"<"left"l><"right"f>>rt<"F"<"left"i><"right"p>>',
        	   "iDisplayLength": size,
        	   "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Alle"]],
	       	   "oTableTools": {
        		    "sSwfPath": "includes/libs/DataTables-1.7.6/extras/TableTools/media/swf/copy_cvs_xls_pdf.swf",
        		    "aButtons": [
        		                 {
        		                	 "sExtends":    "copy",
        		                	 "sButtonText": "Copy",
        		                	 "sButtonClass": "dataTablesCopy",
        		                	 "sButtonClassHover":"dataTablesCopyHover"
        		                 },
     	       					
        		                 {
        		                	 "sExtends":    "print",
        		                	 "sButtonText": "Print",
        		                	 "sButtonClass": "dataTablesPrint",
        		                	 "sButtonClassHover":"dataTablesPrintHover",
        		                	 "sInfo": "Dieses ist die Druckansicht. Benutzen Sie nun die Druckfunktion ihres Browsers. Drücken Sie ESC wenn Sie fertig sind."
        		                	 
        		                 },
     	       					{
     	       						"sExtends":    "collection",
     	       						"sButtonText": "Exportieren",
     	       						"sButtonClass": "dataTablesExport",
     	       						"sButtonClassHover":"dataTablesExportHover",
     	       						"aButtons":    [ "csv", "xls", "pdf" ]
     	       					}
     	       					
     	       			]
	       		},
	       		"oColVis": {
	    			"buttonText": "Hide Columns"
	    		},
        	   "oLanguage": {
       				"sSearch": "Search:",
       				"sLengthMenu": "Show _MENU_ entries per page",
					"sZeroRecords": "No results",
					"sInfo": "Show _START_ to _END_ of _TOTAL_ entries",
					"sInfoEmpty": "Show 0 to 0 of 0 Entries",
					"sInfoFiltered": "(Filtered from _MAX_ entries)"
       			}
        	   });

			$("#resultTable tfoot tr.filter td:not(.noFilter)").each( function ( i ) {
				this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
				$('select', this).change( function () {
					oTable.fnFilter( $(this).val(), i );
				} );
			});

		
			
			//Table 2
			var oTable2 = $("#resultTable2").dataTable({
                "sPaginationType": "full_numbers",
                "bFilter": true,
                "fnDrawCallback": function(oSettings) {

                },
                "sDom": '<"H"<"left"l><"right"f>>rt<"F"<"left"i><"right"p>>',
                "iDisplayLength": size,
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Alle"]],
                "oTableTools": {
                    "sSwfPath": "includes/libs/DataTables-1.7.6/extras/TableTools/media/swf/copy_cvs_xls_pdf.swf",
                    "aButtons": [
                        {
                            "sExtends":    "copy",
                            "sButtonText": "Copy",
                            "sButtonClass": "dataTablesCopy",
                            "sButtonClassHover":"dataTablesCopyHover"
                        },

                        {
                            "sExtends":    "print",
                            "sButtonText": "Print",
                            "sButtonClass": "dataTablesPrint",
                            "sButtonClassHover":"dataTablesPrintHover",
                            "sInfo": "Dieses ist die Druckansicht. Benutzen Sie nun die Druckfunktion ihres Browsers. Drücken Sie ESC wenn Sie fertig sind."

                        },
                        {
                            "sExtends":    "collection",
                            "sButtonText": "Exportieren",
                            "sButtonClass": "dataTablesExport",
                            "sButtonClassHover":"dataTablesExportHover",
                            "aButtons":    [ "csv", "xls", "pdf" ]
                        }

                    ]
                },
                "oColVis": {
                    "buttonText": "Hide Columns"
                },
                "oLanguage": {
                    "sSearch": "Search:",
                    "sLengthMenu": "Show _MENU_ entries per page",
                    "sZeroRecords": "No results",
                    "sInfo": "Show _START_ to _END_ of _TOTAL_ entries",
                    "sInfoEmpty": "Show 0 to 0 of 0 Entries",
                    "sInfoFiltered": "(Filtered from _MAX_ entries)"
                }
        	   });

			$("#resultTable tfoot tr.filter td:not(.noFilter)").each( function ( i ) {
				this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
				$('select', this).change( function () {
					oTable.fnFilter( $(this).val(), i );
				} );
			});
			
				
				
				//Table 3
            var oTable3 = $("#resultTable3").dataTable({
                "sPaginationType": "full_numbers",
                "bFilter": true,
                "fnDrawCallback": function(oSettings) {

                },
                "sDom": '<"H"<"left"l><"right"f>>rt<"F"<"left"i><"right"p>>',
                "iDisplayLength": size,
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Alle"]],
                "oTableTools": {
                    "sSwfPath": "includes/libs/DataTables-1.7.6/extras/TableTools/media/swf/copy_cvs_xls_pdf.swf",
                    "aButtons": [
                        {
                            "sExtends":    "copy",
                            "sButtonText": "Copy",
                            "sButtonClass": "dataTablesCopy",
                            "sButtonClassHover":"dataTablesCopyHover"
                        },

                        {
                            "sExtends":    "print",
                            "sButtonText": "Print",
                            "sButtonClass": "dataTablesPrint",
                            "sButtonClassHover":"dataTablesPrintHover",
                            "sInfo": "Dieses ist die Druckansicht. Benutzen Sie nun die Druckfunktion ihres Browsers. Drücken Sie ESC wenn Sie fertig sind."

                        },
                        {
                            "sExtends":    "collection",
                            "sButtonText": "Exportieren",
                            "sButtonClass": "dataTablesExport",
                            "sButtonClassHover":"dataTablesExportHover",
                            "aButtons":    [ "csv", "xls", "pdf" ]
                        }

                    ]
                },
                "oColVis": {
                    "buttonText": "Hide Columns"
                },
                "oLanguage": {
                    "sSearch": "Search:",
                    "sLengthMenu": "Show _MENU_ entries per page",
                    "sZeroRecords": "No results",
                    "sInfo": "Show _START_ to _END_ of _TOTAL_ entries",
                    "sInfoEmpty": "Show 0 to 0 of 0 Entries",
                    "sInfoFiltered": "(Filtered from _MAX_ entries)"
                }
        	   });

			$("#resultTable tfoot tr.filter td:not(.noFilter)").each( function ( i ) {
				this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
				$('select', this).change( function () {
					oTable.fnFilter( $(this).val(), i );
				} );
			});

    	});   