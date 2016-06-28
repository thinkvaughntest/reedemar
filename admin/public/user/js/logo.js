
"use strict";

var MyApp = angular.module("logo-app", ["ngFileUpload"]);
MyApp.controller('LogoController',["$scope", "$route", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "$route", "fileToUpload", function (a, r, b, c, d, x, $route, fu) {          
    localStorage.removeItem('pdid');
    var site_path=$("#site_path").val();
    a.searchText   = '';     // set the default search/filter term          
    $("#logo_details_div").hide();
    $("#loading_div").hide();
    a.save_logo_btn = "save"; 
    a.add_logo_btn = "UPLOAD";
    a.currentImage = 0;

    x.post("../admin/dashboard/logobyuser").success(function(data_response){              
        a.logo_details = data_response;
        a.file_path=site_path;
        $("#logo_image_first").attr("src",a.logo_details.logo_name);
    });

    // x.get("../partner/checklogin").success(function(login_response){
    //   if(login_response=="logout")
    //   {                ;
    //     window.location.href = "http://localhost/reedemer/admin/public/auth/login";
    //   }
    //   //alert(login_response);
    // });
  $(document).ready(function() {
      // $(".various").fancybox({
      //   maxWidth  : 800,
      //   maxHeight : 600,
      //   fitToView : false,
      //   width   : '70%',
      //   height    : '70%',
      //   autoSize  : false,
      //   closeClick  : false,
      //   openEffect  : 'none',
      //   closeEffect : 'none'
      // });
    });

    a.updateAction = function(itemId){
        alert("v->"+itemId);
    }


    a.add_logo = function(){
      //alert("A");
      //return false;
      var file = a.myFile;
      var logo_name=$("#logo_name").val() ;

      a.add_logo_btn = "saving ...";
      a.add_logo_disable = true ; 

      var ext = $('#logo_name').val().split('.').pop().toLowerCase();
     // alert(ext);
      if($.inArray(ext, ['jpg','jpeg']) == -1) {
        
        // showing error message if image not upload or not .jpg type 
        $("#notification_success").hide();
        $("#notification_info").hide();
        $("#notification").slideDown();
        $("#notification_error").html("Please upload only .jpg /.jpeg image.");       

        a.add_logo_disable = false ; 
        a.add_logo_btn = "UPLOAD";

        setTimeout(function() { 
          $("#notification").slideUp();
        }, 5000);      

        return false;
      }

      a.logo=[];
      var uploadUrl = "../admin/dashboard/uploadlogo";  
      // fu.uploadNewFileToUrl(file, uploadUrl, logo_name).then(function(fdata){
        
      //     var logo_name = fdata.data;
      //     a.logo.logo_name=logo_name;

      //     var logo_text='demo';
      //     if($("#enhance_logo").prop("checked")==true)
      //     {
      //       var enhance_logo = 1;
      //     }
      //     else
      //     {
      //      var enhance_logo = 0; 
      //     }
      //     a.logo.logo_name = enhance_logo;

      //     x.get("../admin/dashboard/addlogo/"+logo_text+"/"+logo_name+"/"+enhance_logo).success(function(response_back){
            
      //       if(response_back.response=="success")
      //       {
      //           $("#notification_success").hide();
      //           $("#notification_error").hide();
      //           $("#notification").slideDown();
      //           $("#notification_info").html("Data inserted successfully. It can take maximum 5 minutes to receive your image rating.");
      //           $("#notification_info").show();              

      //           setTimeout(function() { 
      //             $("#notification").slideUp();
      //           }, 5000);                                         
      //       }
      //     })         
      // });
    };

    
    a.save_logo=function(){      
        var logo_details=a.userlogo;
        var main_site_url=$('#main_site_url').val();
        x.post("../admin/dashboard/updatestatus",logo_details).success(function(response){
      
          a.save_logo_btn = "saving ...";       

          $("#error_div").hide();
          $("#show_message").slideDown();
          $("#success_div").html("Thank you for choosing this logo.");
          $("#success_div").show(); 
          $route.reload();
       });
    };

     function OnGetFile (e) {
            console.log("hi");
            e.stopPropagation();
            e.preventDefault();
            var file = null;
            if (e.dataTransfer) {// file drag and drop
              file = e.dataTransfer.files[0] || null;
            } else if ($("#logo_name")[0].files) {// file upload
              file = $("#logo_name")[0].files[0] || null;
            }
            if (!file) {
              return;
            }

            var reader = new FileReader();
            reader.readAsDataURL(file, "UTF-8");
            reader.onload = function (e) {
             // $("#filename").html("Result: '"+ file.name +"' ("+ e.target.result.length +" B)");
              // $("#result").val(e.target.result);
              //console.log(e.target.result);
            $("#img_data").empty();
            $("#img_data").append("<img id='image_src' src='"+e.target.result+"' style='width:200px;height:200px'>");
            };
            reader.onerror = function (e) {
              //console.log(e.target.error);

             // $("#result").val(e.target.error);
            };
          }
     

        // if (window.File && window.FileReader && window.FileList && window.Blob) {
        // document.getElementById('logo_name').addEventListener('change', OnGetFile, false);
        // } else {
        // alert('The File APIs are not fully supported in this browser.');
        // }

     
        function send_data()
        {
          var file_data = $("#logo_name").prop("files")[0];   // Getting the properties of file from file field
          var form_data = new FormData();
          x = $("#image_src");
          var resp = x[0].currentSrc;
          var fd = new FormData(); 

          // Creating object of FormData class
          var site_path=$("#site_path").val();
          var ext = $('#logo_name').val().split('.').pop().toLowerCase();
          //alert(file_data.type+"---"+ext);
          form_data.append("logo_image", resp);
          form_data.append("image_type", ext);
          
   
          $.ajax({
          url: '../partner/uploadlogoback',
          type: "POST",
          data:  form_data,
          contentType: false,
          cache: false,
          processData:false,
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },        
          success: function(response){ 
        //  alert(JSON.stringify(response,null,4));
          //alert(JSON.stringify(response.response,null,4));
            if(response.response=='success')
            { 
             // alert("a");
              window.location="dashboard#/tables/logo";
            }
            if(response=='already_exists')
            {
              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#error_div").html("Image with same name already exists. <br /> Please try with a diffrent name.");
              $("#error_div").show();
              $("#success_div").hide();
            }
          }
        }); 

        }

    $("#logo_name").on('change',function(e){
          e.stopPropagation();
            e.preventDefault();
            var file = null;
            if (e.dataTransfer) {// file drag and drop
              file = e.dataTransfer.files[0] || null;
            } else if ($("#logo_name")[0].files) {// file upload
              file = $("#logo_name")[0].files[0] || null;
            }
            if (!file) {
              return;
            }
           // alert("a");
            var reader = new FileReader();
            reader.readAsDataURL(file, "UTF-8");
            reader.onload = function (e) {
             // $("#filename").html("Result: '"+ file.name +"' ("+ e.target.result.length +" B)");
              // $("#result").val(e.target.result);
             // console.log(e.target.result);
            $("#img_data").empty();
            $("#img_data").append("<img id='image_src' src='"+e.target.result+"' style='width:200px;height:200px'>");
            };
            reader.onerror = function (e) {
              //console.log(e.target.error);

             // $("#result").val(e.target.error);
            };
             setTimeout(send_data, 2000);

    });

     

  

    a.delete_user_logo=function(itemId){ 
      if(confirm("Are you sure?"))
      { 
        x.get("../admin/dashboard/deletelogo/"+itemId).success(function(response){
          
          $route.reload();  
                     
        })
      }
    };

    a.show_rating=function(itemId){                
       var main_site_url=$('#main_site_url').val();
       var site_path=$('#site_path').val();

       $("#loading_div").show();  
       $("#rating_div").hide(); 
       $("#logo_details_div").hide();
       
       x.get("../admin/dashboard/logodetails/"+itemId).success(function(data_response){
            $("#logo_details_div").show();
            $("#loading_div").hide();  
            $("#rating_div").show(); 
            a.tracking_rating=data_response[0].tracking_rating;
            a.logo_name=data_response[0].logo_name;    
            a.target_id=data_response[0].target_id;  
            
            $("#logo_image_first").attr("src", site_path+'../uploads/original/'+a.logo_name)
            
            a.userlogo = {user_logo_id: itemId,user_logo_target_id:a.target_id};          
            $( "#rateYo" ).hide();
            $( "#rating_div" ).after( '<div id="rateYo"></div>' );
            $("#rateYo").rateYo({
                rating: a.tracking_rating,
                readOnly: true
            });
       });         
    };

    a.make_default=function(itemId){ 

      x.get("../admin/dashboard/updatedefault/"+itemId).success(function(response){
                         
        if(response=='success') {             
          r.reload();               
        }                
      });
    };

    
}]);