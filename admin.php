<?
// +----------------------------------------------------------------------+
// | xml2db / Example                                                     |
// | Creating SQL Queries from a xml file                                 |
// | Requirements: PHP5 with SimpleXML Support                            |
// | This file explains how to use and call the class                     |
// +----------------------------------------------------------------------+
// | Author: Nico Puhlmann <nico@puhlmann.com>                            |
// +----------------------------------------------------------------------+
// $Id: example.php,v 1.0 2oo5/o4/29 18:11:23 npuhlmann Exp $

include( dirname(__FILE__) . "/class.xml2db.php");

echo '<pre>';

$xml = simplexml_load_file("na-ski.xml");
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

$sql_skiarea = "
	INSERT INTO skiarea 
		( skiarea_id, country_id, region_id, state_id, name, shortname, url, email, open, callaheadphone, projectedopeningdate, projectedclosingdate, lastupdate, report_ski, report_snowboard, report_crosscountry ) 
		VALUES ";

$sql_skiarea_links = "
	INSERT INTO skiarea_links
		( skiarea_id, label, url )
		VALUES ";

foreach ( $xml->children() as $key1 => $value1 )
{
	$value1_attr = attr_gather($value1);
	$$key1 = $value1->attributes();

	foreach ( $value1 as $key2 => $value2 )
	{
		$value2_attr = attr_gather($value2);
		$$key2 = $value2->attributes();


		foreach ( $value2 as $key3 => $value3 )
		{
			$value3_attr = attr_gather($value3);
			$$key3 = $value3->attributes();

			foreach ( $value3 as $key4 => $value4 )
			{
				$value4_attr = attr_gather($value4);
				$$key4 = $value4->attributes();

				foreach ( $value4 as $key5 => $value5 )
				{
					$value5_attr = attr_gather($value5);

					if ( $key5 == 'Email' ) { $value5_tmp = explode(' ', $value5); $value5 = ( count($value5_tmp) > 0 ) ? $value5_tmp[0] : $value5; }
					if ( $key5 == 'MNCLink' )
					{
						$sql_skiarea_links .= "
	( $SkiArea, '" . attr_get($value5, 'label') . "', '$value5' ),";
					}
					if ( $key5 == 'ReportIndicators' )
					{
						foreach ( $value5 as $key6 => $value6 ) $$key6 = $value6;
					}
					$$key5 = trim($value5);
					

				}
				
				
				if ( $$SkiArea != TRUE && $SkiArea != '' ) $sql_skiarea .= "
		( $SkiArea, $Country, $Region, $State, '" . addslashes($Name) . "', '" . addslashes($ShortName) . "', '$URL', '$Email', '$Open', '$CallAheadPhone', STR_TO_DATE('$ProjectedOpeningDate', '%m/%d/%y'), STR_TO_DATE('$ProjectedClosingDate', '%m/%d/%y'), STR_TO_DATE('$LastUpdate', '%m/%d/%y'), '$Ski', '$Snowboard', '$CrossCountry'),";
				$$SkiArea = TRUE; 
//echo $flag; $flag = $SkiArea; 
			}
		}
	}
}
if ( isset($_GET['links']) ) echo $sql_skiarea_links;
if ( isset($_GET['ski']) ) echo $sql_skiarea;

?>
