angular.module('djLand.charts', [
  'ui.router',
    'ngSanitize'
])
    .config(function($stateProvider) {
      $stateProvider

          .state( 'charts', {
          url: '/charts',
          views: {
              "main": {
                  controller: 'chartsCtrl',
                  templateUrl: 'charts/charts.tpl.html'
              }
          },
          data:{ pageTitle: 'charts' }
      });
    })

    .controller('chartsCtrl', ['$scope','$filter','chartsService','$sanitize', function($scope, $filter, chartsService, $sanitize) {


        chartsService.getChartsData().then(function(chartsData){

            $scope.charts = (chartsData);

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
    .factory('chartsService', function($http, $filter, $sanitize, API_URL_BASE) {
        return {

            getChartsData: function(id){
                return $http.get( API_URL_BASE+'/chart')
                    .then(function(result){

                        return result.data;
                    });
            }
        };
    })

;
