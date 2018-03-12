<?php
/**
 * PLUGIN NAME: Plot Record 
 * DESCRIPTION: Allows you to plot variable using each value from different each event. It uses external javascript library Chart.js.
 * VERSION: 0.0.1
 * AUTHOR: Pavel Dusek
 */
function checkGETVariables() {
  /** 
  * Check if project id and record id have been set and whether project is longitudinal.
  */
  if (!isset( $_GET['pid'] )) exit("<p>You have to set project ID!</p>");
  if (!isset( $_GET['record'] )) exit("<p>You have to set record ID!</p>");
  if (!REDCap::isLongitudinal()) exit("<p>Cannot get event names because this project is not longitudinal.</p>");
  return True;
}
require_once "../redcap_connect.php"; // Call the REDCap Connect file in the main "redcap" directory
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php'; // Display the project header

//Main plugin logic
echo "<h3 style='color:#800000;'> REDCap Plain Text Report Plugin </h3>";
if ( checkGETVariables() ) {
  if ( isset( $_GET['field_name'] ) ) {
    //run the plugin
    echo "<p>Project ID: $_GET[pid]</p>\n";
    echo "<p>Record ID: $_GET[record]</p>\n";
    echo "<p>field name: $_GET[field_name]</p>\n";
    echo "<h4>Data</h4>\n";
    $data = REDCap::getData($_GET['pid'], 'array', $_GET['record'], $_GET['field_name'] ); //get values of the field_name for particular project and record
    $events = REDCap::getEventNames($_GET['pid']);
    $values = Array();
    $eventNames = Array();
    $bgColor = Array();
    echo "<table border='1'>\n";
    foreach ($data as $recordid => $record) {
      foreach ($record as $eventid => $event) {
        foreach ($event as $name => $value) {
          echo "<tr><th style='padding: 5px;'>" . htmlspecialchars( $events[$eventid] ) . "</th>";
          echo "<td style='padding: 5px;'>" . htmlspecialchars( $value ) . " </td></tr>\n";
          array_push( $values, $value );
          array_push( $eventNames, "\"" . htmlspecialchars( $events[$eventid] ) . "\"" );
          array_push( $bgColor, "'rgb(0,191,255)'" );
        } 
      }
    }
    echo "</table>\n";
    $eventNames = implode( ", ", $eventNames );
    $barData = implode( ", ", $values );
    $bgColor = implode( ", ", $bgColor );
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
    <canvas id="REDCapPlotRecord-canvas" width="200px" height="200px"></canvas>
    <script>
      var ctx = document.getElementById("REDCapPlotRecord-canvas");
      var scatterChart2 = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [ <?php echo $eventNames ?> ],
          datasets: [
            {
              label: [ '<?php echo $_GET['field_name'] ?>'],
              data: [ <?php echo $barData ?> ],
              backgroundColor: [ <?php echo $bgColor ?> ]
            }
          ]
        },
	options: {
	scales: {
		responsive: false,
		maintainAspectRatio: true,
		yAxes: [{ ticks: { beginAtZero: true } }] }
	}
      });
    </script>
<?php
  } else {
    //allow to set field_name
    $data_dictionary = REDCap::getDataDictionary($_GET['pid'], 'array');
    echo "<form method='get'>\n";
    echo "<label>Project ID</label><input type='text' name='pid' value='$_GET[pid]' />\n";
    echo "<label>Record ID</label><input type='text' name='record' value='$_GET[record]' />\n";
    echo "<label>Variable name:</label>\n";
    echo "\t<select name='field_name'>\n";
    foreach ($data_dictionary as $field_name => $attributes) {
      echo "\t\t<option value='$field_name'>$field_name</option>\n";
    }
    echo "\t</select>\n";
    echo "\t<input type='submit'value='submit' />\n";
    echo "</form>\n";
  }
}
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php'; // Display the project footer
?>
