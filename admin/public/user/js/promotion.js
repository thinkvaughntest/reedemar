
"use strict";

var MyApp = angular.module("promotion-app", ["ngFileUpload", 'rzModule', 'ui.bootstrap']);

MyApp.controller('PromotionController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload","$route",function (a, b, c, d, x, fu, r) {          
    localStorage.removeItem('pdid');
    var site_path = $("#site_path").val();
    a.selectedCampaign = null;

    a.selected_validate_after_Hour = 0;
    a.selected_validate_after_Days = 0;
    a.validate_after = 0;

    a.selected_validate_within_Hour = 0;
    a.selected_validate_within_Days = 0;
    a.validate_within = 0;

    // $( "#c_s_date" ).datepicker({ 

    //     dateFormat:"mm/dd/yy",
    //     minDate: dateToday,
    //     onSelect: function (selected) {
    //         var dt = new Date(selected);
    //         dt.setDate(dt.getDate() + 1);
    //         $("#c_e_date").datepicker("option", "minDate", dt);
    //     }
    //   });

    $( "#c_s_date" ).datepicker({         
      dateFormat:"mm/dd/yy", 
      minDate: a.camp_min_date,   
      //minDate: '06/21/2016',       
      onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() + 1);
        $("#c_e_date").datepicker("option", "minDate", dt);
      }

    });
    
    //$("#total_redeemar").keyup(function(){
      var maxLength = 10;
      $('#offer_description').keyup(function() {
        var length = $(this).val().length;
        var length = maxLength-length;
       // $('#chars').text(length);
       //alert(length);
      });
   // });
    $( "#c_e_date" ).datepicker({
      dateFormat:"mm/dd/yy",
      onSelect: function (selected) {
        var dt = new Date(selected);
        dt.setDate(dt.getDate() - 1);
        $("#c_s_date").datepicker("option", "maxDate", dt);
        calculate_date_diff();
      }     

    });    
   
    x.post("../campaign/list").success(function(response){ 
      a.campaign_list = response; 
      a.file_path = site_path;
      a.selectedCampaign = localStorage.getItem("campaignId");
      if(a.selectedCampaign){
        a.campaign_id = a.selectedCampaign;
      }      
    }); 
   
    

    x.get("../admin/dashboard/owncategory").success(function(response_cat){ 
      a.cat_list = response_cat;        
    }); 

    x.post("../inventory/list").success(function(inventory_list){ 
      a.inventory_list = inventory_list;  
    });

    x.post("../promotion/currentuser").success(function(inventory_list){ 
      a.current_user = inventory_list;  
    }); 

    x.get("../promotion/index").success(function(promotion_list){ 
      //console.log(JSON.stringify(promotion_list,null,4));
      a.promotion_list = promotion_list;  
    });  
     x.post("../partnersetting/list").success(function(data_response){    
       //console.log(JSON.stringify(data_response[0].price_range_id,null,4));
       a.reedemar_price_range=data_response[0].price_range_id;
     }); 
   // a.reedemar_price_range=setting_val.price_range_id;
   // console.log(JSON.stringify(a.reedemar_price_range,null,4));
   

    $('input[name="choose_image"]').on('change', function() {
      var selectd_val=$('input[name="choose_image"]:checked').val(); 
      if(selectd_val==2)
      {
        $(".image_chooser").hide();
        x.post("../promotion/defaultlogo").success(function(response){           
          var img_pathShow=site_path+'../uploads/original/'+response; 
          $(".logo_img_box").hide();
          $(".campaign_image_show").attr("src", img_pathShow);
        }); 
      }
      else
      {
          $(".image_chooser").show();
          $(".logo_img_box").show();
          var img_pathShow=site_path+'../uploads/no-image-found.gif';    
          //alert(img_pathShow+"B")  ;
          $(".campaign_image_show").attr("src", img_pathShow);   
          //http://localhost/reedemer/admin/uploads/
      }
       
       
    });
   // a.open_pop = function(item){  
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
   // }
    a.delete_offer= function(itemId) {
      if(confirm("Are you sure?")) {  
        x.post("../promotion/softdeloffer",itemId).success(function(data_item){ 
          r.reload();
        });       
      } 
    };

    a.choices = [{id: '1'}];
    a.addNewChoice = function() {
      var newItemNo = a.choices.length+1;
      a.choices.push({'id':newItemNo});
    };
      
    a.removeChoice = function() {
      var lastItem = a.choices.length-1;
      a.choices.splice(lastItem);
    };

    a.get_inventory = function(item, choices){
      var inventory_id=item;     
      x.post("../inventory/inventorydetails",inventory_id).success(function(data_item){        
        a.inventory_item=data_item;
        a.file_path=site_path;        
        var costId = "#cost"+choices;
        $(costId).val(data_item.cost);
        var imageDivId = "#selling_price"+choices;
        $(imageDivId).val(data_item.sell_price);
        var src=site_path+"../uploads/inventory/original/"+data_item.inventory_image;
        var inventoryImageId = "#inventory_image"+choices;
        $((inventoryImageId)).attr("src", src);
        calculate();       
      }); 
    }   

    a.offer_description = "";
    a.what_you_get = "";
    a.cost = "";



    a.validate_after_days = {
      value: 5,
      options: {
        value: 100000,
        showTicksValues: true,
        stepsArray: [
          {value: 0, legend: ''},
          {value: 24, legend: '1 Day'},
          {value: 48, legend: '2 Days'},
          {value: 72, legend: '3 Days'},
          {value: 96, legend: '4 Days'},
          {value: 120, legend: '5 Days'}
        ],
        onEnd: function(id, newValue, highValue, pointerType) {
          console.log(newValue+ "Hours");

          a.selected_validate_after_Hour = newValue;
          a.validate_after = a.selected_validate_after_Hour + a.selected_validate_after_Days;
          // a.otherData.end = newValue * 10;
        }
      }
    };

    a.validate_after_hour = {
      value: 0,
      options: {
        ceil: 24,
        floor: 0,
        showTicks: 4,
        onEnd: function(id, newValue, highValue, pointerType) {
          console.log(newValue+ "Hours");
          a.selected_validate_after_Days = newValue;
          a.validate_after = a.selected_validate_after_Hour + a.selected_validate_after_Days;
          // a.otherData.end = newValue * 10;
        }
      }
    };

    a.validate_within_days = {
      value: 5,
      options: {
        value: 100000,
        showTicksValues: true,
        stepsArray: [
          {value: 0, legend: ''},
          {value: 24, legend: '1 Day'},
          {value: 48, legend: '2 Days'},
          {value: 72, legend: '3 Days'},
          {value: 96, legend: '4 Days'},
          {value: 120, legend: '5 Days'}
        ],
        onEnd: function(id, newValue, highValue, pointerType) {
          a.selected_validate_within_Hour = newValue;
          a.validate_within = a.selected_validate_within_Hour + a.selected_validate_within_Days;
          // a.otherData.end = newValue * 10;
        }
      }
    };

    a.validate_within_hour = {
      value: 0,
      options: {
        ceil: 24,
        floor: 0,
        showTicks: 4,
        onEnd: function(id, newValue, highValue, pointerType) {
          a.selected_validate_within_Days = newValue;
          a.validate_within = a.selected_validate_within_Hour + a.selected_validate_within_Days;
          // a.otherData.end = newValue * 10;
        }
      }
    };
 
    a.include_price=function()
    {      
      // this function will get executed every time the #home element is clicked (or tab-spacebar changed)
      if($("#include_product_value").is(":checked")) // "this" refers to the element that fired the event
      {
        //var selling_price_class_items = $('.selling_price_class').length;
        
        // Sum Retails value price
        var total = 0;
        var $changeInputs = $('input.selling_price_class');
        $changeInputs.each(function(idx, el) 
        {
          total += Number($(el).val());
        });
        $('#retails_value').val(total);
        a.set_discount();
        
      }
    }

    a.retails_value_change = function (){
      $("#include_product_value").prop("checked", false);
      a.set_discount();
    };


    function get_all_data()
    {
        var total = 0;
        var $changeInputs = $('input.selling_price_class');
        $changeInputs.each(function(idx, el) 
        {
          total += Number($(el).val());
        });
        $('#retails_value').val(total);
        a.set_discount();
    }

    a.set_discount = function()
    {
      //alert("b");
      var retails_value = $("#retails_value").val();
      var pay_value = $("#pay_value").val();

      var discount=parseFloat(retails_value)-parseFloat(pay_value);

      var value_calculate=$("input[name=value_calculate]:checked").val();

      if(value_calculate == 2 || value_calculate == 4 || value_calculate == 6)
      {
        var total_discount=discount.toFixed(2);
        var total_discount_show='$'+total_discount;
      }
      if(value_calculate == 1 || value_calculate == 3 || value_calculate == 5)
      {
        var total_discount = ((discount/retails_value)*100).toFixed(2);
        var total_discount_show=total_discount+'%';
      }
      if(value_calculate == 1 || value_calculate == 2)
      {
        var save_text="OFF";
      }
      if(value_calculate == 3 || value_calculate == 4)
      {
        var save_text="DISCOUNT";
      }
      if(value_calculate == 5 || value_calculate == 6)
      {
        var save_text="SAVING";
      }
      $("#off_value").val(total_discount);
      $("#discount_value").val(total_discount);
      $("#saving_value").val(total_discount);
      $(".save_value_show").html(total_discount_show);
      $(".save_text_show").html(save_text);
      var value_show_text="$"+retails_value+" value";
      $("#value_show").html(value_show_text);
    }

    function calculate_date_diff()
    {
      var c_e_date = $("#c_e_date").val();
      var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
      var firstDate = new Date();
      var secondDate = new Date(c_e_date);
      var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)))+1;
      
      var expire_in_text="* expires in "+diffDays+" days ";
     
      $(".expire_in_show").html(expire_in_text);
    }
 
   
  // Change total price value with no of redeemar change
  $( '#total_redeemar' ).keyup(function() {       
     var selectd_val=$('input[name="total_payment"]:checked').val();       
     var total_price=selectd_val*$("#total_redeemar").val();
            
     $("#total_redeemar_price").val(total_price);
  });   

  // Change total price value with radio button click
  $('input[name="total_payment"]').on('change', function() {
     var selectd_val=$('input[name="total_payment"]:checked').val();        
     var total_price=selectd_val*$("#total_redeemar").val();      
     
     $("#total_redeemar_price").val(total_price);
  });



    // onkeyup in Gross Margin field
    a.new_price_calculate=function()    
    {
      var gross_margin=parseFloat(a.gross_margin);
      var margin_cost=parseFloat(a.margin_cost);
      //alert(gross_margin);
      a.margin_price=gross_margin+margin_cost;
      a.margin_markup=((gross_margin/margin_cost)*100).toFixed(2);
      a.pay_value=a.margin_price;
      get_all_data();
    }

    // onkeyup in markup field
    a.new_price_markup=function()    
    {       
       var margin_cost=parseFloat(a.margin_cost);
       var margin_markup=parseFloat(a.margin_markup);
       //alert(margin_markup);
       a.gross_margin=((margin_markup/100)*margin_cost).toFixed(2);;
       a.margin_price=(margin_cost+parseFloat(a.gross_margin)).toFixed(2);
       a.pay_value=a.margin_price;
       get_all_data();
    }

    // onkeyup in new price field
    a.offer_new_price=function()    
    {

       var margin_cost=parseFloat(a.margin_cost);
       var margin_price=parseFloat(a.margin_price);
       //alert(margin_markup);
       a.gross_margin=(margin_price-margin_cost).toFixed(2);;
       a.margin_markup=((parseFloat(a.gross_margin)/margin_cost)*100).toFixed(2);
       a.pay_value=a.margin_price;
       get_all_data();
    }     

  a.add_offer=function()
  {   
     var inventoryId=null;  
     var product_id_str='';
     $(".inventory_class").each(function(){
        inventoryId = $(this).attr('id');       
        var product_id=$("#"+inventoryId).val();        
        product_id_str+=product_id+',';        
     });
  
    a.promotion_arr={};
    var campaign_id=$("#campaign_id").val().split(':')[1];
    var category_id=$("#category_id").val();
    var subcat_id=$("#subcat_id").val();
    var offer_description=$("#offer_description").val();
    var total_redeemar=$("#total_redeemar").val();
    var total_redeemar_price=$("#total_redeemar_price").val();
    var c_s_date=$("#c_s_date").val();
    var c_e_date=$("#c_e_date").val();
    var total_payment=$("input[type='radio'][name='total_payment']:checked").val();
    var what_you_get=$("#what_you_get").val();
    var more_information=$("#more_information").val();
    var pay_value=$("#pay_value").val();
    var retails_value=$("#retails_value").val();
    // var pay_value = a.pay_value;
    // var retails_value = a.retail_value;
    var include_product_value=$("#include_product_value").val();    
    // var value_calculate=$("input[type='radio'][name='value_calculate']:checked").val();
    var value_calculate = a.value_calculate;
    var discount='';
    var product_id_str=product_id_str.replace(/^,|,$/g,'');
    var camp_img_id=$("#camp_img_id").val();
    var choose_image=$("input[type='radio'][name='choose_image']:checked").val();
    var validate_after=$("#validate_after").val();
    var validate_within=$("#validate_within").val();

    if(value_calculate==1 || value_calculate==2) {
      var value_calculate=1;
      var off_value = $("#off_value").val();
      discount = off_value;
    } else if(value_calculate==3 || value_calculate==4) {
      var value_calculate=2;
      var discount_value=$("#discount_value").val();
      discount=discount_value;
    } else if(value_calculate==5 || value_calculate==6) {
      var value_calculate=3;
      var saving_value=$("#saving_value").val();
      discount = saving_value;
    }

    
    a.promotion_arr.campaign_id=campaign_id;
    a.promotion_arr.category_id=category_id;
    a.promotion_arr.subcat_id=subcat_id;
    a.promotion_arr.offer_description=offer_description;
    a.promotion_arr.total_redeemar=total_redeemar;
    a.promotion_arr.total_redeemar_price=total_redeemar_price;
    a.promotion_arr.c_s_date=c_s_date;
    a.promotion_arr.c_e_date=c_e_date;
    a.promotion_arr.total_payment=total_payment;
    a.promotion_arr.what_you_get=what_you_get;
    a.promotion_arr.more_information=more_information;

    a.promotion_arr.value_calculate=value_calculate;
    a.promotion_arr.pay_value=pay_value;
    a.promotion_arr.retails_value=retails_value;
    a.promotion_arr.include_product_value=include_product_value;
    a.promotion_arr.discount=discount;
    a.promotion_arr.product_id_str=product_id_str;
    a.promotion_arr.camp_img_id=camp_img_id;
    a.promotion_arr.choose_image=choose_image;
    a.promotion_arr.validate_after=validate_after;
    a.promotion_arr.validate_within=validate_within;

    x.post("../promotion/storeoffer",a.promotion_arr).success(function(response){
       if(response=='success')
       {
          var main_site_url=$("#main_site_url").val();
                                
          var redirect_url=main_site_url+'/user/dashboard#/promotion/list';  

          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#success_div").html("Data inserted successfully. <br />Please wait,we will reload this page.");
          $("#success_div").show();              
          localStorage.removeItem("campaignId"); 
         
            //$route.reload();
          window.location.href = redirect_url; 
         
       }

     });
  }

    a.calculate_margin=function()    
    {   
       calculate();
    }

    function calculate (){
      var total = 0;
      var $changeInputs = $('input.inventory_cost');
      $changeInputs.each(function(idx, el) 
      {
        total += Number($(el).val());
      });
          
      if(total>0)
      {
        a.margin_cost=total+1;
      }
      else
      {
        var margin_cost=$("#margin_cost").val()+1;
      } 
    }
}]);