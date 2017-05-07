$(function(){
    var MAX_VAL = promptEntry();

    /*
     *3.The table should scale to the size of the browser window, 
     *but with a 100px margin on each side, with table cell width 
     *and height scaling with browser width and height 
     *(i.e. a table with 10 columns and 10 rows should have cells 
     * with widths and heights of 100%/10 = 10% of browser width 
     * and height).
     */
    resizeCellSizeAndFont(MAX_VAL);
    $(window).on('resize', function(){  
      resizeCellSizeAndFont(MAX_VAL);
    });
});

function resizeCellSizeAndFont(MAX_VAL){
      cellWidth = $(window).width()/MAX_VAL;
      cellHeight = $(window).height()/MAX_VAL;
      if(cellHeight<cellWidth){
        cellWidth = cellHeight;
        $('.table').css({
         'width':$(window).height()+ 'px'
        })
      }else{
        $('.table').css({
         'width':$(window).width()+ 'px'
        })
      }

       $('.bcell').css({
         'height': cellWidth + 'px',
         'width': cellWidth + 'px',
         'font-size':cellWidth/3 + 'px'
       });
       $('.wcell').css({
         'height': cellWidth + 'px',
         'width': cellWidth + 'px',
         'font-size':cellWidth/3 + 'px'
       });
}

function createTable(MAX_VAL){
  var content ="";
  var flip = true;
  /*
   *2.Each cell should contain the product of the row index 
   * and the column index pertaining to that cell 
   * (indexing from 1, not 0).
   */
  for(i=1;i<=MAX_VAL;i++){
  	content += "<div class='row'>";
    for(j=1;j<=MAX_VAL;j++){
      if(flip == true){
        content += "<div class='bcell'>" + i*j +"</div>"; 
      }else{
        content += "<div class='wcell'>" + i*j +"</div>";
      }
      flip = !flip;
    }
    if(MAX_VAL%2==0)flip=!flip;
    content += "</div>";
  }
    $('.table').append(content);
}

/*
 *1. The table should be MAX_VAL squares wide by MAX_VAL 
 *squares tall (where MAX_VAL is the value the user 
 *entered in the prompt from Step the step above
 */
function promptEntry() {
  var MAX_VAL = "";
  while (MAX_VAL == "" || isNaN(MAX_VAL) || MAX_VAL < 1 || MAX_VAL > 12){
    if(MAX_VAL == ""){
      MAX_VAL = prompt("Please enter an integer between 1 and 12", "");
	  }else if(isNaN(MAX_VAL) || MAX_VAL < 1 || MAX_VAL > 12){
      MAX_VAL = prompt(MAX_VAL + " is invalid. Please enter an integer between 1 and 12.","");
	  }
  }
  createTable(MAX_VAL);
  return MAX_VAL;
}

