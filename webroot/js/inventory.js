/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function InitInventoryEditModal(item = null) {
    if (item == null || item == undefined) {

        $("#AddMatch_Ebay_Inventory").val('');
        $("#Ebay_Id").val('');
        $("#Ebay_Annonce_Image").attr("src", '');
        $('#Ebay_Id_Show').text('(None)');
        $('#AddMatch_Ebay_Price').val('');
        $('#AddMatch_LastCheckDate').text('');
        $('#AddMatch_All_Stock').val('');

    } else {
        $("#AddMatch_Ebay_Inventory").val(item.Titre);
        $("#Ebay_Id").val(item.Id);
        $("#Ebay_Annonce_Image").attr("src", item.Image);
        $('#Ebay_Id_Show').text(item.Annonce_Id);
        $('#AddMatch_Ebay_Price').val(item.Prix);
        $('#AddMatch_LastCheckDate').text(item.Last_UpdateTime);
        $('#AddMatch_All_Stock').val(item.Quantite_Disponible);
}
}

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function ShowSpin(elementId) {

    var opts = {
        lines: 13 // The number of lines to draw
        , length: 28 // The length of each line
        , width: 14 // The line thickness
        , radius: 42 // The radius of the inner circle
        , scale: 1 // Scales overall size of the spinner
        , corners: 1 // Corner roundness (0..1)
        , color: '#000' // #rgb or #rrggbb or array of colors
        , opacity: 0.25 // Opacity of the lines
        , rotate: 0 // The rotation offset
        , direction: 1 // 1: clockwise, -1: counterclockwise
        , speed: 1 // Rounds per second
        , trail: 60 // Afterglow percentage
        , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
        , zIndex: 2e9 // The z-index (defaults to 2000000000)
        , className: 'spinner' // The CSS class to assign to the spinner
        , top: '50%' // Top position relative to parent
        , left: '50%' // Left position relative to parent
        , shadow: false // Whether to render a shadow
        , hwaccel: false // Whether to use hardware acceleration
        , position: 'absolute' // Element positioning
    }
    var target = document.getElementById(elementId);
    return new Spinner(opts).spin(target);
}


$(function () {



    $.ajax({
        dataType: "html",
        type: "POST",
        evalScripts: true,
        url: $.cookie("__BASEURL") + '/inventory-ajax/all-ebay-inventory?source=local',
        data: ({}),
        success: function (data, textStatus) {
            var jsonInventory = $.parseJSON(data);
            jsonInventory.forEach(function (elem) {
                elem.label = $('<div/>').html(elem.Titre).text();
            });

            document._InventoryData_ = jsonInventory;

            $('#AddMatch_Ebay_Inventory').autocomplete({
                minLength: 2,
                source: jsonInventory,
                focus: function (event, ui) {
                    $("#AddMatch_Ebay_Inventory").val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    InitInventoryEditModal(ui.item);
                    return false;
                }
            }).autocomplete("option", "appendTo", "#Ebay_AddMatch_Zone")
                    /* .autocomplete("instance")._renderItem = function( ul, item ) {
                     return $( "<li>" ) .append( "<div>" + item.label + "</div>" ).appendTo( ul ); }*/;

            $('#Match_Result').jsGrid({
                height: "90%",
                width: "100%",
                data: document._InventoryData_,
                controller: {
                    loadData: function (filter) {
                        return $.grep(document._InventoryData_, function (item, index) {
                            var r = (!filter.Titre || item.Titre.indexOf(filter.Titre) > -1)
                                    && (!filter.Quantite || item.Quantite == filter.Quantite)
                                    && (!filter.Quantite_Disponible || item.Quantite_Disponible == filter.Quantite_Disponible);
                            return r;
                        });

                    },
                    insertItem: function (insertingClient) {
                        document._InventoryData_.push(insertingClient);
                    },

                    updateItem: function (updatingClient) {
                        console.log(updatingClient);
                    },

                    deleteItem: function (deletingClient) {
                        console.log(deletingClient);
                        var clientIndex = $.inArray(deletingClient, document._InventoryData_);
                        document._InventoryData_.splice(clientIndex, 1);
                    }

                },
                filterable: true,
                filtering: true,
                editing: false,
                sorting: true,
                paging: true,
                autoload: false,
                pageSize: 25,
                editItem: function (item) {
                    console.log(item);
                    InitInventoryEditModal(item);
					
				   $('#Modal_AddMatch').modal({
						backdrop:'static'
				   }).on('hidden.bs.modal', function () {
					   InitInventoryEditModal();
				   });
					
                    $('#AddMatch_Submit').unbind('click').bind('click',function () {
					
						console.log('Submit item :'+item.Titre);
                        var spin = ShowSpin('AddMatch_ElementsBody');

                        var ebay_prix = $('#AddMatch_Ebay_Price').val();
                        var quantite = $('#AddMatch_All_Stock').val();
                        if (isNumeric(ebay_prix) == false || isNumeric(quantite) == false) {
                            alert('Prix et Quantite doivent être de type numeric');
                            return;
                        }

                        var ajax_data = {
                            Match_Id: item.Match_Id,
                            Ebay_Id: $('#Ebay_Id').val(),
                            Priceminister_Annonce_Id: $('#Priceminister_Annonce_Id').val(),
                            Cdiscount_Annonce_Id: null,
                            Ebay_Prix: ebay_prix,
                            Priceminister_Prix: $('#AddMatch_Priceminister_Price').val(),
                            Cdiscount_Prix: $('#AddMatch_Cdiscount_Price').val(),
                            Quantite: quantite

                        };

                        $.ajax({
                            dataType: "html",
                            type: "POST",
                            evalScripts: true,
                            url: $.cookie("__BASEURL") + '/inventory-ajax/edit-match',
                            data: ajax_data,
                            success: function (data, textStatus) {
                                var result = JSON.parse(data);

                                var notyf = new Notyf();
                                if (result['result'] == 'success') {

                                    item.Prix = ajax_data.Ebay_Prix;
                                    item.Quantite_Disponible = ajax_data.Quantite;

                                    $('#Match_Result').jsGrid('refresh');
                                    $('#Modal_AddMatch').modal('hide');
                                    notyf.confirm('修改成功');
                                } else {

                                    notyf.alert('修改失败');
                                }
                                spin.stop();
                                // $('#test_result').html(data);
                                console.log(result);
                                // window.location.reload(true); 
                            }
                        });
                    });
                },
                pageButtonCount: 5,
                deleteConfirm: "Do you really want to delete this match ?",

                fields: [
                    {title: "Image", type: 'image', width: '30', itemTemplate: function (value, item) {
                            if (item.Image) {
                                return '<img src="' + item.Image + '" width="60"/>';
                            }
                        }
                    },
                    {name: "Titre", type: "text", width: 250},
                    {name: "Quantite", type: "number", width: 50},
                    {name: "Quantite_Disponible", type: "number", width: 50},
                    {name: "追踪", type: 'image', itemTemplate: function (value, item) {

                            return '<div>\
                                    <img src="' + $.cookie("__BASEURL") + '/img/ebay' + (item.Inventory_Ebay == null ? '-invalid' : '') + '.png" width="60"/>\
                                    <img src="' + $.cookie("__BASEURL") + '/img/priceminister' + (item.Inventory_Priceminister == null ? '-invalid' : '') + '.png" width="60"/>\
                                    <img src="' + $.cookie("__BASEURL") + '/img/cdiscount' + (item.Inventory_Cdiscount == null ? '-invalid' : '') + '.png" width="60"/>\
                                  <div>';
                        }},
                    {type: 'control', deleteButton: false}
                ]
            });
        }

    });

});
