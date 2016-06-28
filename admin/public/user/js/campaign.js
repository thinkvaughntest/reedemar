
"use strict";

var MyApp = angular.module("campaign-app", ["ngFileUpload","angularUtils.directives.dirPagination"]);
MyApp.controller('CampaignAddController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload","$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    a.dataLength={filtered:[]};
    a.cnames = [];
    a.campaign_details = [];
    a.Campaignedit = [];
    var site_path=$("#site_path").val();
    var update_id =$("#update_id").val();
   
    a.add_campaign_btn = "Save";
    a.add_campaign_disable =false;
    a.campaignItemIndex = 0;
    a.pagination = {};


    // localStorage.removeItem("pageNo");
    // localStorage.removeItem("EditCampaignId");
    
    var list_url='dashboard#/campaign/list';
    
    var dateToday = new Date();
    $( "#c_s_date" ).datepicker({ 

      dateFormat:"mm/dd/yy",
      minDate: dateToday,
      onSelect: function (selected) {
          var dt = new Date(selected);
          dt.setDate(dt.getDate() + 1);
          $("#c_e_date").datepicker("option", "minDate", dt);
      }
    });

    $( "#c_e_date" ).datepicker({
        dateFormat:"mm/dd/yy",
        minDate: dateToday,
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() - 1);
            $("#c_s_date").datepicker("option", "maxDate", dt);
        }
    });

    x.post("../campaign/list",update_id).success(function(data_response){     
      //console.log(JSON.stringify(data_response,null,4))    ;
        if(data_response.length){
          a.campaign_details = data_response; 
          a.file_path=site_path;
        } else {
          a.campaign_details = data_response;
        }
    });

    a.addCampaign = function(){
      a.add_campaign_disable=true;
      a.cancel_redirect = true;
      
      if($('#c_s_date').val() == '' || $('#c_e_date').val() == '' || $("#c_name").val() == '')
      {
          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#error_div").html("Please insert all field.");
          $("#error_div").show();
          $("#success_div").hide();

          a.add_campaign_disable = false;
      } else {
          //alert(JSON.stringify($('#c_s_date').val(), null, 4));add_campaign
          //return false;
          var c_s_date_arr = $('#c_s_date').val().split('/');
          var c_s_date = c_s_date_arr[2]+'-'+c_s_date_arr[0]+'-'+c_s_date_arr[1];
             
          var c_e_date_arr = $('#c_e_date').val().split('/');
          var c_e_date = c_e_date_arr[2]+'-'+c_e_date_arr[0]+'-'+c_e_date_arr[1];

          a.Campaign.c_s_date = c_s_date; 
          a.Campaign.c_e_date = c_e_date; 

         // alert(JSON.stringify(a.Campaign, null, 4));
         // return false;
          
          x.post("../campaign/addlogo",a.Campaign).success(function(response){
            if(response=='success')
            {
              a.add_campaign_btn="Saving ...";
              a.add_campaign_disable = true;

              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#success_div").html("Data inserted successfully. <br />Please wait,we will redirect you to listing page.");
              $("#success_div").show();              

              
              window.location = list_url; 
             
            }
            else
            {
              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#error_div").html("There are some issues with your internate connection. Please refresh the page.");
              $("#error_div").show();
              $("#success_div").hide(); 
            }
           
          });
      }
    };

    function getAllOffer(campaignId) {
      console.log(campaignId);
      x.post("../campaign/offerbyid",campaignId).success(function(response){
        console.log("res :: "+JSON.stringify(response, null, 4));
        a.allOffer = response;
        a.allOfferList = [];
        angular.forEach(a.allOffer, function(result, index) {
          // console.log("response :: "+JSON.stringify(result, null, 4));
          var currentd = new Date();
          var dt1 = result.end_date.split(' '),
              dt2 = dt1[0].split('-');
          var current_month = currentd.getMonth()+1; 
          var cd  = [];
          cd.push(currentd.getFullYear());
          cd.push('0'+current_month);
          cd.push(currentd.getDate());

          var one = new Date(cd[0],cd[1],cd[2]),
              two = new Date(dt2[0], dt2[1], dt2[2]);

          var millisecondsPerDay = 1000 * 60 * 60 * 24;
          var millisBetween = two.getTime() - one.getTime();
          var days = millisBetween / millisecondsPerDay;

          result.remaining_days = Math.floor(days);

          a.allOfferList.push(result);
        });
        console.log("a.allOfferList :: "+JSON.stringify(a.allOfferList, null, 4));
      });
    }

    // Function for deleting a campaign
    a.delete_campaign=function(itemId){     
      if(confirm("Are you sure?"))
      {  
        var main_site_url=$("#main_site_url").val();       
        x.get("../campaign/delete/"+itemId).success(function(response){
            r.reload();
        });
      }
    };

    a.redirect_edit=function(itemId, pageNo){
      console.log(itemId+" & "+pageNo);
      localStorage.removeItem("pageNo");
      localStorage.removeItem("EditCampaignId");
      localStorage.setItem("pageNo", pageNo);
      localStorage.setItem("EditCampaignId", itemId);
      // $("#update_id").val(itemId);
      var edit_url = 'dashboard#/campaign/edit/';    
      window.location = edit_url;
    };

    a.cancel_redirect=function(){
      window.location = list_url; 
    };

    a.showOrHideAllOffer = function (index) {
      if (a.campaignItemIndex == index) {
        a.campaignItemIndex = 0;
        localStorage.removeItem("pageNo");
      } else {
        getAllOffer(index);
        a.campaignItemIndex = index;
      }

      console.log(index);
      console.log(a.campaignItemIndex);
    };

    if(localStorage.getItem("pageNo") && localStorage.getItem("EditCampaignId")){
      a.pagination.current = localStorage.getItem("pageNo");
      a.showOrHideAllOffer(localStorage.getItem("EditCampaignId"));
    } else {
      a.pagination.current = 1;
    }

    a.addNewOffer = function(camId){
      localStorage.removeItem("campaignId");
      localStorage.setItem("campaignId", camId);
      var url = 'dashboard#/promotion/create';
      window.location = url; 
    };

    a.delete_offer= function(itemId) {
      if(confirm("Are you sure?")) {
        x.post("../promotion/softdeloffer",itemId).success(function(data_item){ 
          var edit_url = 'dashboard#/campaign/list/';    
          window.location = edit_url;
        });       
      } 
    };

    a.$watch('pagination.current', function(val){
      a.currentPageChanged = val;
      //console.log("val :: "+val);
    });
}]);

MyApp.controller('CampaignEditController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload","$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    
    a.edit_campaign_btn="Save";
    a.save_campaign_disable = false;
    var list_url = 'dashboard#/campaign/list';
    // var update_id = $("#update_id").val();
    if(localStorage.getItem("EditCampaignId")){
      var update_id = localStorage.getItem("EditCampaignId");
    } else {
      var update_id = " ";
      window.location = list_url; 
    }
    
    var dateToday = new Date();
    $( "#c_s_date" ).datepicker({ 

      dateFormat:"mm/dd/yy",
      minDate: dateToday,
      onSelect: function (selected) {
          var dt = new Date(selected);
          dt.setDate(dt.getDate() + 1);
          $("#c_e_date").datepicker("option", "minDate", dt);
      }
    });

    $( "#c_e_date" ).datepicker({
        dateFormat:"mm/dd/yy",
        minDate: dateToday,
        onSelect: function (selected) {
            var dt = new Date(selected);
            dt.setDate(dt.getDate() - 1);
            $("#c_s_date").datepicker("option", "maxDate", dt);
        }
    });

    x.post("../campaign/list",update_id).success(function(data_response){         
        if(data_response.length){
          a.campaign = data_response[0]; 
          a.c_start_date = a.campaign.start_date.split('-')[1]+'/'+a.campaign.start_date.split('-')[2]+'/'+a.campaign.start_date.split('-')[0];
          a.c_end_date = a.campaign.end_date.split('-')[1]+'/'+a.campaign.end_date.split('-')[2]+'/'+a.campaign.end_date.split('-')[0];
        } else {
          a.campaign = data_response;
        }
    });

    a.updateCampaign=function(){
      var c_s_date_arr=$('#c_s_date').val().split('/');
      var c_s_date = c_s_date_arr[2]+'-'+c_s_date_arr[0]+'-'+c_s_date_arr[1];
         
      var c_e_date_arr=$('#c_e_date').val().split('/');
      var c_e_date = c_e_date_arr[2]+'-'+c_e_date_arr[0]+'-'+c_e_date_arr[1];
      var c_name=$('#c_name').val();

      //alert(c_s_date);
      a.campaign_details= [{
        'start_date':c_s_date,
        'end_date':c_e_date,
        'campaign_name':c_name,
        'id':update_id
      }];

      console.log("Campaign :: "+JSON.stringify(a.campaign_details, null, 4));
      if($("#c_name").val() && $("#c_s_date").val() && $("#c_end_date").val()) {
          
        x.post("../campaign/editcampaign",a.campaign_details).success(function(response){
          if(response=='success')
          {
            
            a.save_campaign_disable = true;
            a.edit_campaign_btn = "Saving..."; 
            $("#update_id").val('');
            //return false;
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#success_div").html("Data updated successfully. <br />Please wait,we will redirect you to listing page.");
            $("#success_div").show(); 


       
            window.location = list_url; 
         
          }
          else if(response=='invalid_id')
          {
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#error_div").html("Error occoure! Please try again.");
            $("#error_div").show();
            $("#success_div").hide();

            
            window.location = list_url;
      
          }
          else
          {
            $("#error_div").hide();
            $("#show_message").slideDown();
            $("#error_div").html("Please insert all field.");
            $("#error_div").show();
            $("#success_div").hide(); 
          }
          
        });
      } else {
        $("#error_div").hide();
        $("#show_message").slideDown();
        $("#error_div").html("Please insert all field.");
        $("#error_div").show();
        $("#success_div").hide();
      }
    }

    a.cancel_redirect=function(){
      $("#update_id").val(''); 
      window.location = list_url; 
    }
}]);