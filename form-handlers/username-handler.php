<?php
	require("../headers/db_header.php");
	require("../headers/function_header.php");
	
	if(isset($_POST['username'])){
		$username = $_POST['username'];
	
		//query = "SELECT case when count(username) > 0 then 1 else 0 end as 'bool' FROM user WHERE username LIKE 'boob'";
		$query = "SELECT case when count(username) > 0 then 1 else 0 end as 'bool' FROM user WHERE username LIKE '".$username."'";
		if($result = $db->query($query)){
			$ret=array();
			while($row = mysqli_fetch_array($result)){
				$ret[] = $row;
			}
		}
		echo json_encode($ret);
		$result->close();
	}else{
	echo "fail";
	}
?>


