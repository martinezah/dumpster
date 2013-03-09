var dumpster = angular.module("Dumpster", []);

function DumpsterCtrl($scope, $http) {
    
    //handlers and helpers
    $scope.tags = function() { $http.post('/', {action:'tags'}).success( function(data) { if (data && data.tags) $scope.tags = data.tags;}); };
    $scope.find = function() {
        var tags = $("#tags option:selected").map(function(){ return this.text }).get();
        $http.post('/', {action: 'find', tags: tags}).success( function(data) { if (data && data.dumps) $scope.dumps = data.dumps;}); 
    };

/*
    $scope.selectTag = function(tag) { if ($.inArray(tag, $scope.selectedTags) < 0) $scope.selectedTags.push(tag); };
    $scope.deselectTag = function(tag) { var ii = $.inArray(tag, $scope.selectedTags); if (ii >= 0) $scope.selectedTags.splice(ii, 1); };
    $scope.get = function() { if ($scope.getId) $http.post('/', {action:'get',id:$scope.getId}).success( function(data) { if (data && data.dump && data.dump.id == $scope.getId) $scope.getData = data.dump; }); }
    $scope.getPubkey = function() { $http.post('/', {action:'pubkey'}).success( function(data) { if (data && data.pubkey) { $scope.pubkey = data.pubkey; } }); };
    $scope.togglePubkey = function() { if (!$scope.pubkey) $scope.getPubkey(); $scope.showPubkey = !$scope.showPubkey; }
*/   

    //initialization
    $scope.tags();
}

dumpster.directive('chosen',function(){
    var linker = function(scope,element,attrs) {
        scope.$watch('tags',function(){
            element.trigger('liszt:updated');
        });
        element.chosen();
    };
    return {
        restrict:'A',
        link: linker
    }
})
