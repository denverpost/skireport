<?
// +----------------------------------------------------------------------+
// | skireport / Output                                                   |
// +----------------------------------------------------------------------+
// | Author: Joe Murphy <jmurphy@denverpost.com>                          |
// +----------------------------------------------------------------------+

/*
Command-line arguments:
php output.php $1 $2 $3
        $1: string: write('skiarea'/'links')
        $2: boolean: 'noftp'
var_dump($_SERVER['argv']);
die();
*/

require("output_functions.php");
require("output_constants.php");

$db = new db($input['db'], $input);
$db->connect();

// Hacky, for now
if ( isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'override' ) $input['ids']['skiarea'] = array(507);

if ( $_SERVER['argv'][1] == 'skiarea' ):

        //Loop through each skiarea
        foreach ( $input['ids']['skiarea'] as $id )
        {

                //Get the skiarea data
                $query = str_replace('%%id%%', "$id", $input['skiareaid']['sql']);
                $result = $db->query($query);
                while ( $row = $db->fetch($result) )
                {
                        if ( $row['lastupdate_diff'] == 0 ) $row['lastupdate_str'] = 'Today';

                        if ( $row['open'] == 'Open' ) $openstr = 'opened';
                        elseif ( trim($row['open']) == 'Call Ahead' ) { $openstr = 'callahead'; echo $row['slug'] . $newline; }
                        else $openstr = 'closed';


                        if ( $$row['slug'] == '' )
                        {
                                // This output goes to http://extras.denverpost.com/skireport, and to the RSS feeds
                                //<h5>' . $row['timeago'] . '</h5>


                                //if ( $$row['slug']['lastupdate_time_unix'] == '' )
                                //{
                                //        $timestamps['unix'][$row['slug']] = $row['lastupdate_time_unix'];
                                //}

                                $timestamps['unix'][$row['slug']] = $row['lastupdate_time_unix'];
                                $timestamps['rss'][$row['slug']] = $row['lastupdate_time_str_rss'];

                                $$row['slug'] = '
                                        <h3><a name="' . $row['slug'] . '" href="http://extras.denverpost.com/skireport/colorado/' . $row['slug'] . '.html">' . $row['name'] . ' Ski Resort Snow Report</a></h3>
                                        <h5>Last Updated: ' . $row['lastupdate_str'] . $row['lastupdate_time_str'] . '</h5>
                                        ' . ifequal($row['open'], 'Call Ahead', '<h5><a href="http://extras.denverpost.com/skireport/colorado/' . $row['slug'] . '.html#more_phone_' . $row['slug'] . '" title="Get ' . $row['name'] . '\'s phone number">Call ahead before going</a></h5>') . '
                                        ' . ifgreaterthan($row['opendate'], '<h5>Projected Open: ' . $row['opendate_str'] . ' ( in ' . $row['opendate'] . ' days )</h5>');

                                $$row['slug'] .= '
                                        <!-- <p class="twitter">On the Ski Report:<br><a name="' . $row['slug'] . '_twitter_snow" href="http://twitter.com/' . $row['twitter_snow'] . '">Get the ' . $row['name'] . ' Snow Report updates on twitter.</a> <br><em>&rsaquo; <a href="http://www.denverpost.com/twitter#snow_report">Full list of snow report twitterers.</a></em></p> -->';

                                // Logic to handle display of update data
                                // Not in use: Look in output_scratch.php for this if you want it back.
                                // END Logic to handle display of update data


                                //ROW Put together the resort's recent-snow data
                                unset($datatmp);
                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Past 24 hours',
                                        'value'     => $row['newsnow_24_in'] . '"'
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Past 48 hours',
                                        'value'     => $row['newsnow_48_in'] . '"'
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Past 72 hours',
                                        'value'     => $row['newsnow_72_in'] . '"'
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Base / Max Depth',
                                        'value'     => $row['basedepth_in'] . '" / ' . $row['topdepth_in'] . '"'
                                );
                                $datatmp .=  template($templateinput);

/*
                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Top Depth',
                                        'value'     => $row['topdepth_in'] . '"'
                                );
                                $datatmp .=  template($templateinput);
*/
                                if ($row['conditions'] != '' )
                                {
                                        unset($templateinput);
                                        $templateinput = array(
                                                'templatename'     => 'row.resort.html',
                                                'key'     => 'Conditions',
                                                'value'     => $row['conditions'],
                                                'styledd'     => ' style="clear:left;"'
                                                );
                                        $datatmp .=  template($templateinput);
                                }

                                //Take that data and put it in the resort's snow-list wrapper
                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'wrapper.resort.snow.html',
                                        'body'     => $datatmp,
                                        'openclosed'     => $openstr
                                );
                                $snowlist = template($templateinput);




                                //ROW Put together the resort's slope data
                                unset($datatmp);
                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Acres Open',
                                        'value'     => $row['acresopen'] . ' of ' . $row['terrain_acres']
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Lifts Open',
                                        'value'     => $row['numliftsopen'] . ifset($row['numliftstotal'], ' of %s')
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Runs',
                                        'value'     => $row['numberofruns'] . ' of ' . $row['trails']
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => '<a href="http://www.denverpost.com/ci_22820133/colorado-skiers-die-groomed-blue-runs-after-hitting">Slope Deaths, 2007-2012</a>',
                                        'value'     => $row['deaths_2007_2012'] 
                                );
                                $datatmp .=  template($templateinput);

                                if ( isset($row['baseweather']) )
                                {
                                        unset($templateinput);
                                        $templateinput = array(
                                                'templatename'     => 'row.resort.html',
                                                'key'     => '<!--Base -->' . $row['name'] . ' Weather',
                                                'value'     => $row['baseweather']
                                        );
                                        $datatmp .=  template($templateinput);
                                }
                                if ( isset($row['basetemperature_f']) )
                                {
                                        unset($templateinput);
                                        $templateinput = array(
                                                'templatename'     => 'row.resort.html',
                                                'key'     => 'Base Temperature',
                                                'value'     => $row['basetemperature_f'] . 'F'
                                        );
                                        $datatmp .=  template($templateinput);
                                }

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Vertical Drop',
                                        'value'     => $row['vertical'] . '\''
                                );
                                $datatmp .=  template($templateinput);

                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'row.resort.html',
                                        'key'     => 'Base / Summit',
                                        'value'     => $row['base'] . '\' / ' . $row['summit'] . '\''
                                );
                                $datatmp .=  template($templateinput);



                                //Take that data and put it in the resort's slope-list wrapper
                                unset($templateinput);
                                $templateinput = array(
                                        'templatename'     => 'wrapper.resort.slope.html',
                                        'body'     => $datatmp,
                                        'openclosed'     => $openstr
                                );
                                $slopelist = template($templateinput);


                                //ROW Resort "more" data -- phone, web site etc.

                                $webcams = webcams_get($row['slug']);

                                $$row['slug'] .= '
                                ' . $snowlist . '
                                ' . $slopelist . '
                                        <h4 class="more_link">&raquo; <a href="http://extras.denverpost.com/skireport/colorado/' . $row['slug'] . '.html#more_' . $row['slug'] . '">Get ' . $row['name'] . ' ski resort phone number, website, ticket info and directions</a></h4>';

                                if ( $webcams != '' )
                                {
                                        $$row['slug'] .= '
                                        <h4 class="more_link">&raquo; <a href="http://extras.denverpost.com/skireport/colorado/' . $row['slug'] . '.html#webcams_' . $row['slug'] . '">See ' . $row['name'] . ' webcams here</a></h4>';
                                }

                                $$row['slug'] .= '
                                        <div class="more">
                                                <h4 id="more"><a name="more_' . $row['slug'] . '"></a>More about the ' . $row['name'] . ' Colorado Ski Resort</h4>
                                                <dl>
                                                        ' . ifset($row['url'], '<dt><a name="more_website_' . $row['slug'] . '"></a>Web Site:</dt><dd><a href="%s">%s</a></dd>') . '
                                                        ' . ifset($row['phone'], '<dt><a name="more_phone_' . $row['slug'] . '"></a>Phone:</dt><dd>%s</dd>') . '
                                                        ' . ifset($row['tickets_notes'], '<!--<dt><a name="more_ticketinfo_' . $row['slug'] . '"></a>Ticket Info:</dt><dd style="clear:left;">%s</dd>-->') . '
                                                        ' . ifset($row['location_notes'], '<dt><a name="more_directions_' . $row['slug'] . '"></a>Getting There:</dt><dd style="clear:left;">%s ' . ifset($row['location_link'], '<br><strong><a href="%s">View a map to ' . $row['name'] . ' here</a></strong>') . '</dd>') . '
                                                        <dt><a name="more_links_' . $row['slug'] . '"></a>More Info:</dt>
                                                        ' . links_get($row['slug']) . '
                                                </dl>
                                        </div>


                                        <div class="webcams">' . $webcams . '</div>';

                                //Add this data to the output-by-opened-or-not (this output goes to http://www.denverpost.com/skireport)
                                $$openstr .= $$row['slug'];
                        }
                        unset($row);
                }
        }


        //DETAIL-SCRAPE: Write the opened-or-not outputs
        // We segregate the two into separate files for use on
        // http://denverpost.com/skireport
        $page = new page($cachepath, 'opened.html', '<div class="skireport"><h2 id="opened" class="img">Open Colorado Resorts</h2>' . $opened . '</div>');
        $page -> write();
        unset($page);
        $page = new page($cachepath, 'callahead.html', '<div class="skireport"><h2 id="callahead" class="img">"Call Ahead" Colorado Resorts</h2>' . $callahead . '</div>');
        $page -> write();
        unset($page);



        //DETAIL-FULL: Add the wrapper for each set, write it to a file
        //		DATE_FORMAT(r.timestamp, \'%a, %e %b %Y %H:%i:%s MDT\') AS pubdate
        $input['slugs']['sql'] = '
        SELECT
                DISTINCT s.skiarea_id, s.slug, s.name
        FROM skiarea s, report r
        WHERE
                r.skiarea_id = s.skiarea_id
                AND s.skiarea_id NOT IN (24,92,406)
                AND s.state_id = 8
        ORDER BY s.slug, r.lastupdate DESC, r.timestamp DESC';
        $result = $db->query($input['slugs']['sql']);
        while ( $row = $db->fetch($result) )
        {
                extract($row);
                unset($templateinput);

                /* Want to get the last-updated time for the template.
                $input['slugs']['lastupdate'] = '
                SELECT
                        r.lastupdate, r.timestamp
                FROM report r
                WHERE
                        r.skiarea_id = $skiarea_id
                ORDER BY r.lastupdate DESC, r.timestamp DESC
                LIMIT 1';
                $result = $db->query($input['slugs']['lastupdate']);
                */

                // We're going to do some fullpage-specific parsing of
                // the  content here -- some of the markup here is specific
                // to the http://denverpost.com/skireport page, and doesn't
                // make sense to keep around here.
                $body = str_replace('<h3><a name="' . $slug . '" href="http://extras.denverpost.com/skireport/colorado/' . $slug . '.html">' . $name . ' Ski Resort Snow Report</a></h3>', '<h1><a name="' . $slug . '"></a>' . $name . ' Ski Resort Snow Report</h1>', $$slug);

                //echo $slug . $$slug['timeago'] . "\n";


                $templateinput = array(
                        'body'      => '<div class="skireport fullpage">' . $body . '</div><div style="clear:both;">&nbsp;</div>',
                        'title'     => $name,
                        'titleblurb'     => '',	//Used for RSS
                        'slug'     => $slug,
                        //'timeago'     => $$slug['timeago'],
                        'templatename'     => 'page.html',
                        'filename'     => ''
                );
                $output_html =  template($templateinput);

                //Write the HTML
                $page = new page($cachepath, $slug . '.html', $output_html);
                $page -> write();
                unset($page);

                //Write the XML
                //First parse out all the resort data information
                $skiarea_data = preg_replace('/<div class="more">.*<\/div>/ims', '', $$slug);
                unset($templateinput);
                $templateinput = array(
                        'body'      => $skiarea_data,
                        'title'     => $name,
                        'titleblurb'     => $name . ' snow report',
                        'slug'     => $slug,
                        'templatename'     => 'rss.xml',
                        'filename'     => '',
                        'pubdate'     => $timestamps['rss'][$slug],
                        'unixtime'      => $timestamps['unix'][$slug]
                );
                $output_xml = template($templateinput);
                $page = new page($cachepath, 'ski_area_' . $slug . '.xml', $output_xml);
                $page -> write();
                unset($page);

                // Write the stripped-down-for-twitter XML -- right now that's
                // just the snowfall in the past 24 hours.
                // First parse out all the resort data information
                preg_match('/<dd>([0-9]+)"<\/dd>/ims', $skiarea_data, $matches);
                $newsnow = $matches[1];
                if ( intval($newsnow) > 0 )
                {
                        unset($templateinput);
                        $inch_str = ( intval($newsnow) == 1 ) ? 'inch' : 'inches';
                        $templateinput = array(
                                'body'      => $matches[1] . ' ' . $inch_str . ' of snow in the last 24 hours.',
                                'title'     => $name,
                                'titleblurb'     => $name . ' snow report',
                                'slug'     => $slug,
                                'templatename'     => 'rss-for-twitter.xml',
                                'filename'     => '',
                                'pubdate'     => $timestamps['rss'][$slug],
                                'unixtime'      => $timestamps['unix'][$slug]
                        );
                        $output_xml = template($templateinput);

                        $page = new page($cachepath, 'ski_area_' . $slug . '_newsnow.xml', $output_xml);
                        $page -> write();
                        unset($page);
                }

                //FTP the XML, then the HTML, to extras
                if ( $ftp_action != FALSE )
                {
                        $error_report = TRUE;
                        $file_directory_local = '/var/www/vhosts/denverpostplus.com/httpdocs/cache';
                        $file_format = 'xml';
                        $ftp -> file_put('ski_area_' . $slug, $file_directory_local, $file_format, $error_report, FTP_ASCII);

                        if ( intval($newsnow) > 0 )
                        {
                                $ftp -> file_put('ski_area_' . $slug . '_newsnow', $file_directory_local, $file_format, $error_report, FTP_ASCII);
                        }

                        $file_format = 'html';
                        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_report, FTP_ASCII, '/DenverPost/skireport/colorado/');
                }
        }

endif;



//INDEX Recent Updates
/*
$output_mostsnow['recentupdate']['body'] = '<div>' . recentupdate_output() . '</div>';
$output_recentupdate_final = '<div class="recentupdate skireport"><h3>Recent Updates</h3>' . $output_mostsnow['recentupdate']['body'] . '
<!-- <h4 id="more_link">&raquo; <a href="http://extras.denverpost.com/skireport/colorado/deepestsnow.html">Get the full Deepest Snow report here</a></h4> -->
</div>';
//INDEX-SCRAPE
$page = new page($cachepath, 'ski_recentupdate.html', $output_recentupdate_final);
$page -> write();
//INDEX-FULL Write the full-bodied version for transport to extras
$input = array(
    'body'      => '<div class="skireport fullpage recentupdatepage">' . $output_recentupdate_final . '</div>',
    'title'     => 'Recent Updates',
);
$page -> write($cachepath, 'recentupdate.html', template($input));
unset($page);
*/






// POWDER is handled differently
/*
if ( $_SERVER['argv'][1] == 'powder'  )
{
        //Get the data
        $result = $db->query($input['powder']['sql']);
        $content = '';

        while ( $row = $db->fetch($result) )
        {
                extract($row);
                if ( $width >= 640 )
                {
                        $width = $width / 2;
                        $height = $height / 2;
                }
                $$slug .= '	<div class="webcam_item"><h5>' . $title . ' WebCam</h5><img src="' . $url . '" width="' . $width . '" height="' . $height . '" alt="' . $title . '" /></div>
        ';
                echo $slug;
        }

        //Add the wrapper for each set, write it to a file
        $result = $db->query($input['webcamslugs']['sql']);
        while ( $row = $db->fetch($result) )
        {
                extract($row);

                //$$slug = '<ul class="links">' . $$slug . '</ul>';
                // Write the title
                $$slug = '<h4><a name="webcams_' . $slug . '"></a>WebCams at ' . $name . '</h4>' . $$slug;
                //Write it
                $page = new page($cachepath, 'ski_powder_colorado.html', $$slug);
                $page -> write();
                unset($page);
        }
}
*/

// WEBCAMS are handled differently: They're static so we're writing them to a file that gets included
if ( $_SERVER['argv'][1] == 'webcams'  )
{
        //Get the data
        $result = $db->query($input['webcams']['sql']);

        while ( $row = $db->fetch($result) )
        {
                extract($row);
                if ( $width >= 640 )
                {
                        $width = $width / 2;
                        $height = $height / 2;
                }
                if ( $desc != '' )
                {
                        $description = '<p>' . $desc . '</p>';
                }
                else
                {
                        $description = '';
                }
                $$slug .= '	<div class="webcam_item"><h5>' . $title . ' WebCam</h5><img src="' . $url . '" width="' . $width . '" height="' . $height . '" alt="' . $title . '" />' . $description . '</div>
        ';
                echo $slug;
        }

        //Add the wrapper for each set, write it to a file
        $result = $db->query($input['webcamslugs']['sql']);
        while ( $row = $db->fetch($result) )
        {
                extract($row);

                //$$slug = '<ul class="links">' . $$slug . '</ul>';
                // Write the title
                $$slug = '<h4><a name="webcams_' . $slug . '"></a>WebCams at ' . $name . '</h4>' . $$slug;
                //Write it
                $page = new page($cachepath, 'ski_webcams_' . $slug . '.html', $$slug);
                $page -> write();
                unset($page);
        }
}


// LINKS Links are handled differently: They're static so we're writing them to a file that gets included
if ( $_SERVER['argv'][1] == 'links'  )
{
        //Get the data
        $result = $db->query($input['links']['sql']);
        while ( $row = $db->fetch($result) )
        {
                extract($row);
                $url = str_replace('&', '&amp;', $url);
                $url = str_replace('&amp;amp;', '&amp;', $url);
                $$slug .= '	<dd><a href="' . $url . '" title="' . $label . '">' . $label . '</a></dd>
        ';
        }

        //Add the wrapper for each set, write it to a file
        $result = $db->query($input['linkslugs']['sql']);
        while ( $row = $db->fetch($result) )
        {
                extract($row);
                //$$slug = '<ul class="links">' . $$slug . '</ul>';

                //Write it
                $page = new page($cachepath, 'ski_links_' . $slug . '.html', $$slug);
                $page -> write();
                unset($page);
        }
}



/*
INDEX Recent Snow Output
*/
$snow = array(24, 48, 72);
foreach ( $snow as $key => $value )
{
    $output_recentsnow[$value]['head'] = '<div><h4 class="img" id="hours' . $value . '">Past ' . $value . ' Hours</h4>';
    $output_recentsnow[$value]['body'] = '<dl>' . db_output($value, 'newsnow') . '</dl></div>';
}

unset($input);
$input = array(
    'body'      => $output_recentsnow[24]['head'] . $output_recentsnow[24]['body'] . $output_recentsnow[48]['head'] . $output_recentsnow[48]['body'] . $output_recentsnow[72]['head'] . $output_recentsnow[72]['body'],
    'title'     => '',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'wrapper.recentsnow.html',
    'filename'     => '',
    'pubdate'     => '',
);
$output_recentsnow_final = template($input);
//INDEX-SCRAPE
$page = new page($cachepath, 'ski_recentsnow.html', $output_recentsnow_final);
$page -> write();

//INDEX-FULL Write the full-bodied version for transport to extras
unset($input);
$input = array(
    'body'      => '<div class="skireport fullpage mostsnowpage">' . $output_recentsnow_final . '</div>',
    'title'     => 'Recent Colorado Snow and Snowfall',
    'headerone'      => '<div class="fullpage"><h1>Colorado Ski Resort Recent Snow and Snowfall Report</h1></div>',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'page.html',
    'filename'     => '',
    'pubdate'     => '',
);
$page -> write($cachepath, 'recentsnow.html', template($input));

//INDEX-XML Write the XML for transport to extras
$output = preg_replace('/<h4 id="more_link">.*<\/h4>/ims', '', $output_recentsnow_final);

unset($input);
$input = array(
    'body'      => $output,
    'title'     => 'Recent Snow',
    'titleblurb'     => 'Slope Report: ',
    'slug'     => 'recentsnow',
    'templatename'     => 'rss.xml',
    'filename'     => '',
    'pubdate'     => $date_now,
);
$output_xml = template($input);
$page -> write($cachepath, 'recentsnow.xml', $output_xml);
unset($page);





//INDEX Build a list for acres open
//INDEX-SCRAPE
unset($input);
$output_mostsnow['acresopen']['body'] = '<div><dl>' . db_output('', 'acresopen') . '</dl></div>';
$input = array(
    'body'      => '<div class="skireport fullpage acresopenpage">' . $output_mostsnow['acresopen']['body'] . '</div>',
    'title'     => 'Acres Open',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'wrapper.acresopen.html',
    'filename'     => '',
    'pubdate'     => '',
);
$fullpage = template($input);

$page = new page($cachepath, 'ski_acresopen.html', $fullpage);
$page -> write();

//INDEX-FULL Write the full-bodied version for transport to extras
unset($input);
$input = array(
    'body'      => $fullpage,
    'title'     => 'Acres Open',
    'headerone'      => '<div class="fullpage"><h1>Colorado\'s Ski Resort Acres Open Report</h1></div>',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'page.html',
    'filename'     => '',
    'pubdate'     => '',
);
$page -> write($cachepath, 'acresopen.html', template($input));

//INDEX-XML Write the XML for transport to extras
$output = preg_replace('/<h4 id="more_link">.*<\/h4>/ims', '', $output_acresopen_final);

unset($input);
$input = array(
    'body'      => $output,
    'title'     => 'Acres Open',
    'titleblurb'     => 'Slope Report: ',
    'slug'     => 'acresopen',
    'templatename'     => 'rss.xml',
    'filename'     => '',
    'pubdate'     => $date_now,
);
$output_xml = template($input);
$page -> write($cachepath, 'acresopen.xml', $output_xml);

unset($page);



//INDEX Build a list for the most at base and top depths
$output_mostsnow['basetop']['head'] = '<div><h4>Base / Top Snow Depth</h4>';
$output_mostsnow['basetop']['body'] = '<dl>' . db_output('', 'basetop') . '</dl></div>';

unset($input);
$input = array(
    'body'      => $output_mostsnow['basetop']['head'] . $output_mostsnow['basetop']['body'],
    'title'     => 'Colorado\'s Deepest Snow',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'wrapper.deepestsnow.html',
    'filename'     => '',
    'pubdate'     => '',
);
//INDEX-SCRAPES
$fullpage = template($input);
$page = new page($cachepath, 'ski_deepestsnow.html', $fullpage);
$page -> write();

//INDEX-FULL Write the full-bodied version for transport to extras
unset($input);
$input = array(
    'body'      => '<div class="skireport fullpage deepestsnowpage">' . $fullpage . '</div>',
    'headerone'      => '<div class="fullpage"><h1>Colorado\'s Deepest Snow Report</h1></div>',
    'title'     => 'Colorado\'s Deepest Snow',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'page.html',
    'filename'     => '',
    'pubdate'     => '',
);
$page -> write($cachepath, 'deepestsnow.html', template($input));

//INDEX-XML Write the XML for transport to extras
$output = preg_replace('/<h4 id="more_link">.*<\/h4>/ims', '', $output_deepestsnow_final);

unset($input);
$input = array(
    'body'      => $output,
    'title'     => 'Deepest Snow',
    'titleblurb'     => 'Slope Report: ',
    'slug'     => 'deepestsnow',
    'templatename'     => 'rss.xml',
    'filename'     => '',
    'pubdate'     => $date_now,
);
$output_xml = template($input);
$page -> write($cachepath, 'deepestsnow.xml', $output_xml);

unset($page);




//INDEX Build a list for the powder report
$output_mostsnow['powder']['head'] = '<div style="width:600px!important;"><h4>Colorado Powder</h4>';
$output_mostsnow['powder']['body'] = '<dl>' . db_output('', 'powder') . '</dl></div>';

unset($input);
$input = array(
    'body'      => $output_mostsnow['powder']['head'] . $output_mostsnow['powder']['body'],
    'title'     => 'The Colorado Powder Report',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'wrapper.powder.html',
    'filename'     => '',
    'pubdate'     => '',
);
//INDEX-SCRAPES
$fullpage = template($input);
$page = new page($cachepath, 'ski_powder_colorado.html', $fullpage);
$page -> write();

//INDEX-FULL Write the full-bodied version for transport to extras
unset($input);
$input = array(
    'body'      => '<div class="skireport fullpage powderpage">' . $fullpage . '</div>',
    'headerone'      => '<div class="fullpage"><h1>The Colorado Powder Report</h1></div>',
    'title'     => 'The Colorado Powder Report',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'page.html',
    'filename'     => '',
    'pubdate'     => '',
);
$page -> write($cachepath, 'powder.html', template($input));



//INDEX Build a list for the resorts list
$output_mostsnow['resorts']['head'] = '<div style="width:600px!important;"><h4>Colorado Ski Resorts</h4>';
$output_mostsnow['resorts']['body'] = '<dl>' . db_output('', 'resorts') . '</dl></div>';

unset($input);
$input = array(
    'body'      => $output_mostsnow['resorts']['head'] . $output_mostsnow['resorts']['body'],
    'title'     => 'Colorado Ski Resorts',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'wrapper.resorts.html',
    'filename'     => '',
    'pubdate'     => '',
);
//INDEX-SCRAPES
$fullpage = template($input);
$page = new page($cachepath, 'ski_resorts_colorado.html', $fullpage);
$page -> write();

//INDEX-FULL Write the full-bodied version for transport to extras
unset($input);
$input = array(
    'body'      => '<div class="skireport fullpage resortspage">' . $fullpage . '</div>',
    'headerone'      => '<div class="fullpage"><h1>Colorado Ski Resorts</h1></div>',
    'title'     => 'Colorado Ski Resorts',
    'titleblurb'     => '',
    'slug'     => '',
    'templatename'     => 'page.html',
    'filename'     => '',
    'pubdate'     => '',
);
$page -> write($cachepath, 'resorts.html', template($input));
//INDEX-XML Write the XML for transport to extras
/*
$output = preg_replace('/<h4 id="more_link">.*<\/h4>/ims', '', $output_deepestsnow_final);

unset($input);
$input = array(
    'body'      => $output,
    'title'     => 'Deepest Snow',
    'titleblurb'     => 'Slope Report: ',
    'slug'     => 'deepestsnow',
    'templatename'     => 'rss.xml',
    'filename'     => '',
    'pubdate'     => $date_now,
);
$output_xml = template($input);
$page -> write($cachepath, 'deepestsnow.xml', $output_xml);
*/
unset($page);




//FTP the HTML to extras
if ( $ftp_action != FALSE )
{
        $file_directory_local = '/var/www/vhosts/denverpostplus.com/httpdocs/cache';
        $file_format = 'html';
        $error_display = TRUE;
        $slug = 'deepestsnow';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        //report($input, $type)
        $slug = 'recentsnow';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        $slug = 'acresopen';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        $slug = 'powder';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        $slug = 'resorts';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');

        $file_format = 'xml';
        $slug = 'deepestsnow';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        $slug = 'recentsnow';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');
        $slug = 'acresopen';
        $ftp -> file_put($slug, $file_directory_local, $file_format, $error_display, FTP_ASCII, '/DenverPost/skireport/colorado/');

        $ftp -> ftp_connection_close();
}

$db->close();
?>
