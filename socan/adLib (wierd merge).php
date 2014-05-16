<?php

require_once('headers/showlib.php');
date_default_timezone_set ("America/Vancouver");

class AdLib {
	private $sam_link; // mysql link identifier
	private $citr_link;
	private $curr_week;
	private $curr_time;
	public $ad_dict;
	private $showlib;
	private $availableAds;
	function __construct($samLink, $citrLink) {

		$this->sam_link = $samLink;
		$this->citr_link = $citrLink;
		$this->curr_time = time();
		$this->ad_dict = 	array(
							'AD' => '-AD (PRIORITY)',
						 	'ID'=>'-Station ID',
						 	'PSA'=>'-PSA',
						 	'PROMO'=>'-Show Promo');
						 	
		$this->showlib = new Showlib($this->citr_link);
		$this->availableAds = $this->loadAvailableAds();
//		$this->show_lib = new ShowLib
		
	}
	
	
	function sayHello(){
		echo 'hello';
	}


	function loadAdRows($unixTime){
		$adTimes = array();
		$adTypes = array();
		$adIDnums = array();		
		$adNames = array();
		$dbIDnums = array();
				
		$load_query = "SELECT id, time, type, name FROM adlog WHERE time_block = '".$unixTime."'";
		
		if ($result_load = mysqli_query($this->citr_link,$load_query)){
			while($ad = $result_load->fetch_array()){
		//	print_r($ad);
				$adTimes []= $ad['time'];
				$adTypes []= $ad['type'];
				$dbIDnums []= $ad['id'];
				if (is_numeric($ad['name']) && $ad['name']!=0 ){
				//	echo 'is numeric: '.$ad['name'];
					$adNames []= $this->getAdNameFromID($ad['name']);
					$adIDnums []= $ad['name'];
				} else {
					$adNames []= $ad['name'];
					$adIDnums []= 0;
				}
				
//				$adIDAndName []= array('89',false) ;
			//	if (is_numeric($ad['name']) ){
			//		$adNames[$i-1] = getAdNameFromID($ad['name']);
			//	} else {
			//		$adNames[$i-1] = 'not an ad';
			//	}
			}
			
			if( mysqli_num_rows ( $result_load ) == 0 ) {
				return false;
			}
			
		} else {
			return false;
		}
	
		$returny = array($adTimes,$adTypes,$adIDnums,$adNames, $dbIDnums);
		return $returny;	
		
	}
	
	
// LOADS ADS FROM DATABASE IF THEY HAVE BEEN SCHEDULED

	function getAdNameFromID($id){
		
		$ad_q = "SELECT artist, title FROM songlist WHERE ID = '".$id."'";
		if( $result = mysqli_query($this->sam_link,$ad_q)){
			$ad = $result->fetch_array();
			if (is_array($ad)) {
			//	echo 'loaded an ad name: '.$ad['artist'].' - '.$ad['title'];
			return $ad['artist'].' - '.$ad['title'];
			} else { return false; }
		}
		else return false;
		
	}
	
	
	
	function playAd($ad){
		
	}
	
	function wasPlayed($ad){
		
	}
	function howManySlots($showBlock){
		
		$dur = showBlock::getShowBlockLength($showBlock);
		$start = $showBlock['start_time'];
		$start_dec_a = explode(':',$start);
		$start_dec = ($start_dec_a[1])/60.0;
		$numTopHour = ceil($dur-$start_dec); // number of 'top of the hour's that occur
		
		$slots = ($dur*4.0)+$numTopHour;
				
		return $slots;
		
	}
	
	
	function topOfHour($time){
		
	}
	
	function getTimeAndTypeListForShow($showBlock){ 

		//$showlib = new ShowLib($this->citr_link);
		$num_slots = $this->howManySlots($showBlock);
		

		$start = substr($showBlock['start_time'],0,-3);
		$startD = new DateTime($start);
		$startD2 = new DateTime($start); // because datetime::add modifies the DateTime object
		
		$tenMinutes = new DateInterval('PT10M');
		$twentyMinutes = new DateInterval('PT20M');
				
		$first = date_format($startD2,'g:i a');
		
		//.' - '.date_format($startD2->add($tenMinutes),'g:i a');
		
		
	//	$times = array($first);
		$times = array();
		$types = array();
		
//		$times[0] = $first;
		
		if ( (date_format($startD,'i') % 20) != 0){
			$startD->add($tenMinutes);
		}
		
		$alternating = 1;
		for($i = 0; $i <= $num_slots; $i=$i+2){
			
			$next = date_format($startD,'g:i a');
//			date_format($startD2,'g:i a').' - '.date_format($startD2->add($tenMinutes),'g:i a');
			
			if ( date_format($startD,'i') == 0){
				$tempDate = clone $startD;
				$times []=	$next . ' - '.date_format($tempDate->add($tenMinutes),'g:i a');
				$types []= $this->ad_dict['ID'];
			} else {
				$times []= $next;
				$times []= $next;
				
				if($alternating ==1){
				$types []= $this->ad_dict['AD'];
				$types []= $this->ad_dict['PSA'];
				$alternating = 2;
				} else if($alternating ==2){
				$types []= $this->ad_dict['AD'];
				$types []= $this->ad_dict['PROMO'];
				$alternating = 1;
				}
				
				
			}
			
			
			$startD->add($twentyMinutes);
		}
		
		/*
		for($i=1; $i<$num_slots-1;$i++){
			$times[$i] = ' - ';
		}*/
		$times [] = 'before end';
		
		return array($times,$types);
		
	}
		
	// takes the time list and type list and returns the name list based on these
	function getNames($types, $uniqueTime){
	//	$lastSunday = strtotime("last sunday");
	//	$uniqueTime = $showBlock['wdt']+$lastSunday;
//		$showBlock = $this->showlib->getShowByTime($uniqueTime)->times[0];
//		$types = $this->getTypeListForShow($showBlock);
/*
		$adPairs = $this->getAdsFromTimeBlock($uniqueTime);

		if($adPairs!=0){
			$adIDList = $adPairs[0];
			$adStringList = $adPairs[1];
		} else {
			$adIDlist = 0;
			$adStringList = 0;
		}
*/		
		$names = array();
		
		$adIndex=0;
		
		foreach($types as $i => $type){
			
			if($type == $this->ad_dict['ID']){
				$names[$i] = "&quot;You're listening to CiTR 101.9 in Vancouver&quot;";
			} else
			if($type == $this->ad_dict['PSA']){
				$names[$i] = "(any)";
			} else
			if($type == $this->ad_dict['PROMO']){
				$names[$i] = "(any)";
			} else
			if($type == $this->ad_dict['AD']){
				$names[$i] = 'adslot';
			} else {
				$names[$i] = '?';
			}
		}
		
		$names []= "announce upcoming program";
	//	print_r($names);
		return $names;
	}
			
	function getHTML($view,$times,$types,$adNames,$adIDnums, $dbIDnums){
		
			$strings = '';
			
			$strings .= '<div class="adHead">';
					
		
			if($view=='dj') {
				$strings .=		'<div class="adTime label">time</div>'.
								'<div class="adType label">type</div>'.
								'<div class="adName label">name</div>'.
								'<div class="adPlay label">played</div>'.
								'</div>';	
	
				foreach($times as $i => $time){
		
					// DJ VIEW			
					
						
					$strings .= 	'<div class="adRow" id=db_'.$dbIDnums[$i].'>'.
									'<div class="adTime"><input class="adInput" type="text" id="adTime'.$i.'" name="adTime'.$i.'" value="'.$time.'" readonly="true"></div>';
				
					$strings .=		'<div class="adType" ><input class="adInput" type="text" id="adType'.$i.'" name="adType'.$i.'" readonly="true" ';
					
					$strings .=		'value="'.$types[$i].'"></div>';
					
					$strings .=		'<div class="adName" id="adName'.$i.'"  value="'.$adIDnums[$i].'">'.
					//				'<input class="adInput" type="text" id="adName'.$i.'" name="adName'.$i.'" value="peanut butter" readonly="true"></div>'.
									'<input class="adInput" type="text" id="adName'.$i.'" name="adName'.$i.'" value="'.$adNames[$i].'" readonly="true"></div>'.
									'<div class="adPlay" id="adPlay'.$i.'"><input type="checkbox" id="adPlayCheck'.$i.'" name="adplaydbid_'.$dbIDnums[$i].'"';
					
					if($played){
					$strings .=		$played[$i]? 'checked=true' : ''; 			
					}
					$strings .=		'></div>'.
									'</div>';
				}
			} else if($view=='prog') {
				
				$strings .= 
				'<div class="xbutton label">buttons</div>'.
				'<div class="adTime label">time</div>'.
				'<div class="adType label">type</div>'.
				'<div class="adName label">name</div>'.
				'</div>';
				
				
				foreach($times as $i => $time){
				
					$strings .= 	'<div class="adRow">'.
							'<div><div class="adbuttons ad-delete">-</div><div class="adbuttons ad-add">+</div><div class="adbuttons ad-advert">Ad</div></div>'.
							'<div class="adTime"><input class="adInput" type="text" id="adTime'.$i.'" name="adTime'.$i.'" value="'.$time.'"></div>'.
							'<div class="adType" id="adType'.$i.'"><input class="adInput" type="text" value="'.$types[$i].'"></input></div>'.
							'<div class="adName" id="adName'.$i.'">';
							if($adNames[$i]=='adslot' || $adIDnums[$i] ){
					$strings .=		$this->generateAdSelector($adIDnums[$i],$adNames[$i]);						
							} else {
					$strings .=			'<input class="adInput" type="text" value="'.$adNames[$i].'">';						
							}
					$strings .=			'</div></div>';
			
				}
			
			}	
			return $strings;
		
						/* this code will let the user select an ad type (IN DEVELOPMENT)
				echo '<div class="adType" id="adType'.$i.'"><select id="type'.$p.'">';
					for($num = 0; $num < count($psaTypes); $num++){
						echo '<option>'.$psaTypes[$num][0].'</option>';
					}
				echo '</select></div>';
				*/
				
	}
	
	
	function generateTable($unixTime,$view){
		
		
		if( $ad_array = $this->loadAdRows($unixTime)   ){
		//	echo '<hr>loading rows - unix: '.$unixTime.'<hr>';
			$times = $ad_array[0];
			$types = $ad_array[1];
			$adIDnums = $ad_array[2];
			$names = $ad_array[3];
			$dbIDnums = $ad_array[4];
		} else  if($view == 'prog'){
		
		//	echo '<hr>generating the rows - unix: '.$unixTime.'<hr>';
			
		$theShow = $this->showlib->getShowByTime($unixTime);
		$showBlock = $theShow->times[0];
		
//				print_r($showBlock);

		$table = '';
		
		$times_types = $this->getTimeAndTypeListForShow($showBlock);
		$times = $times_types[0];
		$types = $times_types[1];
		
		$names = $this->getNames($types, $unixTime);
		
//		$names = false;
		$adIDnums = false;
		}else return "<br/>no ad's have been scheduled! <br/>mention station IDs at the top of every hour!";
		
		$table .= $this->getHTML($view,$times,$types,$names,$adIDnums, $dbIDnums);
		return $table;
		
	} 
	
	
	function loadTable($playsheet_id){
		$string = '';
		$view = 'dj';
		$adload_query = "SELECT * FROM adlog WHERE playsheet_id = '".$playsheet_id."'";
		if ($adload_result = mysqli_query($this->citr_link, $adload_query)){
			
			while($adRow = $adload_result->fetch_array()){
				$adTable []= $adRow;
			}
			
			$times = array();
			$types = array();
			$names = array();
			$samIDs = array();
			$playeds = array();
			
			foreach( $adTable as $i => $row ){
				$times []= $row['time'];
				$types []= $row['type'];
				$names []= $row['name'];
				$samIDs []= $row['sam_id'];
				$playeds []= $row['played'];
			}
	//		$string.="<br/>query: ".$adload_query."<br/>";
	//		print_r($adload_result);
	
		
		$string .= $this->getHTML($view,$times,$types,$names,$samIDs, $playeds);  
		} else {
			$string .= "unable to load ads :(";
		}
	
		return $string;
	
	}
	
	function generateAdSelector($ad_id = false,$ad_name = false){
		$string = '<select id="name'.$p.'" class="selectanad">';
		
				if($ad_name&&$ad_id){ // only ad_name will be something if it's a blank ad slot
					$string .= '<option value="'.$ad_id.'">'.$ad_name.'</option>';
				} else {
					$string .= '<option value="0">select an AD</option>';
				}	// whether there are ads or not, we need an option
					// to select scheduling no ad.
					$string .= '<option value="noad"> -- </option>';
					
					foreach($this->availableAds as $i => $ad){
						$string .= '<option value="'.$ad['id'].'">'.$ad['artist'].' - '.$ad['title'].'</option>';
						}
				$string .= '</select>';
				
				return $string;
	}
	

	
	function loadAvailableAds(){
		
		$addys = array();
			
		if ($result_sam = mysqli_query($this->sam_link,"SELECT id, artist, title FROM songlist WHERE songtype = 'A' ")) 
		{		
		//	echo 'loadAvailableAds succeeded';
				while($row = $result_sam->fetch_array())
				{
				$addys []= $row;
				}
				    /* free result set */
				    $result_sam->close();
		} else { echo 'loading ads failed';}
		return $addys;
	
	}
	


}














//old - unused
/*
function getAdsFromTimeBlock($unixTime){
		
		$load_query = "SELECT * FROM scheduled_ads WHERE time_block = '".$unixTime."'";
//		if ($result_load = $this->citr_link->query($load_query)){
		if ($result_load = mysqli_query($this->citr_link,$load_query)){
			
			$loadedAdSelect = $result_load->fetch_array();
			
			if(count($loadedAdSelect)<=0){
				//there are no ads!
				
				return 0;
			}
			$adIDList = explode(';',$loadedAdSelect[sam_song_id_list]);
//			echo 'loaded ad select:<br/>';
//			print_r($loadedAdSelect);
			$adStringList = array();
			
			$result_load->close();
			
		}
	
		foreach($adIDList as $i => $adID){
			if($adID!='noad'){
				//	echo 'adID: '.$adID.'<br/>';
				$ad_q = "SELECT artist, title FROM songlist WHERE ID = '".$adID."'";
	//			if( $result_ads = $this->sam_link->query($ad_q)){
				if( $result_ads = mysqli_query($this->sam_link,$ad_q)){
					$adArray = $result_ads->fetch_array();
				$adStringList []= $adArray[artist].' - '.$adArray[title];
	
				}
			}
			else{
			$adStringList []= '--';
			}
			
		}
		
	//		echo $load_query;
		return array($adIDList,$adStringList);
//		return 0;
	
	
	}
	*/
?>