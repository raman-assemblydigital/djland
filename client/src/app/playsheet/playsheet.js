angular.module('djLand.playsheet', [
  'ui.router',
  'placeholders',
  'ui.bootstrap',
  'ui.sortable'
])
    .config(function($stateProvider) {
      $stateProvider.state( 'playsheet', {
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

    .controller('playsheetCtrl', ['$scope','$filter','$http', '$location', '$window',function($scope, $filter, $http, $location, $window) {
      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
      $scope.samVisible = false;
      $scope.id = 12345; // hard coded id
      $scope.loadPlaysheet = function() {
        $http.get('../../server/api/playsheet/'+$scope.id)
            .success(function(data, status, headers, config){
              $scope.status = 'success';

              $scope.status = 'complete';

              time = new Date();
              $scope.time = time.getTime() / 1000;
              $scope.type = 'live';
              $scope.date = time;
              $scope.language = 'eng';
              $scope.crtc = 30;
              $scope.active_shows = [

                {id:124, name:"Radio No Jikan", host:"radio no jikan host"},
                {id:103, name:"Rocket From Russia", host:"tim"},
                {id:128, name:"Duncan's Donuts", host:"duncan"},
                {id:154, name:"Exploding Head Movies", host:"GAK"}
              ];

              $scope.play_items = angular.fromJson(data);
              $scope.totals = {cancon2:0,cancon3:0,hits:0,femcon:0,nu:0};

              $scope.$watch('play_items', function(){
                var newTotals = {cancon2:0,cancon3:0,hits:0,femcon:0,nu:0};
                var num = $scope.play_items.length;
                var num_20 = 0;
                var num_30 = 0;
                for(var i=0; i < num; i++){
                  if ($scope.play_items[i].nu) {
                    newTotals.nu++;
                  }
                  if ($scope.play_items[i].cancon && $scope.play_items[i].crtc == 20) {
                    newTotals.cancon2++;
                  }
                  if ($scope.play_items[i].cancon && $scope.play_items[i].crtc == 30) {
                    newTotals.cancon3++;
                  }
                  if ($scope.play_items[i].femcon) {
                    newTotals.femcon++;
                  }
                  if ($scope.play_items[i].hit) {
                    newTotals.hits++;
                  }

                  if($scope.play_items[i].crtc == 20) {
                    num_20++;
                  }
                  if($scope.play_items[i].crtc == 30) {
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





            }).error(function(data,status,headers,config){

            });
      };
      $scope.loadPlaysheet();

      $scope.add = function(id){
        $scope.play_items.splice(id+1,0,{ artist:'', album:'', song:'', nu:false,cancon:false,femcon:false,instrumental:false,partial:false,hit:false,crtc:$scope.crtc,language:$scope.language});

        for(var i=0; i < $scope.play_items.length; i++){
          $scope.play_items[i].id = i;
        }

      };

      $scope.remove = function(id){
        $scope.play_items.splice(id,1);

        for(var i=0; i < $scope.play_items.length; i++){

        }
      };

      $scope.sam_add = function(sam){
        $scope.play_items.push(angular.copy(sam));
      };

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

      $scope.loadSAM();
      $window.setInterval($scope.loadSAM, 25000);

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



    }])
    .controller('playsheetRowsCtrl', ['$scope','$filter', function($scope, $filter) {
      $scope.play_items = $scope.$parent.play_items;



    }])
    .value('channel_id',124)
    .value('timezone_offset',-28800);
