<?php
date_default_timezone_set('America/Denver');
$input = array(
    'db' => 'db27949_ski',
    'server' => 'localhost',
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS']);

if ( $_ENV['DEPLOY'] == 'localhost' )
{
	$cachepath = 'cache/';
	$extraspath = '/DenverPost/skireport/';
    $libpath = '/var/www/lib/';
	$ftp_action = FALSE;
}
else
{
    $inputftp['error_check'] = TRUE;
	$cachepath = '/var/www/vhosts/denverpostplus.com/httpdocs/cache/';
	$extraspath = '/DenverPost/skireport/';
	$libpath = '/var/www/lib/';
	$ftp_action = 1;
}

$argv = array();
if ( $_SERVER['argc'] > 1 ):
	$newline = "\n";
	foreach ( $_SERVER['argv'] as $value ) $argv[$value] = TRUE;
endif;



require($libpath . "class.db.php");
require($libpath . "class.page.php");
require($libpath . "class.ftp.php");



//$date_now = date("D, j M Y H:i:s O");
//$db = new db($input['db'], $input);
//$db->connect();

if ( $_SERVER['argv'][2] == 'noftp' || $ftp_action == FALSE ) $ftp_action = FALSE;
else
{
	$ftp = new ftp($inputftp);
	$ftp->connection_passive();
}
//unset($input);




// SNOW REPORT This query works if we want to get all the skiareas first, then sort them out later.
// It's not currently in use.
$input['skiarea']['sql'] = '
	SELECT
		DISTINCT(r.skiarea_id),
		DATEDIFF(r.lastupdate, CURDATE()) AS lastupdate_diff,
		DATE_FORMAT(r.lastupdate, "%b. %e") AS lastupdate_str,
		DATE_FORMAT(r.timestamp, " at %h:%i %p") AS lastupdate_time_str,
		DATEDIFF(s.projectedopeningdate, CURDATE()) AS opendate,
		DATE_FORMAT(s.projectedopeningdate, "%b. %e") AS opendate_str,
		TIME_FORMAT(TIMEDIFF(NOW(), d.timestamp), "Updated about %k hour(s) ago") AS timeago,
		r.lastupdate, r.newsnow_24_in, r.newsnow_48_in, r.newsnow_72_in, r.conditions, r.numliftsopen, r.numliftstotal, r.acresopen, r.kmxc, r.eventnotices, r.basedepth_in, r.topdepth_in, r.open, r.baseweather, r.basetemperature_f, r.numberofruns,
		e.phone, e.terrain_acres, e.terrain_acres_expert, e.terrain_acres_advanced, e.terrain_acres_intermediate, e.terrain_acres_beginner, e.trails, e.terrain_parks, e.terrain_parks_acres, e.summit, e.base, e.vertical, e.longest_run, e.hours_open, e.hours_close, e.hours_notes, e.lifts_notes, e.tickets_notes, e.location_notes, e.location_link, e.deaths_2007_2012,
		d.open AS previous_open, d.numliftsopen AS previous_numliftsopen, d.numliftstotal AS previous_numliftstotal, d.numberofruns AS previous_numberofruns, d.acresopen AS previous_acresopen, d.basedepth_in AS previous_basedepth_in, d.topdepth_in AS previous_topdepth_in, d.newsnow_24_in AS previous_newsnow_24_in, d.newsnow_48_in AS previous_newsnow_48_in, d.newsnow_72_in AS previous_newsnow_72_in, d.conditions, d.kmxc AS previous_kmxc, d.eventnotices AS previous_eventnotices, d.baseweather AS previous_baseweather, d.basetemperature_f AS previous_basetemperature_f,
		ROUND((e.terrain_acres_expert/e.terrain_acres)*100) AS terrain_expert_pct, ROUND((e.terrain_acres_advanced/e.terrain_acres)*100) AS terrain_advanced_pct,  ROUND((e.terrain_acres_intermediate/e.terrain_acres)*100) AS terrain_intermediate_pct,  ROUND((e.terrain_acres_beginner/e.terrain_acres)*100) AS terrain_beginner_pct,
		s.name, s.slug, s.callaheadphone, s.projectedopeningdate, s.url, s.email
	FROM report r, skiarea s, skiarea_extras e, report_delta d
	WHERE
		e.skiarea_id = s.skiarea_id
		AND d.skiarea_id = s.skiarea_id
		AND r.skiarea_id = s.skiarea_id
		AND d.new = 0
		AND s.projectedopeningdate <> 0
		AND s.state_id = 8
        AND r.timestamp > CURDATE() - 2
	ORDER BY s.name, r.lastupdate DESC, r.timestamp DESC, d.timestamp DESC';

// This query is the one we're using to pull recent results for the snow report.
$input['skiareaid']['sql'] = '
	SELECT
		r.skiarea_id,
		DATEDIFF(r.lastupdate, CURDATE()) AS lastupdate_diff,
		DATE_FORMAT(r.lastupdate, "%b. %e") AS lastupdate_str,
		DATE_FORMAT(r.timestamp, " at %h:%i %p") AS lastupdate_time_str,
        DATE_FORMAT(r.timestamp, "%a, %d %b %Y %H:%i:%s -0700") AS lastupdate_time_str_rss,
		UNIX_TIMESTAMP(r.timestamp) AS lastupdate_time_unix,
		DATEDIFF(s.projectedopeningdate, CURDATE()) AS opendate,
		DATE_FORMAT(s.projectedopeningdate, "%b. %e") AS opendate_str,
		#TIME_FORMAT(TIMEDIFF(NOW(), d.timestamp), "Updated about %k hour(s) ago") AS timeago,
        \'&nbsp;\' as timeago,
		r.lastupdate, r.newsnow_24_in, r.newsnow_48_in, r.newsnow_72_in, r.conditions, r.numliftsopen, r.numliftstotal, r.acresopen, r.kmxc, r.eventnotices, r.basedepth_in, r.topdepth_in, r.open, r.baseweather, r.basetemperature_f, r.numberofruns,
		e.phone, e.terrain_acres, e.terrain_acres_expert, e.terrain_acres_advanced, e.terrain_acres_intermediate, e.terrain_acres_beginner, e.trails, e.terrain_parks, e.terrain_parks_acres, e.summit, e.base, e.vertical, e.longest_run, e.hours_open, e.hours_close, e.hours_notes, e.lifts_notes, e.tickets_notes, e.location_notes, e.location_link, e.zipcode, e.deaths_2007_2012,
		#d.open AS previous_open, d.numliftsopen AS previous_numliftsopen, d.numliftstotal AS previous_numliftstotal, d.numberofruns AS previous_numberofruns, d.acresopen AS previous_acresopen, d.basedepth_in AS previous_basedepth_in, d.topdepth_in AS previous_topdepth_in, d.newsnow_24_in AS previous_newsnow_24_in, d.newsnow_48_in AS previous_newsnow_48_in, d.newsnow_72_in AS previous_newsnow_72_in, d.conditions, d.kmxc AS previous_kmxc, d.eventnotices AS previous_eventnotices, d.baseweather AS previous_baseweather, d.basetemperature_f AS previous_basetemperature_f,
		ROUND((e.terrain_acres_expert/e.terrain_acres)*100) AS terrain_expert_pct, ROUND((e.terrain_acres_advanced/e.terrain_acres)*100) AS terrain_advanced_pct,  ROUND((e.terrain_acres_intermediate/e.terrain_acres)*100) AS terrain_intermediate_pct,  ROUND((e.terrain_acres_beginner/e.terrain_acres)*100) AS terrain_beginner_pct,
		s.name, s.slug, s.callaheadphone, s.projectedopeningdate, s.url, s.email, s.twitter, s.twitter_snow
	FROM report r, skiarea s, skiarea_extras e#, report_delta d
	WHERE
		e.skiarea_id = s.skiarea_id
		#AND d.skiarea_id = s.skiarea_id
		AND r.skiarea_id = s.skiarea_id
		#AND d.new = 0
        AND r.skiarea_id = %%id%%
	ORDER BY r.lastupdate DESC, r.timestamp DESC#, d.timestamp DESC
    LIMIT 1';

/*
 	SELECT
		r.skiarea_id,
		DATEDIFF(r.lastupdate, CURDATE()) AS lastupdate_diff,
		DATE_FORMAT(r.lastupdate, "%b. %e") AS lastupdate_str,
		DATE_FORMAT(r.timestamp, " at %h:%i %p") AS lastupdate_time_str,
        DATE_FORMAT(r.timestamp, "%a, %d %b %Y %H:%i:%s -0700") AS lastupdate_time_str_rss,
		UNIX_TIMESTAMP(r.timestamp) AS lastupdate_time_unix,
		DATEDIFF(s.projectedopeningdate, CURDATE()) AS opendate,
		DATE_FORMAT(s.projectedopeningdate, "%b. %e") AS opendate_str,
		#TIME_FORMAT(TIMEDIFF(NOW(), d.timestamp), "Updated about %k hour(s) ago") AS timeago,
        '&nbsp;' as timeago,
		r.lastupdate, r.newsnow_24_in, r.newsnow_48_in, r.newsnow_72_in, r.conditions, r.numliftsopen, r.numliftstotal, r.acresopen, r.kmxc, r.eventnotices, r.basedepth_in, r.topdepth_in, r.open, r.baseweather, r.basetemperature_f, r.numberofruns,
		e.phone, e.terrain_acres, e.terrain_acres_expert, e.terrain_acres_advanced, e.terrain_acres_intermediate, e.terrain_acres_beginner, e.trails, e.terrain_parks, e.terrain_parks_acres, e.summit, e.base, e.vertical, e.longest_run, e.hours_open, e.hours_close, e.hours_notes, e.lifts_notes, e.tickets_notes, e.location_notes, e.location_link, e.zipcode,
		ROUND((e.terrain_acres_expert/e.terrain_acres)*100) AS terrain_expert_pct, ROUND((e.terrain_acres_advanced/e.terrain_acres)*100) AS terrain_advanced_pct,  ROUND((e.terrain_acres_intermediate/e.terrain_acres)*100) AS terrain_intermediate_pct,  ROUND((e.terrain_acres_beginner/e.terrain_acres)*100) AS terrain_beginner_pct,
		s.name, s.slug, s.callaheadphone, s.projectedopeningdate, s.url, s.email
	FROM report r, skiarea s, skiarea_extras e
	WHERE
		e.skiarea_id = s.skiarea_id
		AND r.skiarea_id = s.skiarea_id
        AND r.skiarea_id = 507
	ORDER BY r.lastupdate DESC, r.timestamp DESC#, d.timestamp DESC
    LIMIT 1
*/
//$input['ids']['skiarea'] = array(20, 24, 25, 36, 77, 92, 113, 120, 143, 181, 197, 220, 240, 329, 330, 365, 372, 406, 425, 445, 456, 482, 507, 511, 809, 810, 814, 815, 816, 817, 818, 819, 820, 821, 822, 823, 824, 825, 826, 828, 829, 830, 1393, 1435, 1673);
$input['ids']['skiarea'] = array(20, 25, 36, 77, 113, 120, 143, 181, 197, 220, 240, 329, 330, 365, 372, 425, 445, 456, 482, 507, 511, 1435);


$input['powder']['sql'] = 'SELECT
		DISTINCT(r.skiarea_id),
		DATE_FORMAT(r.lastupdate, "%b. %e") AS lastupdate_str,
		DATE_FORMAT(r.timestamp, " at %h:%i %p") AS lastupdate_time_str,
		r.lastupdate, r.newsnow_24_in, r.conditions,
		s.name, s.slug
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND r.conditions LIKE "%Powder%"
		AND s.projectedopeningdate <> 0
		AND s.state_id = 8
        AND r.timestamp > CURDATE()
	GROUP BY r.skiarea_id
	ORDER BY r.lastupdate DESC';

$input['links']['sql'] = '
	SELECT
		s.skiarea_id, s.slug, s.url AS site_url,
		l.label, l.url
	FROM skiarea s, skiarea_links l
	WHERE
		s.skiarea_id = l.skiarea_id
	ORDER BY s.skiarea_id DESC';
$input['linkslugs']['sql'] = '
	SELECT
		DISTINCT s.skiarea_id, s.slug
	FROM skiarea s, skiarea_links l
	WHERE
		s.skiarea_id = l.skiarea_id
	ORDER BY s.slug DESC';



$input['webcams']['sql'] = '
	SELECT
		s.skiarea_id, s.slug, s.url AS site_url,
		w.title, w.desc, w.height, w.width, w.url
	FROM skiarea s, skiarea_webcams w
	WHERE
		s.skiarea_id = w.skiarea_id
	ORDER BY s.skiarea_id DESC';
$input['webcamslugs']['sql'] = '
	SELECT
		DISTINCT s.skiarea_id, s.slug, s.name
	FROM skiarea s, skiarea_webcams w
	WHERE
		s.skiarea_id = w.skiarea_id
	ORDER BY s.slug DESC';
?>
