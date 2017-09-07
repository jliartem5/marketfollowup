/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substringRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      var kwText = str['keyword'];
      if (substrRegex.test(kwText)) {
        matches.push(kwText);
      }
    });

    cb(matches);
  };
};

function KW_SelectedItem_ClickEvent(){
    $(this).remove();
}

function add_customKW(keyword){
    
       var kw_zone = $("#Keyword_Container");
       
       var selected = false;
       kw_zone.find(".KW_SelectedItem").each(function(){
           if($.trim($(this).text()) === keyword){
                selected = true;
                $(this).fadeOut(200).fadeIn(200);
                return false;
           }
       });
       if(selected === false){
            var selected_kw = $("<span class='KW_SelectedItem label label-danger'>"+keyword+"</span>");
            $(selected_kw).click(KW_SelectedItem_ClickEvent).appendTo(kw_zone);
        }
}

$(function(){
    
    $('.KW_SelectedItem').click(KW_SelectedItem_ClickEvent);
    $('[data-toggle="tooltip"]').tooltip(); 
    
     $.ajax({
            dataType: "html",
            type: "POST",
            evalScripts: true,
            url: $.cookie("__BASEURL")+'/ajax/allkeywords',
            data: ({}),
            success: function (data, textStatus){
                var jsonKW = JSON.parse(data);
                $("#Keyword_Search").typeahead({
                         hint: true,
                         highlight: true,
                         minLength: 1
                       },
                       {
                         name: 'keywords',
                         source: substringMatcher(jsonKW)
                }).keypress(function( event ) {
                    if ( event.which == 13 ) {
                        add_customKW($(this).val());
                        $(this).val('');
                        return false;
                    }
                });
            }
        });
        
    
   $(".KW_Highlight_Source .KW_Item").hover(function(){
       $(this).parent().siblings('.KW_Highlight_Target').each(function(){
           //Highlight
       });
   });
   
   $(".KW_Item").click(function(){
       var kw_text = $.trim($(this).text());
       add_customKW(kw_text);
   });
   
   $("#Filter_Form").submit(function(event){
       var keywordsArr = [];
       $("#Keyword_Container .KW_SelectedItem").each(function(){
           keywordsArr.push($(this).text());
       });
       $('<input />').attr('type', 'hidden')
          .attr('name', "Filter_Keywords")
          .attr('value', keywordsArr)
          .appendTo($(this));
   });
});
