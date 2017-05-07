$(function(){
    resizeCellSizeAndFont();
    $(window).on('resize', function(){  
      resizeCellSizeAndFont();
    });
});

function resizeCellSizeAndFont(){
      cellWidth = $(window).width();
      cellHeight = $(window).height();
      getSize(cellHeight, cellWidth);
}

function getSize(l, w) {
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function() {
        document.getElementById("txtHint").innerHTML = this.responseText;
    }

    xmlhttp.open("GET", "s-times-navigation.php?l="+l+"&w="+w+"&t=r", true);
    xmlhttp.send();
}

