
"use strict";

var MyApp = angular.module("partnersetting-app", ["ngFileUpload","angularUtils.directives.dirPagination"]);

MyApp.controller('PartnersettingListController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route", function (a, b, c, d, x, fu, $route) {          
    //clearing local storage
    localStorage.removeItem('pdid');
    localStorage.removeItem("itemId");
    localStorage.removeItem("rating");

    a.dataLength={filtered:[]};
    var site_path=$("#site_path").val();
    var update_id =$("#update_id").val();

    x.post("../partnersetting/list").success(function(data_response){              
        a.settings_details = data_response;
        a.file_path = site_path;
    });

    a.redirect_edit=function(itemId, rating){
      var data = {
        itemId : itemId,
        rating : rating
      };

      localStorage.setItem("itemId",itemId);
      localStorage.setItem("rating",rating);
      var edit_url = 'dashboard#/settings/edit/';    
      window.location.href = edit_url;
    };

}]);

MyApp.controller('PartnersettingEditController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route", function (a, b, c, d, x, fu, $route) {          
    localStorage.removeItem('pdid');
    if(localStorage.getItem("itemId") && localStorage.getItem("rating")){
      a.alreadySelectedRange = "";
      a.edit_inventory_btn = "Save";
      a.cancel_redirect_btn = "Back";

      x.get("../partnersetting/allrange").success(function(range){              
        a.range_details = range; 
      });

      var update_id = localStorage.getItem("itemId");
      a.alreadySelectedRange = localStorage.getItem("rating");

    } else {
      var redirect_url = 'dashboard#/'+folder_name+'/list';
      window.location.href = redirect_url;
    }

    a.updateSetting=function(){
       
      a.edit_inventory_disable = true;
      a.cancel_redirect_disable = true;
      a.edit_inventory_btn = "Saving ...";

      a.settings_details= [{
        'price_range_id': a.selectedRange,
        'update_id': update_id                             
      }];
      
      x.post("../partnersetting/update",a.settings_details).success(function(response){
        var redirect_url='dashboard#/settings/list';
        
        if(response=='success')
        {
          //return false;
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#success_div").html("Data updated successfully. <br />Please wait,we will redirect you to listing page.");
          $("#success_div").show();
          localStorage.removeItem("itemId");
          localStorage.removeItem("rating");
          window.location.href = redirect_url; 
         
        }
        else if(response=='id_not_match')
        {
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#error_div").html("Some problem occoure! Please logout and login again.");
          $("#error_div").show();
          $("#success_div").hide(); 
        }        
        else
        {
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#error_div").html("Some problem occoure! Please check all input.");
          $("#error_div").show();
          $("#success_div").hide(); 

          a.edit_inventory_disable = false;
          a.cancel_redirect_disable = false;
          a.edit_inventory_btn = "Save";
        }
        
      });
    }

    a.cancel_redirect=function(folder_name){
        localStorage.removeItem("itemId");
        localStorage.removeItem("rating");
        var redirect_url = 'dashboard#/settings/list';
        window.location.href = redirect_url; 
    }
}]);