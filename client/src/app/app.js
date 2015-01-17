angular.module( 'djLand', [
  'templates-app',
  'templates-common',
  'djLand.home',
  'djLand.playsheet',
    'djLand.podcast',
    'djLand.show',
    'djLand.membership',
    'djLand.ads',
    'djLand.library',
    'djLand.charts',
  'podcastEpisode',
  'ui.router',
  'ui.sortable',
    'ngSanitize'
])

.config( function myAppConfig ( $stateProvider, $urlRouterProvider ) {
  $urlRouterProvider.otherwise( '/home' );
        
})

.run( function run () {
})

.controller( 'AppCtrl', function AppCtrl ( $scope, $location, userService, showService ) {
  $scope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){
    if ( angular.isDefined( toState.data.pageTitle ) ) {
      $scope.pageTitle = toState.data.pageTitle + ' | djLand' ;

    }





  });
})

.factory('userService', function($http, API_URL_BASE) {
      return {
        getUserData: function(){
          return $http.get(API_URL_BASE+'/userinfo')
              .then(function(result){
                return result.data;
              });
        }
      };
    })

.factory('showService', function($http, API_URL_BASE) {
      return {
        getShowData: function(id){
          return $http.get(API_URL_BASE+'/show/'+id)
              .then(function(result){
                return result.data;
              });
        }
      };
    })

.factory('stationDataService', function($http, API_URL_BASE) {
        return {
            getActiveShows: function(){
                return $http.get(API_URL_BASE+'/show/active')
                    .then(function(result){
                        return result.data;
                    });
            },

            getAllShows: function(){
                return $http.get(API_URL_BASE+'/show/all')
                    .then(function(result){
                        return result.data;
                    });
            }
        };
    })


    .value('API_URL_BASE','../../server/api')//  change to api.citr.ca or whatever when we go live
    .value('AUTH_URL_BASE','../../server/auth') //  change to auth.citr.ca or whatever when we go live


;

