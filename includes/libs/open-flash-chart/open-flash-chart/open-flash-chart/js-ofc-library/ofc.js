function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}


/**
 * @param index as integer.
 *
 * Returns a CLONE of the chart with one of the elements removed
 */
function chart_remove_element(chart, index)
{
    
 //   global_showing_old_data = !global_showing_old_data;
    
    // clone the chart
    var modified_chart = {};
    jQuery.extend(modified_chart, chart);

    // remove the old data from the chart:
    var element = modified_chart.elements[1];
    var elements = new Array();
    var c=0;
    for(i=0; i<modified_chart.elements.length; i++)
    {
      if(i!=index)
      {
        elements[c] = modified_chart.elements[i];
        c++;
      }
    }
    modified_chart.elements = elements;
    return modified_chart;
}