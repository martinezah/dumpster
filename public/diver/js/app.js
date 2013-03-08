var dumpster = angular.module("Dumpster", []);

function DumpsterCtrl($scope, $http) {
    
    //handlers and helpers
    $scope.searchTags = function() { $http.post('/', {action:'tags', prefix: $scope.tagFilter}).success( function(data) { if (data && data.tags) $scope.filteredTags = data.tags; }); }
    $scope.selectTag = function(tag) { if ($.inArray(tag, $scope.selectedTags) < 0) $scope.selectedTags.push(tag); };
    $scope.deselectTag = function(tag) { var ii = $.inArray(tag, $scope.selectedTags); if (ii >= 0) $scope.selectedTags.splice(ii, 1); };


    $scope.getPubkey = function() { $http.post('/', {action:'pubkey'}).success( function(data) { if (data && data.pubkey) { $scope.pubkey = data.pubkey; } }); };
    $scope.togglePubkey = function() { if (!$scope.pubkey) $scope.getPubkey(); $scope.showPubkey = !$scope.showPubkey; }
    
    //initialization
    $scope.selectedTags = [];
    $scope.searchTags();
}
