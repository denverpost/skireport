<?php
function template($input, $resortdata = '')
{
/*
    This function takes the input values and returns a whole page (as a string),
    ready for writing to a file and ftp'ing somewhere.

    It gets used to create the html pages that live on extras.denverpost.com, and the
    xml files that get scraped by feedburner.

$input = array(
	'body'      => '',
	'title'     => '',
	'titleblurb'     => '',	//Used for RSS
	'slug'     => '',
	'templatename'     => '',
	'filename'     => '',
	'pubdate'     => '',	//Used for RSS
	//Used in the snow amount row templates
	'type'	=> $type,
	'evenodd'	=> $evenodd,
	'fieldname'	=> trim($row['name']),
	'snowamount'	=> trim($row['snowamount']),
	'word_inches'	=> $word_inches,
	'count'	=> $i,
);
*/
        $title = '';
        $titleblurb = '';
        $slug = '';
        $body = '';
        $feedlink = '';
        $static['recentupdates'] = '';
        $pubdate = '';
        $unixtime = '';
        $type = '';
        $evenodd = '';
        $fieldname = '';
        $snowamount = '';
        $word_inches = '';
        $count = '';
        $key = '';
        $value = '';
        $styledd = '';
        $headerone = '';
        $format = '';

	global $cachepath;
	extract($input);

    if ( $slug != '' ) $feedlink = '
<link rel="alternate" type="application/rss+xml"  description="' . $title . ' Ski Report"  href="http://feeds.denverpost.com/dp-skireport-' . $slug . '"   title="' . $title . ' Ski Report" text="' . $title . ' Ski Report">';
    if ( isset($filename) && $filename == 'page.html' ) $static['recentupdates'] = file_get_contents($cachepath .'/ski_recentupdate.html');

    $template['search'] = array(
        'TITLE',
        'TITLEBLURB',
        'SLUG',
        'BODY',
        'FEEDLINK',
        'STATIC.RECENTUPDATES',
        'PUBDATE',
        'UNIXTIME',
        'TIMESTAMP',
        'TYPE',
        'EVENODD',
        'FIELDNAME',
        'SNOWAMOUNT',
        'WORD_INCHES',
        'COUNT',
        'KEY',
        'VALUE',
        'STYLEDD',
        'TIMEAGO',
        'HEADERONE',
        'OPENCLOSED',
        'SNOW-WIDGET',
        //DETAIL - Resort
    );
    $template['replace'] = array(
        $title,
        $titleblurb,
        $slug,
        $body,
        $feedlink,
        $static['recentupdates'],
        $pubdate,
        $unixtime,
        time(),
        $type,
        $evenodd,
        $fieldname,
        $snowamount,
        $word_inches,
        $count,
        $key,
        $value,
        $styledd,
        '', //$timeago,
        $headerone,
        '', //$openclosed,
        file_get_contents('content/snow-widget.html'),
        //DETAIL - Resort
    );

	switch ( $format )
	{
		case 'html':
			$template['file'] = 'page.html';
			break;
		case 'xml':
			$template['file'] = 'rss.xml';
			break;
		default:
			$template['file'] = $templatename;
			break;
	}

    $template['content'] = file_get_contents('template/' . $template['file']);
    array_walk($template['search'], 'array_add_str');
    $return = str_replace($template['search'], $template['replace'], $template['content']);

	return $return;
}

function array_add_str(&$item)
{
	if ( $item != '' ) $item  = '{{' . $item . '}}';
}

function ifset($var, $wrapper)
{
	if ( $var != '' ) return str_replace('%s', $var, $wrapper);
}

function ifgreaterthan($var, $wrapper)
{
	if ( $var > 0 ) return $wrapper;
}

function ifequal($var, $var_value, $wrapper)
{
	if ( $var == $var_value ) return $wrapper;
}

function date_compare($var, $wrapper)
{
	if ( $var != '' ) return str_replace('%s', $var, $wrapper);
}

function links_get($slug)
{
	if ( file_exists('/var/www/vhosts/denverpostplus.com/httpdocs/cache/ski_links_' . $slug . '.html') == TRUE ) return file_get_contents('/var/www/vhosts/denverpostplus.com/httpdocs/cache/ski_links_' . $slug . '.html');
}

function webcams_get($slug)
{
	if ( file_exists('/var/www/vhosts/denverpostplus.com/httpdocs/cache/ski_webcams_' . $slug . '.html') == TRUE ) return file_get_contents('/var/www/vhosts/denverpostplus.com/httpdocs/cache/ski_webcams_' . $slug . '.html');
}







// Recent Updates
function recentupdate_output()
{
	global $db;

	$sql = '
	SELECT
		TIME_FORMAT(TIMEDIFF(NOW(), d.timestamp), \'Updated about %k hour(s) ago\') AS timeago,
		s.shortname AS name, s.slug,
		d.open, d.numliftsopen, d.numliftstotal, d.numberofruns, d.acresopen, d.basedepth_in, d.topdepth_in, d.newsnow_24_in, d.newsnow_48_in, d.newsnow_72_in, d.conditions, d.kmxc, d.eventnotices, d.baseweather, d.basetemperature_f
	FROM report_delta d, skiarea s
	WHERE
		d.skiarea_id = s.skiarea_id
		AND TIMEDIFF(NOW(), d.timestamp) < 24
		AND s.state_id = 8
	ORDER BY d.timestamp DESC
	';

	$result = $db->query($sql);
	$i = 0;
	// Put the results into an array for further processing
	while ( $row = $db->fetch($result, 'assoc') )
	{
		$slug = $row['slug'];
		// This logic prevents the same resort appearing multiple times.
		// The same resort would appear multiple times in cases where multiple updates where made to a particular resort's snow data on the same day.
		if ( $$slug < 2 ) { $compare[$i] = $row; $i++; }
		$$slug ++;

	}
	$count = count($compare);

	// Filter out the fields in the results that are the same
	for ( $i = 0; $i < $count; $i = $i + 2 )
	{
		$diff[$i]['name'] = $compare[$i]['name'];
		$diff[$i]['slug'] = $compare[$i]['slug'];
		$diff[$i]['timeago'] = $compare[$i]['timeago'];
		$diff[$i]['old'] = array_diff_assoc($compare[$i], $compare[$i + 1]);
		$diff[$i]['new'] = array_diff_assoc($compare[$i + 1], $compare[$i]);
	}

	// Process the data -- we should have an array with the resort name, slug, how long ago it was updated, and two arrays inside that, one with all the old values and one with the news.
	foreach ( $diff as $key => $value )
	{
		// This filters out the results that just changed something like their last-updated date and didn't change any real information.
		if ( count($value['old']) > 0 )
		{
			$return .= '<h4><a href="http://extras.denverpost.com/skireport/colorado/' . $value['slug'] . '.html">' . $value['name'] . ' Ski Resort Information</a></h4>
';
			$return .= '<p>' . $value['timeago'] . '</p>';

			$return .= '<dl>';
			foreach ( $value['old'] as $key_change => $value_change )
			{
				switch ( $key_change )
				{
					case 'lastupdate':
						$return .= '<dt>Last Update:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'newsnow_24_in':
						$return .= '<dt>New Snow, 24 Hours:</dt><dd style="clear:left;">Was ' . $value_change . '", now is ' . $value['new'][$key_change] . '"</dd>
	';
						break;
					case 'newsnow_48_in':
						$return .= '<dt>New Snow, 48 Hours:</dt><dd style="clear:left;">Was ' . $value_change . '", now is ' . $value['new'][$key_change] . '"</dd>
	';
						break;
					case 'newsnow_72_in':
						$return .= '<dt>New Snow, 72 Hours:</dt><dd style="clear:left;">Was ' . $value_change . '", now is ' . $value['new'][$key_change] . '"</dd>
	';
						break;
					case 'conditions':
						$return .= '<dt>Conditions (base, top):</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'numliftsopen':
						$return .= '<dt>Lifts Open:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'numliftstotal':
						$return .= '<dt>Lifts Total:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'acresopen':
						$return .= '<dt>Acres Open:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'kmxc':
						$return .= '<dt>Cross Country Open:</dt><dd style="clear:left;">Was ' . $value_change . ' km, now is ' . $value['new'][$key_change] . ' km</dd>
	';
						break;
					case 'eventnotices':
						$return .= '<dt>Event Notices:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'basedepth_in':
						$return .= '<dt>Base Depth:</dt><dd style="clear:left;">Was ' . $value_change . '", now is ' . $value['new'][$key_change] . '"</dd>
	';
						break;
					case 'topdepth_in':
						$return .= '<dt>Top Depth:</dt><dd style="clear:left;">Was ' . $value_change . '", now is ' . $value['new'][$key_change] . '"</dd>
	';
						break;
					case 'open':
						$return .= '<dt>Open:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '!</dd>
	';
						break;
					case 'baseweather':
						$return .= '<dt>Base Weather:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
					case 'basetemperature_f':
						$return .= '<dt>Base Temperature:</dt><dd style="clear:left;">Was ' . $value_change . ' f, now is ' . $value['new'][$key_change] . ' f</dd>
	';
						break;
					case 'numberofruns':
						$return .= '<dt>Number of Runs:</dt><dd style="clear:left;">Was ' . $value_change . ', now is ' . $value['new'][$key_change] . '</dd>
	';
						break;
				}
			}
			$return .= '</dl>';
		}
	}
	return $return;
}





function db_output($amount = '', $type = 'newsnow', $verbose = FALSE)
{
/*
	This function figures out which of the main snow-amount output queries
	we want to run, runs it, and then returns the output as HTML
	(which is generated via the template() function).
*/
	global $db;

	switch ( $type )
	{
		case 'resorts':
			$sql = '
SELECT
		DISTINCT(r.skiarea_id),
		CONCAT("Updated ", DATE_FORMAT(r.lastupdate, "%b. %e"),	DATE_FORMAT(r.timestamp, " at %h:%i %p; "), r.conditions, " conditions") as snowamount,
		r.lastupdate, r.newsnow_24_in,
		s.name, s.slug
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND s.state_id = 8
		AND s.projectedopeningdate <> 0
        AND r.timestamp > CURDATE()
	GROUP BY r.skiarea_id
	ORDER BY s.name ASC';
			break;

		case 'powder':
			$sql = '
SELECT
		DISTINCT(r.skiarea_id),
        CONCAT(r.conditions, " (Updated ", DATE_FORMAT(r.lastupdate, "%b. %e"),	DATE_FORMAT(r.timestamp, " at %h:%i %p"), ")") as snowamount,
		DATE_FORMAT(r.lastupdate, "%b. %e") AS lastupdate_str,
		DATE_FORMAT(r.timestamp, " at %h:%i %p") AS lastupdate_time_str,
		r.lastupdate, r.newsnow_24_in,
		s.name, s.slug
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND s.state_id = 8
		AND r.conditions LIKE "%Powder%"
		AND s.projectedopeningdate <> 0
        AND r.timestamp > CURDATE()
	GROUP BY r.skiarea_id
	ORDER BY r.lastupdate DESC';
			break;

		case 'newsnow':
			$sql = '
	SELECT
		DISTINCT(s.shortname) AS name, s.slug,
		r.newsnow_' . $amount . '_in AS snowamount
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND s.state_id = 8
		AND r.newsnow_' . $amount . '_in >= 0
		AND r.lastupdate >= DATE_SUB(CURDATE(), INTERVAL 0 DAY)
	ORDER BY r.newsnow_' . $amount . '_in DESC';
			break;

		case 'acresopen':
			$sql = '
	SELECT
		DISTINCT(s.shortname) AS name, s.slug,
		r.acresopen AS snowamount
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND s.state_id = 8
		AND r.acresopen > 0
		AND r.lastupdate >= DATE_SUB(CURDATE(), INTERVAL 0 DAY)
	ORDER BY r.acresopen DESC';
			break;

		case 'basedepth':
		case 'topdepth':
			$sql = '
	SELECT
		DISTINCT(s.shortname) AS name, s.slug,
		r.' . $type . '_in AS snowamount
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND s.state_id = 8
		AND r.open = "Open"
		AND r.' . $type . '_in > 0
		AND r.lastupdate >= DATE_SUB(CURDATE(), INTERVAL 0 DAY)
	ORDER BY r.' . $type . '_in DESC';
			break;

		case 'basetop':
			$sql = '
	SELECT
		DISTINCT(s.shortname) AS name, s.slug,
		CONCAT(r.basedepth_in, "\"&nbsp;/&nbsp;", r.topdepth_in) AS snowamount,
		( r.basedepth_in + r.topdepth_in ) AS totalamount
	FROM report r, skiarea s
	WHERE
		r.skiarea_id = s.skiarea_id
        AND s.skiarea_id NOT IN (24,92,406)
		AND r.open = "Open"
		AND r.lastupdate >= DATE_SUB(CURDATE(), INTERVAL 0 DAY)
		AND s.state_id = 8
	ORDER BY r.basedepth_in + r.topdepth_in DESC';
			break;
	}

	if ( $verbose == TRUE )
	{
		echo $sql;
	}

	$result = $db->query($sql);
	$i = 0;
	while ( $row = $db->fetch($result) )
	{
		if ( $i == 0 && $type != 'acresopen' ) $word_inches = '&nbsp;inches';
		else $word_inches = '';
		if ( $type != 'acresopen' ) $word_inches = '"';
        if ( $type == 'powder' || $type == 'resorts' ) $word_inches = '';
		$i++;

		// This logic prevents the same resort appearing multiple times.
		// The same resort would appear multiple times in cases where multiple updates where made to a particular resort's snow data on the same day.
		if ( $$row['slug'] != TRUE )
		{
			$evenodd = ( $evenodd == 'odd' ) ? 'even' : 'odd';
			unset($input);
			$input = array(
			    'body'      => '',
			    'title'     => '',
			    'titleblurb'     => '',
			    'slug'     => $row['slug'],
			    'templatename'     => 'row.snowamount.html',
			    'filename'     => '',
			    'pubdate'	=> '',
			    'type'	=> $type,
			    'evenodd'	=> $evenodd,
			    'fieldname'	=> trim($row['name']),
			    'snowamount'	=> trim($row['snowamount']),
			    'word_inches'	=> $word_inches,
			    'count'	=> $i,
			);

            if ( $type == 'newsnow' )
            {
                $input['templatename'] = 'row.snowamount.reverse.html';
            }

			$output .= template($input);
		}
		$$row['slug'] = TRUE;
	}

	return $output;
}

function report($input, $type)
{
        switch ( $type )
        {
                case 'link':
                        return 'http://extras.denverpost.com/skireport/colorado/' . $input . '.html' . "\n";
        }
}
?>
