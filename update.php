<?
// +----------------------------------------------------------------------+
// | skireport / Update                                                   |
// +----------------------------------------------------------------------+
// | Author: Joe Murphy <jmurphy@denverpost.com>                          |
// +----------------------------------------------------------------------+

include('output_constants.php');
foreach ( $_SERVER['argv'] as $value ) $argv[$value] = TRUE;

$last_update = array();
$update_sql = array();

function attr_gather($input)
{
	foreach ( $input->attributes() as $key => $value ) $return .= trim($key) . ':' . trim($value) . '|'	;
	if ( $return != '' ) return '(' . $return . ')';
	else return '';
}
function attr_get($xml, $name)
{
	foreach ( $xml->attributes() as $key => $value ) if ( $key == $name ) return $value;
}


//$ids = explode("\n", file_get_contents('ids.txt'));
//if ( count($ids) == 1 || ( count($ids) == 2 && $ids[0] == "11" ) ) die('Nothing to update');

if ( $argv['update'] == TRUE )
{
       $ids = explode("\n", file_get_contents('ids.txt'));
       //if ( count($ids) == 1 || ( count($ids) == 2 && $ids[0] == "11" ) ) die('Nothing to update');
}
if ( $argv['colorado'] == TRUE || $argv['backup'] == TRUE )
{
	$ids = explode("\n", file_get_contents('ids.colorado.txt'));
}

$sql_skiarea = "
	INSERT INTO skiarea
		( skiarea_id, country_id, region_id, state_id, name, shortname, url, email, callaheadphone, projectedopeningdate, projectedclosingdate, lastupdate)
		VALUES ";

$sql_skiarea_update = "
	";

$sql_skiarea_links = "
	INSERT INTO skiarea_links
		( skiarea_id, label, url )
		VALUES ";

$sql_report = "
	INSERT INTO report
		( skiarea_id, newsnow_24_in, newsnow_48_in, newsnow_72_in, basedepth_in, topdepth_in, conditions, numliftsopen, numliftstotal, perliftsopen, numberofruns, acresopen, kmxc, eventnotices, cc_facilities, cc_numberoftrails, cc_kmopen, cc_kmtrackset, cc_kmskategroomed, sb_parkresshaped, sb_piperecut, sb_hits, sb_pipes, basetemperature_f, basetemperature_c, baseweather, lastupdate, open )
		VALUES ";

// KILL FIELD LIST:
// report.cc_facilities
foreach ( $ids as $id ):
    echo $id;
    if ( trim($id) == '' ) continue;

    $ASSOC_ARRAY = TRUE;
    $record = json_decode(file_get_contents('/tmp/' . $id . '-snowreport'), $ASSOC_ARRAY);
    if ( $record ):
        $report = $record['report'];
        // Zero out the fields we haven't dealt with upgrading yet.
        $NumLiftsTotal = 0;
        $NumberOfTrails = 0;
        $KmOpen = 0;
        $KmTrackset = 0;
        $KmSkateGroomed = 0;
        $ParkResshaped = 0;
        $PipeRecut = 0;
        $Hits = 0;
        $Pipes = 0;
        
        if ( ( $argv['update'] == TRUE ) || $argv['report'] == TRUE )
        {
            if ( $report['terrainReport']['acresOpen'] == '' ) $report['terrainReport']['acresOpen'] = 0;
            // We lay out these vars to make it easier for us to edit.
            $sql_report_tmp = "
( $id,
 " . $report['snowfall']['snow24h'] . ",
 " . $report['snowfall']['snow48h'] . ",
 " . $report['snowfall']['snow72h'] . ",
 " . $report['snowQuality']['onSlope']['lowerDepth'] . ",
 " . $report['snowQuality']['onSlope']['upperDepth'] . ",
 '" . $report['snowQuality']['onSlope']['surfaceBottom'] . ", " . $report['snowQuality']['onSlope']['surfaceTop'] ."',
 " . $report['liftsReport']['liftsOpen'] . ",
 " . $NumLiftsTotal . ",
 " . $report['liftsReport']['perLiftsOpen'] . ",
 " . $report['terrainReport']['trailsOpen'] . ",
 " . $report['terrainReport']['acresOpen'] . ",
 " . $report['terrainReport']['numKmOpen'] . ",
 '" . $EventNotices . "',
 '" . $Facilities . "',
 " . $NumberOfTrails . ",
 " . $KmOpen . ",
 " . $KmTrackset . ",
 " . $KmSkateGroomed . ",
 " . $ParkResshaped . ",
 " . $PipeRecut . ",
 " . $Hits . ",
 " . $Pipes . ",
 '" . $report['resortReportedWeather']['tempBottom'] . "',
 0,
 '" . $report['resortReportedWeather']['baseWeatherText'] . "',
 STR_TO_DATE('$LastUpdate',
 '%m/%d/%y'),
 '$Open' ),
";
            $sql_report .= str_replace("\n", "", $sql_report_tmp)  . "\n";
        }

        // Also, we save the LastUpdate value in an array assigned
        // to that SkiArea for reference
        if ( $State == 8 )
        {
            $last_update[intval($SkiArea)] = $LastUpdate;
            $update_sql[intval($SkiArea)] = "
( $SkiArea, $Ski, $Snowboard, $CrossCountry, $NewSnow24_in, $NewSnow24_cm, $NewSnow48_in, $NewSnow48_cm, $NewSnow72_in, $NewSnow72_cm, $BaseDepth_in, $BaseDepth_cm, $TopDepth_in, $TopDepth_cm, '$Conditions', $NumLiftsOpen, $NumLiftsTotal, $PerLiftsOpen, $NumberOfRuns, $AcresOpen, $KmXC, '$EventNotices', '$Facilities', $NumberOfTrails, $KmOpen, $KmTrackset, $KmSkateGroomed, $ParkResshaped, $PipeRecut, $Hits, $Pipes, $BaseTemperature_f, $BaseTemperature_c, '$BaseWeather', STR_TO_DATE('$LastUpdate', '%m/%d/%y'), '$Open' ),";
        }
    endif;
endforeach;
    


//Return the query, with the final comma chopped off and replaced with a semi-colon.
if ( $argv['links'] == TRUE ) echo substr($sql_skiarea_links, 0, -1) . ";\n";
if ( $argv['ski'] == TRUE ) echo substr($sql_skiarea, 0, -1) . ";\n";
if ( $argv['skiarea_update'] == TRUE ) echo $sql_skiarea_update;
if ( $argv['report'] == TRUE || $argv['update'] == TRUE ) echo substr($sql_report, 0, -2) . ";\n";

/*
// Data cleanup
					//Turning "yes" into 1 and "no" into 0
					if ( $value5 == 'yes' ) $value5 = 1;
					elseif ( $value5 == 'no' ) $value5 = 0;

					$value5_attr = attr_gather($value5);
					//Set variables that could maintain value from the previous loop to NULL
					$KmXC = 'NULL'; $Facilities = 'NULL'; $NumberOfTrails = 'NULL'; $KmOpen = 'NULL'; $KmTrackset = 'NULL'; $KmSkateGroomed = 'NULL'; $ParkResshaped = 'NULL'; $PipeRecut = 'NULL'; $Hits = 'NULL'; $Pipes = 'NULL'; $BaseTemperature_f = 'NULL'; $BaseTemperature_c = 'NULL';

					if ( $key5 == 'Email' ) { $value5_tmp = explode(' ', $value5); $value5 = ( count($value5_tmp) > 0 ) ? $value5_tmp[0] : $value5; }
					if ( $key5 == 'MNCLink' )
					{
						$sql_skiarea_links .= "
	( $SkiArea, '" . attr_get($value5, 'label') . "', '$value5' ),";
					}
					if ( $key5 == 'ReportIndicators' || $key5 == 'CrossCountryReport' || $key5 == 'SnowboardReport' )
					{
						foreach ( $value5 as $key6 => $value6 )
						{
							if ( $value6 == 'N/A' ) $value6 = 'NULL';
							//Turning "yes" into 1 anif ( $argv['update'] == TRUE ) echo $sql_update;d "no" into 0
							if ( $value6 == 'yes' ) $value6 = 1;
							elseif ( $value6 == 'no' ) $value6 = 0;
							$$key6 = $value6;
						}
					}
					if ( $key5 == 'NewSnow24' || $key5 == 'NewSnow48' || $key5 == 'NewSnow72' || $key5 == 'BaseDepth' || $key5 == 'TopDepth' || $key5 == 'BaseTemperature' )
					{
						$unit = strtolower(attr_get($value5, 'unit'));
						eval("$$key5" . '_' . "$unit" . ' = ' . "'$value5';");
					}
					if ( $value5 == 'N/A' ) $value5 = 'NULL';
				}






				if ( $$SkiArea != TRUE && $SkiArea != '' )
				{
					//$sql_skiarea_update .=
					$$SkiArea = TRUE;

					//Add logic to only output the Colorado resorts
					if ( $State == 8 )  $sql_skiarea_update .= "UPDATE skiarea SET projectedopeningdate = STR_TO_DATE('$ProjectedOpeningDate', '%m/%d/%y'), projectedclosingdate = STR_TO_DATE('$ProjectedClosingDate', '%m/%d/%y') WHERE skiarea_id = $SkiArea LIMIT 1;
";
					if ( $State == 8 )  $sql_skiarea .= "
		( $SkiArea, $Country, $Region, $State, '" . addslashes($Name) . "', '" . addslashes($ShortName) . "', '$URL', '$Email', '$CallAheadPhone', STR_TO_DATE('$ProjectedOpeningDate', '%m/%d/%y'), STR_TO_DATE('$ProjectedClosingDate', '%m/%d/%y'), STR_TO_DATE('$LastUpdate', '%m/%d/%y') ),";
				}
*/









if ( $argv['backup'] == TRUE )
{
	$sql_backup = "
		INSERT INTO report
			( skiarea_id, report_ski, report_snowboard, report_crosscountry, newsnow_24_in, newsnow_24_cm, newsnow_48_in, newsnow_48_cm, newsnow_72_in, newsnow_72_cm, basedepth_in, basedepth_cm, topdepth_in, topdepth_cm, conditions, numliftsopen, numliftstotal, perliftsopen, numberofruns, acresopen, kmxc, eventnotices, cc_facilities, cc_numberoftrails, cc_kmopen, cc_kmtrackset, cc_kmskategroomed, sb_parkresshaped, sb_piperecut, sb_hits, sb_pipes, basetemperature_f, basetemperature_c, baseweather, lastupdate, open )
			VALUES ";

	// This is the update.php backup -- in case a resort has updated their information, but we missed it in the xml diff
	foreach ( $ids as $SkiArea )
	{
		// We're looking here for Colorado resorts that have an older lastupdate date than the XML file.
		//echo $SkiArea . ' ' . $last_update[intval($SkiArea)] . ' ';
		$sql = '
		SELECT COUNT(*) FROM report
		WHERE
			skiarea_id = ' . $SkiArea . '
			AND lastupdate = STR_TO_DATE(\'' . $last_update[intval($SkiArea)] . '\', \'%m/%d/%y\');
		';
		$result = $db->query($sql);
		$count = $db->fetch($result);
		//echo $count[0] . "\n";

		// If the count is 0, then we write whatever was in that record to the database.
		if ( $count[0] == 0 )
		{
			$sql_backup .= $update_sql[intval($SkiArea)];
		}
	}

	echo substr($sql_backup, 0, -1) . ";\n";
}
?>
