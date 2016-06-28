
"use strict";

var MyApp = angular.module("product-app", ["ngFileUpload","angularUtils.directives.dirPagination"]);

MyApp.controller('ProductController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    
    a.dataLength={filtered:[]};
    a.cnames = [];
    a.product_details = [];
    a.product_details.product_image = [];

    //a.disableaddproduct = false;
    a.addproductbtn="Save";
    a.updateProduct_disabled = false;

    a.showPreview = false;
    a.cropMe = false;

    a.cropper = {};
    a.cropper.sourceImage = null;
    a.cropper.croppedImage   = null;

    //Convert image into base64
    $('#product_image').on('change', function(e){  
     // alert(JSON.stringify(e.target,null,4))        ;
     // return false;
      $(this).base64img({
        url: e.target.files[0],
        result: '#product_image_encode'
      });
    });
    
    $(document).ready(function() {
        $(".various").fancybox({
          maxWidth  : 800,
          maxHeight : 600,
          fitToView : false,
          width   : '70%',
          height    : '70%',
          autoSize  : false,
          closeClick  : false,
          openEffect  : 'elastic',
          closeEffect : 'none',
          loop  : false,
        });
      });
    var site_path=$("#site_path").val();
   // alert(update_id);
    x.post("../product/list", update_id).success(function(data_response){              
        a.product_details = data_response; 
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

    a.addProduct = function(){ 


      if($("#product_name").val()=='' || $("#sell_price").val()=='' || $("#cost").val()=='' || $("#retail_price").val()=='') {
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#error_div").html("Please insert all fields.");
          $("#error_div").show();
          $("#success_div").hide();

          $('#add_product').prop('disabled', false);
          $("#add_product").text('Save');
          return false;
      } else {        
        //var image_data=$("#product_image_encode").text();
        var image_id=$("#image_id").val();
        
        a.product_details= [{
          'product_name':a.Product.product_name,
          'sell_price':a.Product.sell_price,
          'cost':a.Product.cost,
          'retail_price':a.Product.retail_price,
          'image_id':image_id
        }];

      // alert(JSON.stringify(a.product_details,null,4));
       //return false;   

        x.post("../product/store",a.product_details).success(function(response){         
           var url = 'dashboard#/product/list';
           switch(response) {
            case 'success':
                    a.Product = {};
                    $("#error_div").hide();
                    $("#show_message").slideDown();
                    $("#success_div").html("Data inserted successfully. <br />Please wait,we will reload this page.");
                    $("#success_div").show();
                    
                    var url = 'dashboard#/product/list';
                    window.location = url;
                    break;
                case 'image_not':
                    a.Product.product_image = "";
                    $("#error_div").hide();
                    $("#show_message").slideDown();
                    $("#error_div").html("Unable to upload image. Please try again.");
                    $("#error_div").show();
                    $("#success_div").hide();
                    return false;  
                    break;
                default:
                    $("#error_div").hide();
                    $("#show_message").slideDown();
                    $("#error_div").html("Please insert all field.");
                    $("#error_div").show();
                    $("#success_div").hide();
                    break;
           }
           window.location = url;
           //break;
        });        
      }
    };

    // Function for deleting a Product
    a.delete_product=function(itemId){ 
    
      if(confirm("Are you sure?")) {    
        x.get("../product/delete/"+itemId).success(function(response){
          r.reload();
        });
      }
    };

    a.redirect_edit=function(itemId){
      //alert(itemId);
      //return false;
      localStorage.setItem('editId', itemId);
      var url = 'dashboard#/product/edit';
      window.location = url;
    }

}]);

// Edit Product data
MyApp.controller('ProductEditController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    a.product_details = [];
    a.updateProductbutton="Save";
    
    if(localStorage.getItem('editId')){
      var update_id = JSON.parse(localStorage.getItem('editId'));
      x.post("../product/list",update_id).success(function(data_response){              
          a.product_details = data_response[0]; 
      });

      a.updateProduct=function(){
        $('#edit_product').prop('disabled', true);
        $("#edit_product").text('Saving..');

        a.product_details= [{
          'product_name':a.product_details.product_name,
          'sell_price':a.product_details.sell_price,
          'cost':a.product_details.cost,
          'id':update_id
        }];
        
        x.post("../product/editproduct",a.product_details).success(function(response){
          
          if(response=='success') {
            //return false;
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#success_div").html("Data updated successfully. <br />Please wait,we will redirect you to listing page.");
            $("#success_div").show(); 

            localStorage.removeItem('editId');
            
            var url = 'dashboard#/product/list';
            window.location = url;
          } else if(response=='invalid_id') {
            
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#error_div").html("Error occoure! Please try again.");
            $("#error_div").show();
            $("#success_div").hide();

            localStorage.removeItem('editId');
            
            var url = 'dashboard#/product/list';
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
            
          var url = 'dashboard#/product/list';
          window.location = url; 
      }

    } else {
      localStorage.removeItem('editId');
            
      var url = 'dashboard#/product/list';
      window.location = url;
    }
}]);