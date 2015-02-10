(function() {
    var app = angular.module("Boards", ['ui.select']);
    app.controller("BusBoardsController", ["$scope", "$http", function($scope, $http) {
        $scope.currentBoard = null;
        $scope.entries = [];
        $scope.busBoards = null;
        $http.get('/getBusStops').success(function(data) {
            $scope.busBoards = angular.fromJson(data);
        });
        $scope.change = function(busStop, model) {
            $scope.currentBoard = busStop;
            $scope.entries = [];
           $http.get('/getEntries?bus_stop=' + $scope.currentBoard).success(function(data) {
                $scope.entries = angular.fromJson(data).entries;
           });
        };
        $scope.getTime = function(date) {
            var date = new Date(date).getTime();
            var now = new Date().getTime();
            var difference = date - now;
            return Math.floor(difference / 60000);
        }
    }]);
})();
