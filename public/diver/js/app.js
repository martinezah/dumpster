var dumpster = angular.module('Dumpster', ['DumpsterFilters']);

function DumpsterCtrl($scope, $http) {
    
    //handlers and helpers
    $scope.tags = function() { $http.post('/', {action:'tags'}).success( function(data) { if (data && data.tags) $scope.tags = data.tags;}); };
    $scope.find = function() {
        var tags = $('#tags option:selected').map(function(){ return this.text }).get();
        $http.post('/', {action: 'find', tags: tags}).success( function(data) { if (data && data.dumps) $scope.dumps = data.dumps;}); 
    };

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
});

angular.module('DumpsterFilters', []).
    filter('truncate', function () {
        return function (text, length, end) {
            if (isNaN(length))
                length = 10;

            if (end === undefined)
                end = '...';

            if (text.length <= length || text.length - end.length <= length) {
                return text;
            }
            else {
                return String(text).substring(0, length-end.length) + end;
            }

        };
    });

