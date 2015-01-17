angular.module('djLand.library', [
  'ui.router',
  'placeholders',
  'ui.bootstrap',
  'ui.sortable',
  'podcastEpisode'
])
    .config(function($stateProvider) {
      $stateProvider

          .state( 'library', {
          url: '/library',
          views: {
              "main": {
                  controller: 'libraryCtrl',
                  templateUrl: 'library/library.tpl.html'
              }
          },
          data:{ pageTitle: 'library' }
      });
    })

    .controller('libraryCtrl', ['$scope','$filter','libraryService', function($scope, $filter, libraryService) {

        $scope.formatOptions = ['ALL','CD','CASS','MP3'];
        $scope.orderOptions = ['Artist','Catalog','Title', 'Date Added', 'Date Modified'];

        $scope.filter = {};
        $scope.filter.format = 'ALL';
        $scope.filter.order = 'Artist';

        $scope.editVisible=false;
        $scope.editing = false;

        $scope.search = function(){
            $scope.editVisible=false;

            libraryService.getSearchResults($scope.term)
                .then(function(results){
                $scope.results = results;
            });
            console.log($scope.results);
        };

        $scope.setActive = function(newActive){

            $scope.editVisible = true;
            $scope.editing = false;
            $scope.active = angular.copy(newActive);

            for(var i=0;i<$scope.results.length;i++){
                $scope.results[i].isActive = false;
            }
            newActive.isActive = true;
        };

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
    .factory('libraryService', function($http, $filter, API_URL_BASE) {
        return {

            getSearchResults: function(term){

                return $http.get(API_URL_BASE+'/search')
                    .then(function(result){
                        return result.data;
                    });
            }
        };
    })

;
