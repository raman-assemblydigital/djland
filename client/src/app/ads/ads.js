angular.module('djLand.ads', [
  'ui.router',
  'placeholders',
  'ui.bootstrap',
  'ui.sortable',
  'podcastEpisode'
])
    .config(function($stateProvider) {
      $stateProvider

          .state( 'ads', {
          url: '/ads',
          views: {
              "main": {
                  controller: 'adsCtrl',
                  templateUrl: 'ads/ads.tpl.html'
              }
          },
          data:{ pageTitle: 'ads' }
      });
    })

    .controller('adsCtrl', ['$scope','$filter','userService', function($scope, $filter, userService) {

        userService.getUserData().then(function(userData){

        });

    }])/*
    .controller('newPlaysheet', ['$scope','$controller', function($scope, $controller) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        $controller('playsheetCtrl', {$scope:$scope});
    }])
    .controller('Playsheet', ['$scope','$controller', function($scope, $controller) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        $controller('playsheetCtrl', {$scope:$scope});
    }])*/
    .value('value',123)
    .factory('adsService', function($http, $filter, API_URL_BASE) {
        return {

            getServiceData: function(id){
                return $http.get(API_URL_BASE+'/endpoint/'+id)
                    .then(function(result){
                        return result.data;
                    });
            }
        };
    })

;
