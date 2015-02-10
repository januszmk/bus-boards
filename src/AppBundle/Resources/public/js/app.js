(function() {
    var app = angular.module("Boards", ['ui.select2']);
    app.controller("BusBoardsController", ["$scope", "$http", function($scope, $http) {
        $scope.currentBoard = null;
        $scope.entries = [];
        $scope.busBoards = null;
        $http.get('/getBusStops').success(function(data) {
            $scope.busBoards = angular.fromJson(data);
        });
        $scope.change = function() {
            $scope.entries = [];
           $http.get('/getEntries?bus_stop=' + $scope.currentBoard).success(function(data) {
                $scope.entries = angular.fromJson(data).entries;
           });
        };
        $scope.getTime = function(date) {
            var date = new Date(date);
            var now = new Date();
            return Math.round((((date - now) % 86400000) % 3600000) / 60000)
        }
    }]);
})();
