angular.module('djLand.podcast', [
  'ui.router',
  'placeholders',
  'ui.bootstrap'
])
    .config(function($stateProvider) {
      $stateProvider.state( 'podcast', {
        url: '/podcast',
        views: {
          "main": {
            controller: 'podcastCtrl',
            templateUrl: 'podcast/podcast.tpl.html'
          }
        },
        data:{ pageTitle: 'new podcast' }
      });
//      $stateProvider.html5Mode({enabled:true,requireBase:false});

    })

    .controller('podcastCtrl', ['$scope','$filter',function($scope, $filter) {


    }]);