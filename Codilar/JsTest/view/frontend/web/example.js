define([], function () {
    var mageJsComponent = function(config, node)
    {
        alert("Look in your browser's console");
        console.log(config);
        console.log(node);
    };

    return mageJsComponent;
});