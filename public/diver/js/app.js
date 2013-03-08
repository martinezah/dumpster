var dumpster = angular.module("Dumpster", []);

function DumpsterCtrl($scope, $http) {
    
    //handlers and helpers
    $scope.getPubkey = function() { $http.post('/', {action:'pubkey'}).success( function(data) { if (data && data.pubkey) { $scope.pubkey = data.pubkey; } }); };
    $scope.togglePubkey = function() { if (!$scope.pubkey) $scope.getPubkey(); $scope.showPubkey = !$scope.showPubkey; }

    $scope.testStore = function() { $scope.status = "sending"; $http.post('/', {action:'dump',message:{apiKey:"fac0909b-3b4e-467d-9df2-a45183682422",data:"This is a test",tags:["foo","bar","baz"]}}).success( function(data) { if (data && data.dump) { $scope.status = data.dump ? "success" : "failed"; } else { $scope.status = "unknown"; } }).error( function() { $scope.status = "error"; }); };
    
    //initialization
}
