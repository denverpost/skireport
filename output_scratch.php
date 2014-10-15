<?
    			// Logic to handle display of update data
    			if ( $row['open'] == 'Opened' ) //This is set to not work on purpose
    			{
    				$$row['slug'] .= '
    				<h4>New in this update</h4>
    				';
    				$$row['slug'] .= '<dl>';
    				if ( $row['previous_newsnow_24_in'] != '' )
    				{
    					$$row['slug'] .= '<dt>New Snow, 24 Hours:</dt><dd style="clear:left;">Was ' . $row['previous_newsnow_24_in'] . '", now is ' . $newsnow_24_in . '"</dd>
    ';
    				}
    				if ( $row['previous_newsnow_48_in'] != '' )
    				{
    					$$row['slug'] .= '<dt>New Snow, 48 Hours:</dt><dd style="clear:left;">Was ' . $row['previous_newsnow_48_in'] . '", now is ' . $newsnow_48_in . '"</dd>
    ';
    				}
    				if ( $row['previous_newsnow_72_in'] != '' )
    				{
    					$$row['slug'] .= '<dt>New Snow, 72 Hours:</dt><dd style="clear:left;">Was ' . $row['previous_newsnow_72_in'] . '", now is ' . $newsnow_72_in . '"</dd>
    ';
    				}
    				if ( $row['previous_conditions'] != '' )
    				{
    					$$row['slug'] .= '<dt>Conditions:</dt><dd style="clear:left;">Was ' . $row['previous_conditions'] . ', now is ' . $conditions . '</dd>
    ';
    				}
    				if ( $row['previous_numliftsopen'] != '' )
    				{
    					$$row['slug'] .= '<dt>Lifts Open:</dt><dd style="clear:left;">Was ' . $row['previous_numliftsopen'] . ', now is ' . $numliftsopen . '</dd>
    ';
    				}
    				if ( $row['previous_numliftstotal'] != '' )
    				{
    					$$row['slug'] .= '<dt>Lifts Total:</dt><dd style="clear:left;">Was ' . $row['previous_numliftstotal'] . ', now is ' . $numliftstotal . '</dd>
    ';
    				}
    				if ( $row['previous_acresopen'] != '' )
    				{
    					$$row['slug'] .= '<dt>Acres Open:</dt><dd style="clear:left;">Was ' . $row['previous_acresopen'] . ', now is ' . $acresopen . '</dd>
    ';
    				}
    				if ( $row['previous_kmxc'] != '' )
    				{
    					$$row['slug'] .= '<dt>Cross Country Open:</dt><dd style="clear:left;">Was ' . $row['previous_kmxc'] . ' km, now is ' . $kmxc . ' km</dd>
    ';
    				}
    				if ( $row['previous_eventnotices'] != '' )
    				{
    					$$row['slug'] .= '<dt>Event Notices:</dt><dd style="clear:left;">Was ' . $row['previous_eventnotices'] . ', now is ' . $eventnotices . '</dd>
    ';
    				}
    				if ( $row['previous_basedepth_in'] != '' )
    				{
    					$$row['slug'] .= '<dt>Base Depth:</dt><dd style="clear:left;">Was ' . $row['previous_basedepth_in'] . '", now is ' . $basedepth_in . '"</dd>
    ';
    				}
    				if ( $row['previous_topdepth_in'] != '' )
    				{
    					$$row['slug'] .= '<dt>Top Depth:</dt><dd style="clear:left;">Was ' . $row['previous_topdepth_in'] . '", now is ' . $topdepth_in . '"</dd>
    ';
    				}
    				if ( $row['previous_open'] != '' )
    				{
    					$$row['slug'] .= '<dt>Open:</dt><dd style="clear:left;">Was ' . $row['previous_open'] . ', now is ' . $open . '!</dd>
    ';
    				}
    				if ( $row['previous_baseweather'] != '' )
    				{
    					$$row['slug'] .= '<dt>Base Weather:</dt><dd style="clear:left;">Was ' . $row['previous_baseweather'] . ', now is ' . $baseweather . '</dd>
    ';
    				}
    				if ( $row['previous_basetemperature_f'] != '' )
    				{
    					$$row['slug'] .= '<dt>Base Temperature:</dt><dd style="clear:left;">Was ' . $row['previous_basetemperature_f'] . ' f, now is ' . $basetemperature_f . ' f</dd>
    ';
    				}
    				if ( $row['previous_numberofruns'] != '' )
    				{
    					$$row['slug'] .= '<dt>Number of Runs:</dt><dd style="clear:left;">Was ' . $row['previous_numberofruns'] . ', now is ' . $numberofruns . '</dd>
    ';
    				}
    				$$row['slug'] .= '</dl>';
    			}
    			// END Logic to handle display of update data
?>
