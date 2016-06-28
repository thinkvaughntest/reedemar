
"use strict";

var MyApp = angular.module("inventory-app", ["ngFileUpload","angularUtils.directives.dirPagination"]);

MyApp.controller('InventoryController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    
    a.dataLength={filtered:[]};
    a.cnames = [];
    a.inventory_details = [];
    a.inventory_details.inventory_image = [];

    a.disableaddinventory = false;
    a.addinventorybtn="Save";
    a.updateInventory_disabled = false;

    a.showPreview = false;
    a.cropMe = false;

    a.cropper = {};
    a.cropper.sourceImage = null;
    a.cropper.croppedImage   = null;

    var site_path=$("#site_path").val();
    
    x.post("../inventory/list", update_id).success(function(data_response){              
        a.inventory_details = data_response; 
        a.file_path=site_path;  
    });    
   //  console.log($('#main_site_url').val()) ;

    $("#file").on('change',function(){
      a.showPreview = true;
    });

    a.enable_cropping = function(){
      a.cropMe = true;
    };

    a.cancel_cropping = function(){
      a.cropMe = false;
    };     

    a.addInventory = function(){     
      if($("#inventory_name").val()=='' || $("#sell_price").val()=='' || $("#cost").val()=='') {
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#error_div").html("Please insert all fields.");
          $("#error_div").show();
          $("#success_div").hide();

          $('#add_inventory').prop('disabled', false);
          $("#add_inventory").text('Save');
          return false;
      } else {

        if(a.cropMe){
          var file = {
            'image_type' : 'jpg',
            'image_data' : a.cropper.croppedImage,
            'inventory_data' : a.Inventory
          }
        } else {
          var file = {
            'image_type' : 'jpg',
            'image_data' : a.cropper.sourceImage,
            'inventory_data' : a.Inventory
          }
        }        
        a.disableaddinventory = true;
        a.addinventorybtn="Saving ...";
        var update_id = "";   
       // console.log(file);
       // return false;     
        x.post("../inventory/store",file).success(function(response){         
           var url = 'dashboard#/inventory/list';
          // window.location = url;
           //break;
        });
        // fu.uploadNewFileToUrl(file, uploadUrl).then(function(fdata){
            
        //     console.log("fdata.data  :: "+fdata.data);
        //     var logo_name = fdata.data;
        //     a.Inventory.inventory_image = logo_name;

        //     x.post("../inventory/addlogo",a.Inventory).success(function(response){
              
        //       switch(response) {
        //         case 'success':
        //             a.Inventory = {};
        //             $("#error_div").hide();
        //             $("#show_message").slideDown();
        //             $("#success_div").html("Data inserted successfully. <br />Please wait,we will reload this page.");
        //             $("#success_div").show();
                    
        //             var url = 'dashboard#/inventory/list';
        //             window.location = url;
        //             break;
        //         case 'image_not':
        //             a.Inventory.inventory_image = "";
        //             $("#error_div").hide();
        //             $("#show_message").slideDown();
        //             $("#error_div").html("Unable to upload image. Please try again.");
        //             $("#error_div").show();
        //             $("#success_div").hide();
        //             return false;  
        //             break;
        //         default:
        //             $("#error_div").hide();
        //             $("#show_message").slideDown();
        //             $("#error_div").html("Please insert all field.");
        //             $("#error_div").show();
        //             $("#success_div").hide();
        //             break;
        //       }

        //     })
        // });
      }
    };



    // Function for deleting a Inventory
    a.delete_inventory=function(itemId){ 
    
      if(confirm("Are you sure?")) {    
        x.get("../inventory/delete/"+itemId).success(function(response){
          r.reload();
        });
      }
    };

    a.redirect_edit=function(itemId){
      //return false;
      localStorage.setItem('editId', itemId);
      var url = 'dashboard#/inventory/edit';
      window.location = url;
    }

}]);

// Edit Inventory data
MyApp.controller('InventoryEditController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    a.inventory_details = [];
    a.updateInventorybutton="Save";
    
    if(localStorage.getItem('editId')){
      var update_id = JSON.parse(localStorage.getItem('editId'));
      x.post("../inventory/list",update_id).success(function(data_response){              
          a.inventory_details = data_response[0]; 
      });

      a.updateInventory=function(){
        $('#edit_inventory').prop('disabled', true);
        $("#edit_inventory").text('Saving..');

        a.inventory_details= [{
          'inventory_name':a.inventory_details.inventory_name,
          'sell_price':a.inventory_details.sell_price,
          'cost':a.inventory_details.cost,
          'id':update_id
        }];
        
        x.post("../inventory/editinventory",a.inventory_details).success(function(response){
          
          if(response=='success') {
            //return false;
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#success_div").html("Data updated successfully. <br />Please wait,we will redirect you to listing page.");
            $("#success_div").show(); 

            localStorage.removeItem('editId');
            
            var url = 'dashboard#/inventory/list';
            window.location = url;
          } else if(response=='invalid_id') {
            
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#error_div").html("Error occoure! Please try again.");
            $("#error_div").show();
            $("#success_div").hide();

            localStorage.removeItem('editId');
            
            var url = 'dashboard#/inventory/list';
            window.location = url;
          } else {
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#error_div").html("Please insert all field.");
            $("#error_div").show();
            $("#success_div").hide(); 
          }
          
        });
      }

      a.cancel_redirect=function(){ 
          localStorage.removeItem('editId');
            
          var url = 'dashboard#/inventory/list';
          window.location = url; 
      }

    } else {
      localStorage.removeItem('editId');
            
      var url = 'dashboard#/inventory/list';
      window.location = url;
    }
}]);