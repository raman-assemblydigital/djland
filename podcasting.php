<?php

if (!isset($_GET['channel'])){
	echo 'please append podcasting.php with ?channel=CHANNEL# after migrating your database. (For example, <a href="./podcasting.php?channel=12">click here for channel 12</a>) <br/><br/>see <a href="./podcasting/NOTES.txt"> notes.txt </a> for more info about migration</a></a>';
} else {
	$channel_id = $_GET['channel'];
	date_default_timezone_set('America/Vancouver');
}

if (isset($_GET['editall']) && $_GET['editall'] == 'true'){
	$edit_all = 'true';
} else {
	$edit_all = 'false';
}

?>
<html>
<head>
	<style type='text/css'>

	body {
		background-color: white;
		padding-left: 12px;
	}
/*
		.play_episode{
			color:black;
		//	background-color:white;

		}
		.play_episode:hover{
			color:blue;
		}*/

	</style>

	<link rel="stylesheet" href='js/bootstrap/bootstrap.min.css'></script>


</head>


<body ng-app='podcastEditor'>
<h2>podcast editor demo</h2>



<div ng-controller='channelCtrl as channel'>

            <div loading-indicator></div>
            

	<div ng-controller='episodeCtrl' ng-repeat="episode in episodes" class=episode >
		<ng-include src="'podcasting/podcast-episode.html'">

		</ng-include>
		<!--
duration: {{episode.duration}}<br/>

episode start obj: {{episode.start_obj | date: 'medium'}}<br/>
episode end obj: {{episode.end_obj | date: 'medium'}}<br/>
episode duration: {{episode.duration | date: 'medium'}}<br/><br/><br/>
-->
		{{episode}}
	</div>

</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script type='text/javascript' src='js/soundmanager2.js'></script>	

<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular.js"></script>
<script type='text/javascript' src='js/bootstrap/bootstrap.js'></script>
<script type='text/javascript' src='js/bootstrap/ui-bootstrap-tpls-0.12.0-withseconds.js'></script>

<script type='text/javascript'>

	var podcastEditor = angular.module('podcastEditor', ['ui.bootstrap','podcastEpisode'])
		.controller('datepicker', ['$scope','$filter',function($scope, $filter) {
			var episode = $scope.$parent.$parent.episode;

			$scope.today = function() {
				$scope.dt = new Date();
			};

			$scope.clear = function () {
				$scope.dt = null;
			};

			$scope.open = function($event) {
				$event.preventDefault();
				$event.stopPropagation();

				$scope.opened = true;
			};

			$scope.format = 'medium';

			$scope.date_change = function(){
				episode.updateTimeObjs();
			}

		}])
		.controller('timepicker', ['$scope','$filter','timezone_offset', function($scope, $filter, timezone_offset) {
			var episode = $scope.$parent.episode;
			episode.time = episode.date;
			episode.duration_obj = new Date((episode.duration-timezone_offset) * 1000);

			$scope.start_changed = function(time){
				var hh = time.getHours();var mm = time.getMinutes();var ss = time.getSeconds();
				var episode_date = new Date(episode.date);
				episode_date.setHours( hh);episode_date.setMinutes( mm);episode_date.setSeconds( ss);
				episode.date = episode_date;//$filter('date')(episode_date, 'medium');
				episode.date_unix = episode_date.getTime() / 1000;

				episode.updateTimeObjs()
			};

			$scope.length_changed = function(time){

				var existing_duration = time.getSeconds();
				episode.duration = ( time.getTime() / 1000 ) + timezone_offset;
				var hh = time.getHours();var mm = time.getMinutes();var ss = time.getSeconds();

				var new_end_date = new Date(episode.date);
				var start_hh = new_end_date.getHours();
				var start_mm = new_end_date.getMinutes();
				var start_ss = new_end_date.getSeconds();

				new_end_date.setSeconds(start_ss + ss + timezone_offset);
				new_end_date.setMinutes(start_mm + mm);
				new_end_date.setHours(start_hh + hh);

				episode.end_obj = new_end_date;
				episode.updateTimeObjs()

			};
		}])
		.controller('channelCtrl', ['$scope', '$http', '$filter','channel_id','edit_all', function($scope, $http, $filter, channel_id, edit_all){

			$scope.status = 'loading...';
			$scope.edit_all = edit_all;

			$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
			$scope.channel_id = channel_id;


			$scope.makeEpisodes = function() {
				$http.get('./api/episodes/list.php?channel='+channel_id)
					.success(function(data, status, headers, config){
						$scope.status = '';
						$scope.episodes = [].concat(angular.fromJson(data));

						if(!edit_all){
							$scope.episodes = $scope.episodes.slice(0,10);
						}
					});
			};
			$scope.makeEpisodes();
			/*
			 $scope.numPages = function () {
			 return Math.ceil($scope.episodes.length / $scope.numPerPage);
			 };
			 */

			sm = new SoundManager();

		}]);

	angular.module('podcastEpisode',[])
		.controller('episodeCtrl', ['$scope', '$http', '$filter', 'archiveService', 'channel_id', function($scope, $http, $filter, archiveService, channel_id){

			$scope.episode = $scope.$parent.episode;

			var episode = $scope.episode;

			episode.active = parseInt(episode.active,10);
			episode.archiveURL = archiveService.url(episode.start_obj, episode.end_obj);

			episode.start_obj = new Date(episode.date_unix*1000);
			episode.date = $filter('date')(episode.start_obj, 'medium');

			episode.updateTimeObjs = function(){
				var start = new Date(episode.date);
				episode.date_unix = start.getTime() / 1000;
				episode.start_obj = start;

				var end = new Date(episode.date_unix*1000);
				var end_seconds = end.getSeconds();
				end.setSeconds(end_seconds + parseInt(episode.duration,10));
				episode.end = $filter('date')(end, 'mediumTime');
				episode.end_obj = end;

			};
			episode.updateTimeObjs();

			$scope.editing = false;

			$scope.editToggle = function(){
				$scope.editing = !$scope.editing;

			}

			$scope.save = function(episode){
				$scope.status = 'saving...';

				var data_to_post = {};
				data_to_post.url = episode.url;
				data_to_post.channel = channel_id;
				data_to_post.data = {
					title:episode.title,
					subtitle:episode.subtitle,
					summary:episode.summary,
					id:episode.id,
					date:episode.date,
					duration:episode.duration,
					active:episode.active,
					author:episode.author

				};

				$http({
					url:'./podcasting/episode.php',
					method:'POST',
					data:$.param(data_to_post)
				})
					.success(function(data, status, headers, config){
						$scope.status = data;

					})
					.error(function(data,status,headers,config){
						$scope.status = 'error: '+status;

					});
			}

			var basic_sound_options = {
				debugMode:false,
				useConsole:false,
				autoLoad:true,
				multiShot:false,
				stream:true/*,
				 onplay: function(){
				 for (var i = 0; i< sm.soundIDs.length ; i++){
				 var soundID = sm.soundIDs[i];
				 if( (this.id != soundID) && sm.getSoundById(soundID).playState ){
				 sm.getSoundById(soundID).stop();
				 console.warn('stopped playing sound id '+soundID);
				 }
				 }
				 }*/
			};

			sm.setup({
				debugMode:true,
				useConsole:true,/*
				 onready:function(){

				 var music_url = episode.url;

				 episode.sound = sm.createSound(
				 angular.extend(basic_sound_options,{
				 id:'full'+episode.id,
				 url:episode.url
				 })
				 );

				 },
				 ontimeout: function() {
				 console.error('Soundmanager init failed!');
				 }*/

			});

			$scope.load_and_play_sound = function(url){
				var the_scope = this;
				if(typeof(episode.sound) != 'undefined') {
					episode.sound.destruct();
				}
				sm.stopAll();
				episode.sound = sm.createSound(
					angular.extend(basic_sound_options,{
						autoPlay:true,
						multiShot:false,
						id:'sound'+episode.id,
						url:url,
						onfinish:function(){
						},
						whileloading: function() {
							the_scope.sound_status = 'loading preview...';
							if (this.duration == 0){
								the_scope.sound_status = 'sorry, preview not available';
							}
						},
						whileplaying: function() {
							the_scope.sound_status = 'playing ... type: '+this.type;
							if (this.duration == 0){
								the_scope.sound_status = 'sorry, preview not available.';
							}
						}
					})
				);

			};

			$scope.preview_start = function(){


				var start_prev_end = new Date(episode.start_obj);
				start_prev_end.setSeconds(start_prev_end.getSeconds() + 5);
				var sound_url = archiveService.url(episode.start_obj, start_prev_end);
				$scope.load_and_play_sound(sound_url);
			};

			$scope.preview_end = function(){

				var end_prev_start = new Date(episode.end_obj);
				end_prev_start.setSeconds(end_prev_start.getSeconds() - 5);
				var sound_url = archiveService.url(end_prev_start, episode.end_obj);
				$scope.load_and_play_sound(sound_url);

			}

		}])
		.factory('archiveService', ['$filter', function($filter) {
			return {
				url: function(date, end) {

					return 'http://archive.citr.ca/py-test/archbrad/download?'+
						'archive=%2Fmnt%2Faudio-stor%2Flog'+
						'&startTime='+$filter('date')(date,'dd-MM-yyyy HH:mm:ss')+
						'&endTime='+$filter('date')(end,'dd-MM-yyyy HH:mm:ss');

				}
			};
		}])
		.factory('soundManager', function() {

			return {
				sm: function(){

				}
			}
		})
		.config(function($httpProvider) {

            $httpProvider.interceptors.push(function($q, $rootScope) {
                return {
                    'request': function(config) {
                        $rootScope.$broadcast('loading-started');
                        return config || $q.when(config);
                    },
                    'response': function(response) {
                        $rootScope.$broadcast('loading-complete');
                        return response || $q.when(response);
                    }
                };
            });

        })
		.directive("loadingIndicator", function() {
            return {
                restrict : "A",
                template: "<div>Loading...</div>",
                link : function(scope, element, attrs) {
                    scope.$on("loading-started", function(e) {
                        element.css({"display" : ""});
                    });

                    scope.$on("loading-complete", function(e) {
                        element.css({"display" : "none"});
                    });

                }
            };
        });

</script>

	<script type='text/javascript'>
		podcastEditor.value('timezone_offset',<?php echo date_offset_get(new DateTime); ?>); //timezone offset is in seconds
		podcastEditor.value('channel_id','<?php echo $channel_id; ?>');
		podcastEditor.value('edit_all',<?php echo $edit_all;?>);
		podcastEditor.value('creating_new_episode',false);

	</script>

</body>

</html>


