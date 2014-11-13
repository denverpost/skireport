<?
// +----------------------------------------------------------------------+
// | skireport / Cleanup                                                  |
// +----------------------------------------------------------------------+
// | Author: Joe Murphy <jmurphy@denverpost.com>                          |
// +----------------------------------------------------------------------+

include('output_constants.php');

$db = new db($input['db'], $input);
$db->connect();
unset($input);


/*
Loop through the most-recent ids and check:
	1. To see if the most-recent record is identical to the previous record
    If it is identical, delete it, and if not see what the differences are.
*/

// Pull the list of recently-changed ids

$ids_file = file_get_contents('ids.txt');
$ids = explode("\n", trim($ids_file));

foreach ( $ids as $value ):
	if ( $value != '' ):

		$diff_count = 0;
		$sql = 'SELECT * FROM report WHERE skiarea_id = ' . $value . ' ORDER BY timestamp DESC LIMIT 2';
		$result = $db->query($sql);


		while ( $row = $db->fetch($result, 'assoc') ):
			$row_compare[] = $row;
	//		$$slug .= '	<dd><a href="' . $url . '" title="' . $label . '">' . $label . '</a></dd>
        endwhile;

		//$diff = array_diff_assoc($row_compare[0], $row_compare[1]);
		// row_compare[0] is the most-recent result
		foreach ( $row_compare[0] as $keyc => $valuec ):
			if ( $keyc != 'timestamp' && ( $valuec != $row_compare[1][$keyc] ) ):
				$keys[] = $keyc;
				$values['new'][] = ( is_int($valuec) ) ? $valuec : "'" . $valuec . "'";
				$values['old'][] = ( is_int($row_compare[1][$keyc]) ) ? $row_compare[1][$keyc] : "'" . $row_compare[1][$keyc] . "'";
				//echo "0: $valuec 1: " . $row_compare[1][$keyc] . "<br>";
				if ( $keyc != 'report_id' ) $diff_count ++;
			endif;
		endforeach;

		if ( $diff_count > 0 ):
			$sql_keys_bit = implode(', ', $keys);
			$sql_values_new_bit = implode(', ', $values['new']);
			$sql_values_old_bit = implode(', ', $values['old']);
			$sql_delta[] = 'INSERT INTO report_delta ( skiarea_id, new, ' . $sql_keys_bit . ' ) VALUES ( ' . $value . ', 0, ' . $sql_values_old_bit . ' ),
		(  ' . $value . ', 1, ' . $sql_values_new_bit . ' );
	';
		else:
			$sql_delete[] = 'DELETE FROM report WHERE skiarea_id = ' . $value . ' ORDER BY timestamp DESC LIMIT 1;
	';
		endif;

		//echo $value . ': ' . $diff_count . '<hr>';
		unset($row_compare); unset($keys); unset($values);
		//var_dump($row_compare);
	endif;

endforeach;

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
