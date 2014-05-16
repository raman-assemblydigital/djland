<?php

 require("headers/security_header.php");
class ShowLib{
	private $mysqli_db_link; // mysql link identifier pointing to $db
	private $curr_week_and_year; //current year and week YY:WW
	private $curr_time;
	private $dayname;
	private $shows = array();//
	function __construct($db_link){
		$this->mysqli_db_link = $db_link;
		$this->curr_week_and_year =  date("oW",strtotime(now));
		$this->curr_time = date("H:m:s",strtotime(now));
		$this->dayname = array(1=>,"Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
		}
	function prepareShowForThisWeek($show){
		$show->current_start_date_time = strtotime($show->start_time + date("d:m:y", strtotime(now)));
		$show->current_end_date_time =strtotime($show->start_time + date("d:m:y", strtotime(now)));
		}
	};
	
class Show{
		public $id;
		public $name;
		public $host;
		public $genre;
		public $show_desc;
		public $img_url;
		public $lang_default;
		public $crtc_default;
		public $website;
		public $podcast;
		public $requirements = array();
		public $show_start_time
		public $show_start_day
		public $show_end_time
		public $show_end_day
		public $show_duration
		public $current_start_date_time
		public $current_end_date_time
		#public $times = array();
		public $contact = array();
		//Queries
		$get_host_query = "SELECT name FROM hosts where id = {$show['host_id']}";
		$get_show_time_query = "SELECT * FROM show_times WHERE show_id = {$show['id']}";
		$get_social_query = "SELECT * FROM social WHERE show_id = {$show['id']}";
		$get_show_query = "SELECT * FROM shows WHERE show_id = {$show['id]}";
		
		//get the host's names
		if($host_result = $this->msqli_db_link->query($get_host_query){
			$this->host = $hosts->fetch_array(['name']);
			$host_result->close();
			}
		
			
			
			
		//get the show times	
		$show_times = array();
		if($show_time_result = $this->mysqli_db_link->query($get_show_time_query)){
			$show_times[] = $show_time_result;
			$show_time_result->close();
			}
			
		for(int i=0;i<$show_times.size();i++){
		$show_time = $show_times[i]->fetch_array();
		$show_start_time = strtotime($show_time['start_time']);
		$show_start_day = strtoTime($dayname[ $show_time['start_day'] ]);
		$show_end_time = strtotime($show_time['end_time']);
		$show_end_time = strtotime($dayname[ $show_time['end_day'] ]);
		$show_duration = strtotime($show_time['end_time'] + $dayname[ $show_time['end_day'] ]) - strtotime($show_time['start_time'] + $dayname[ $show_time['start_day'] ]);
		
		//get the social
		$social_info = array();
		if($social_result = $this->mysqli_db_link->query($get_show_time_query)){
			$social_info = $social_result;
			$social_result->close();
			}
		$duration = array();
		
		
		
			
			
			
		}
		
	};
	

	
	
?>