<?php
//DB HEADER
//open the database

global $samDB_ip, $samDB_user, $samDB_pass, $samDB_dbname;
$samDB_ip = 'p:192.168.25.69';
$samDB_user = 'samuser';
$samDB_pass = 'sam101.9';
$samDB_dbname = 'samdb-live';



$db = new mysqli('p:192.168.25.73', 'playlist', '79bananas2013CAPS4evr', 'citr_dev');
			if (mysqli_connect_error()) {
	    		print('Connect Error for citr db (' . mysqli_connect_errno() . ') '
	            . mysqli_connect_error());
			}
			
			
			
$mysqli_sam = new mysqli($samDB_ip, $samDB_user, $samDB_pass, $samDB_dbname);

if (mysqli_connect_error()) {
	echo 'there is a connection error';
    die('Connect Error for sam db (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
}

function mysqli_result($res, $row, $field=0) { 
//	echo 'called mysqli result';
//	echo '<br/>';
//	echo 'res:'.'<br/>';
//	print_r($res);
//	echo 'row:'.$row.'<br/>';
//	echo 'field:'.$field.'<br/>';
	if(is_object($res))    
		$res->data_seek($row); 
	else 	return false;
	
	$datarow = $res->fetch_array();
	
	if(is_array($datarow))
	    return $datarow[$field];
	else 	return false;
	        
} 

//END DB HEADER
?>