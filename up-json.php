<?php
// +----------------------------------------------------------------------+
// | skireport / Update                                                   |
// +----------------------------------------------------------------------+
// | Author: Joe Murphy <jmurphy@denverpost.com>                          |
// +----------------------------------------------------------------------+

$json = file_get_contents('ski-new.json');

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
	INSERT INTO report_snocountry
		( skiarea_id, newsnow_24_in_min, newsnow_24_in_max, newsnow_48_in, basedepth_in, topdepth_in, conditions, numliftsopen, numliftstotal, perliftsopen, numberofruns, acresopen, eventnotices, basetemperature_f, baseweather, lastupdate, open, last_snowfall_date, last_snowfall_amount, prev_snowfall_date, prev_snowfall_amount)
		VALUES ";

$resort_data = json_decode($json, TRUE);
//print_r($resort_data);
$year = 2010;
foreach ( $resort_data['items'] as $resort )
{
    //print_r($resort);
    // We trust the data in this array, so we can extract it.
    extract($resort);
    //echo "$resortName\n$id\n=====\n";

                        $percentLiftsOpen = intval($openDownHillLifts/$maxOpenDownHillLifts);

                        $lastsnow_sql = "''";
                        if ( $lastSnowFallDate != '' ) $lastsnow_sql = "STR_TO_DATE('$lastSnowFallDate $year',  '%b %d %Y')";

                        $prevsnow_sql = "''";
                        if ( $prevSnowFallDate != '' ) $prevsnow_sql = "STR_TO_DATE('$prevSnowFallDate $year',  '%b %d %Y')";

						$sql_report .= "
		( $id, '$newSnowMin', '$newSnowMax', '$snowLast48Hours', '$avgBaseDepthMin', '$avgBaseDepthMax', '$primarySurfaceCondition', '$openDownHillLifts', '$maxOpenDownHillLifts', '$percentLiftsOpen', '$openDownHillTrails', '$openDownHillAcres', '$primarySurfaceCondition', '$forecastBaseTemp', '$forecastWeather', STR_TO_DATE('$LastUpdate', '%m/%d/%y'), '$resortStatus', $lastsnow_sql, '$lastSnowFallAmount', $prevsnow_sql, '$prevSnowFallAmount' ),";
}

echo substr($sql_report, 0, -1) . ";\n";
?>
