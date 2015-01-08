angular.module('djLand.show', [
  'ui.router',
  'placeholders',
  'ui.bootstrap'
])
    .config(function($stateProvider) {
      $stateProvider.state( 'show', {
        url: '/show',
        views: {
          "main": {
            controller: 'showCtrl',
            templateUrl: 'show/show.tpl.html'
          }
        },
        data:{ pageTitle: 'edit show' }
      });
//      $stateProvider.html5Mode({enabled:true,requireBase:false});

    })

    .controller('showCtrl', ['$scope','userService','showService',function($scope, userService, showService){
        userService.getUserData().then(function(userData){
            $scope.userData = userData;

            showService.getShowData(userData.show_id).then(function(showData){
                $scope.showData = showData;
            });
        });

      $scope.editing = false;

    }])

//

;