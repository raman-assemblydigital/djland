angular.module('djLand.section', [
  'ui.router',
  'placeholders',
  'ui.bootstrap',
  'ui.sortable',
  'podcastEpisode'
])
    .config(function($stateProvider) {
      $stateProvider

          .state( 'section', {
          url: '/section',
          views: {
              "main": {
                  controller: 'sectionCtrl',
                  templateUrl: 'section/section.tpl.html'
              }
          },
          data:{ pageTitle: 'section' }
      });
    })

    .controller('sectionCtrl', ['$scope','$filter','userService', function($scope, $filter, userService) {

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
    .factory('sectionService', function($http, $filter, API_URL_BASE) {
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
