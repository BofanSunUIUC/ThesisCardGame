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
    xmlhttp.open("GET", "detectScreenSize.php?l="+l+"&w="+w, true);
    xmlhttp.send();
}

