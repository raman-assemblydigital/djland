angular.module('djLand.playsheet', [
  'ui.router',
  'placeholders',
  'ui.bootstrap',
  'ui.sortable',
  'podcastEpisode'
])
    .config(function($stateProvider) {
      $stateProvider

          .state( 'playsheet', {
          url: '/playsheet',
          views: {
              "main": {
                  controller: 'playsheetCtrl',
                  templateUrl: 'playsheet/playsheet.tpl.html'
              }
          },
          data:{ pageTitle: 'new playsheet' }
      })


          .state( 'editplaysheet', {
          url: '/playsheet',
          views: {
              "main": {
                  controller: 'playsheetCtrl',
                  templateUrl: 'playsheet/playsheet.tpl.html'
              }
          },
          data:{ pageTitle: 'new playsheet' }
      });
//      $stateProvider.html5Mode({enabled:true,requireBase:false});

    })

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
        console.log('hi');
        episode.updateTimeObjs();
      };

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

        episode.updateTimeObjs();
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
        episode.updateTimeObjs();

      };
    }])

    .controller('playsheetCtrl', ['$scope','$filter','$http', '$location', '$window', 'userService','showService','stationDataService', 'playsheetService', function($scope, $filter, $http, $location, $window,userService,showService,stationDataService, playsheetService) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

        $scope.playsheet = {};
        $scope.id = 12345; // hard coded id // TODO: make this in URL (REST style)

        userService.getUserData().then(function(userData){
            $scope.userData = userData;

            showService.getShowData(userData.show_id).then(function(showData){
                $scope.showData = showData;
                $scope.playsheet.language = showData.language;
                $scope.playsheet.crtc = showData.crtc;
                $scope.playsheet.host = showData.host;
                $scope.playsheet.showid = showData.id;
            });
        });

        stationDataService.getActiveShows().then(function(data){
            $scope.active_shows = data;
        });

        playsheetService.getPlaysheetData($scope.id).then(function(data){

            $scope.playsheet = data;


            $scope.samVisible = false;
            $scope.totals = {cancon2:0,cancon3:0,hits:0,femcon:0,nu:0};


            $scope.$watch('playsheet.plays', function(){
                var newTotals = {cancon2:0,cancon3:0,hits:0,femcon:0,nu:0};
                var num = $scope.playsheet.plays.length;
                var num_20 = 0;
                var num_30 = 0;
                for(var i=0; i < num; i++){
                    if ($scope.playsheet.plays[i].nu) {
                        newTotals.nu++;
                    }
                    if ($scope.playsheet.plays[i].cancon && $scope.playsheet.plays[i].crtc == 20) {
                        newTotals.cancon2++;
                    }
                    if ($scope.playsheet.plays[i].cancon && $scope.playsheet.plays[i].crtc == 30) {
                        newTotals.cancon3++;
                    }
                    if ($scope.playsheet.plays[i].femcon) {
                        newTotals.femcon++;
                    }
                    if ($scope.playsheet.plays[i].hit) {
                        newTotals.hits++;
                    }

                    if($scope.playsheet.plays[i].crtc == 20) {
                        num_20++;
                    }
                    if($scope.playsheet.plays[i].crtc == 30) {
                        num_30++;
                    }
                }


                newTotals.cancon2 = 100.00* newTotals.cancon2 / num_20;
                newTotals.cancon3 = 100.00* newTotals.cancon3 / num_30;
                newTotals.femcon = 100.00* newTotals.femcon / num;
                newTotals.hits = 100.00* newTotals.hits / num;
                newTotals.nu = 100.00* newTotals.nu / num;
                $scope.totals = newTotals;
            }, true);
            $scope.add = function(id){
                $scope.playsheet.plays.splice(id+1,0,{ artist:'', album:'', song:'', nu:false,cancon:false,femcon:false,instrumental:false,partial:false,hit:false,crtc:$scope.playsheet.crtc,language:$scope.playsheet.language});

                for(var i=0; i < $scope.playsheet.plays.length; i++){
                    $scope.playsheet.plays[i].id = i;
                }

            };

            $scope.remove = function(id){
                $scope.playsheet.plays.splice(id,1);

                for(var i=0; i < $scope.playsheet.plays.length; i++){

                }
            };

            $scope.sam_add = function(sam){
                $scope.playsheet.plays.push(angular.copy(sam));
            };
           // end of after() function for getting playsheet data
        });

        $scope.loadSAM = function(limits){
            console.log('trying to load new sams');

            $http.get('/samJSON.php')
                .success(function(data, status, headers, config){
                    data = angular.fromJson(data);
                    console.log(data);
                    var samRecent = [];
                    for (var i = 0; i< data.length; i++){
                        samRecent.push({
                            artist:data[i].artist,
                            album:data[i].album,
                            song:data[i].title,
                            nu:false,
                            cancon:data[i].cancon,
                            femcon:data[i].femcon,
                            instrumental: false,
                            partial:false,
                            hit:false,
                            crtc:$scope.crtc,
                            language:$scope.language
                        });
                    }
                    $scope.samRecent = samRecent;

                });

        };


        // DATE STUFF (faking knowing the start of current episode

        var now = new Date();
        var later = new Date();
        later.setHours(now.getHours() + 1);
        now.setMinutes(0);
        later.setMinutes(0);
        now.setSeconds(0);
        later.setSeconds(0);
        now = now.getTime() ;
        later = later.getTime() ;
        $scope.startDate = now;
        $scope.endDate = later;
        $scope.persistent_date = {};

        $scope.persistent_date.start = now;
        $scope.persistent_date.duration = 60*60;

        $scope.episode = {
            title:'han solo',
            subtitle:'new subtitle',
            summary:'new summary',
            active:'1',
            date_unix: $scope.startDate/1000,
            duration: $scope.persistent_date.duration

        };

// angular should be able to do this a better way... but here is manually updating date across scopes
//
        $scope.$watch('episode.start_obj', function(){
            $scope.startDate = $scope.episode.start_obj;
        });
        $scope.$watch('episode.end_obj', function(){

            $scope.endDate = $scope.episode.end_obj;
        });


        $scope.varshidden=true;
    }])
    .controller('newPlaysheet', ['$scope','$controller', function($scope, $controller) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        $controller('playsheetCtrl', {$scope:$scope});
    }])
    .controller('Playsheet', ['$scope','$controller', function($scope, $controller) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        $controller('playsheetCtrl', {$scope:$scope});
    }])
    .value('channel_id',124)
    .value('timezone_offset',-28800)



.factory('episodeNum', ['$filter', function($filter) {
  return {
    url: function(date, end) {


      console.warn(date);

      var start_ = $filter('date')(date.getTime(),'dd-MM-yyyy HH:mm:ss');
      var end_ = $filter('date')(end.getTime(),'dd-MM-yyyy HH:mm:ss');

      console.warn(start_);

      return 'http://archive.citr.ca/py-test/archbrad/download?'+
          'archive=%2Fmnt%2Faudio-stor%2Flog'+
          '&startTime='+start_+
          '&endTime='+end_;

    }
  };
}]);