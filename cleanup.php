<?
// +----------------------------------------------------------------------+
// | skireport / Cleanup                                                  |
// +----------------------------------------------------------------------+
// | Author: Joe Murphy <jmurphy@denverpost.com>                          |
// +----------------------------------------------------------------------+

if ( $_SERVER['SERVER_NAME'] == 'owsley'  || $_SERVER['SERVER_ADMIN'] == 'webmaster@localhost' )
{
	$db_url = "localhost";
	$db_userid = "root";
	$db_pwd = $_ENV['DB_PASS'];
	$db_dbname = "ski";
	$input['db'] = 'ski';
	$input['server'] = 'localhost';
	$input['username'] = 'root';
	$input['password'] = $_ENV['DB_PASS'];
	$dirpath = '/var/www/skireport/';
	$cachepath = '/var/www/skireport/cache/';
	$outputpath = '/root/';
}
else
{
	$db_url = "localhost";
	$db_userid = "db27949";
	$db_pwd = $_ENV['DB_PASS'];
	$db_dbname = "db27949_ski";
	$input['db'] = 'db27949_ski';
	$input['server'] = 'localhost';
	$input['username'] = 'db27949';
	$input['password'] = $_ENV['DB_PASS'];
	$dirpath = '/var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/';
	$cachepath = '/var/www/vhosts/denverpostplus.com/httpdocs/cache/';
	$outputpath = $dirpath;
}
require("/var/www/lib/class.db.php");
//require("/var/www/lib/class.page.php");
//require("/var/www/lib/class.ftp.php");

$db = new db($input['db'], $input);
$db->connect();
//$ftp = new ftp();
unset($input);


/*
Loop through the most-recent ids and check:
	1. To see if the most-recent record is identical to the previous record -- if it is identical, delete it, and if not see what the differences are.


*/

// Pull the list of recently-changed ids

$ids_file = file_get_contents($outputpath . 'ids.txt');
$ids = explode("\n", trim($ids_file));

/*
if ( isset($_GET['colorado']) )
{
	$ids = explode("\n", file_get_contents($outputpath . 'ids.colorado.txt'));
}
$ids = explode("\n", file_get_contents($outputpath . 'ids.colorado.txt'));
*/
foreach ( $ids as $key => $value )
{
	if ( $value != '' )
	{
		$diff_count = 0;

		$sql = 'SELECT * FROM report WHERE skiarea_id = ' . $value . ' ORDER BY timestamp DESC LIMIT 2';
		$result = $db->query($sql);
		//$row = $db->fetch($result);


		while ( $row = $db->fetch($result, 'assoc') )
		{
			$row_compare[] = $row;

	//		$$slug .= '	<dd><a href="' . $url . '" title="' . $label . '">' . $label . '</a></dd>

		}
		//$diff = array_diff_assoc($row_compare[0], $row_compare[1]);
		// row_compare[0] is the most-recent result
		foreach ( $row_compare[0] as $keyc => $valuec )
		{
			if ( $keyc != 'timestamp' && ( $valuec != $row_compare[1][$keyc] ) )
			{
				$keys[] = $keyc;
				$values['new'][] = ( is_int($valuec) ) ? $valuec : "'" . $valuec . "'";
				$values['old'][] = ( is_int($row_compare[1][$keyc]) ) ? $row_compare[1][$keyc] : "'" . $row_compare[1][$keyc] . "'";
				//echo "0: $valuec 1: " . $row_compare[1][$keyc] . "<br>";
				if ( $keyc != 'report_id' ) $diff_count ++;
			}
		}

		if ( $diff_count > 0 )
		{
			$sql_keys_bit = implode(', ', $keys);
			$sql_values_new_bit = implode(', ', $values['new']);
			$sql_values_old_bit = implode(', ', $values['old']);
			$sql_delta[] = 'INSERT INTO report_delta ( skiarea_id, new, ' . $sql_keys_bit . ' ) VALUES ( ' . $value . ', 0, ' . $sql_values_old_bit . ' ),
		(  ' . $value . ', 1, ' . $sql_values_new_bit . ' );
	';
		}
		else
		{
			$sql_delete[] = 'DELETE FROM report WHERE skiarea_id = ' . $value . ' ORDER BY timestamp DESC LIMIT 1;
	';
		}
		//echo $value . ': ' . $diff_count . '<hr>';
		unset($row_compare); unset($keys); unset($values);
		//var_dump($row_compare);
	}

}
//echo '<pre>' . $sql_delta;
//echo '<pre>';
//var_dump($sql_delete);
//echo '<hr>';
//var_dump($sql_delta);
// Add the changed records
if ( count($sql_delta) > 0 )
{
	foreach ( $sql_delta as $sql )
	{
		//echo $sql . "\n";
		$result = $db->query($sql);
	}
}
// Delete the false-positive records
if ( count($sql_delete) > 0 )
{
	foreach ( $sql_delete as $sql )
	{
		//echo $sql . "\n";
		$result = $db->query($sql);
	}
}

$db->close();
?>
