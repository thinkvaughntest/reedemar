
"use strict";

var MyApp = angular.module("repo-app", ["ngFileUpload"]);

MyApp.controller('RepoListController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route", function (a, b, c, d, x, fu, $route) {          
    
    a.upload_repo_img_disabled = false;
    a.upload_repo_img_btn = "Upload";
    a.currentPage = 0;
    a.pageSize = 10;
    var site_path = $("#site_path").val();
    //localStorage.removeItem('pdid');
    function showAllData() {
      if(localStorage.getItem('pdid') != undefined ){
        var dir_id = localStorage.getItem('pdid');
      } else {
        var dir_id = 0;
      }

      x.get("../directory/alllisting/"+dir_id).success(function(response){
        // console.log('response :: '+JSON.stringify(response, null, 4));
        if(response.length){
          if(response[0].id != undefined){
            a.repo_details = response;
            a.setBackButton = response[0].previous_id;
            a.file_path = site_path;
          } else {
            a.repo_details = [];
            a.setBackButton = response[0].previous_id;
          }
        } else {
          a.repo_details = [];
          a.setBackButton = null;
        }
      });
    }

    

    a.set_dir_id = function(dir_id, parent_directory_id){
       // console.log("dir_id :: "+dir_id+"parent_directory_id :: "+parent_directory_id);
        if(dir_id != undefined){
          localStorage.setItem('pdid', dir_id);
        } else {
          localStorage.setItem('pdid', 0);
        }
         
        showAllData();
        
    };


    a.delete_folder = function(itemId){      
      if(confirm("Are you sure?"))
      { 
        x.get("../directory/delete/"+itemId).success(function(response){

          if(response=='success')
          {
            $route.reload();
          }
        })
      }
    };

    a.clearAlllocalStorage = function (pdid){
      localStorage.removeItem('pdid');
      localStorage.setItem('pdid', pdid);
      showAllData();
    }

    showAllData();
    
   
}]);

MyApp.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
});

MyApp.controller('RepoAddFolderController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route", function (a, b, c, d, x, fu, $route) {          
    
    a.upload_repo_img_disabled = false;
    a.upload_repo_img_btn = "Upload";

    if(localStorage.getItem('pdid')) {
      a.new_dir_id = localStorage.getItem('pdid');
    } else {
      a.new_dir_id = 0;
    }


    a.add_folder = function(){
        if(localStorage.getItem('pdid')) {
          a.repodetails.new_dir_id = localStorage.getItem('pdid');
        } else {
          a.repodetails.new_dir_id = 0;
        }
        
        // console.log("a.repodetails :: "+JSON.stringify(a.repodetails, null, 4));
        x.post("../directory/store",a.repodetails).success(function(response){
          
          if(response=="success") {               
              var redirect_url='dashboard#/repository/list';

              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#success_div").html("Data inserted successfully. <br />Please wait,we will redirect you to listing page.");
              $("#success_div").show();              

              
              window.location.href = redirect_url;
                                                
          } else if(response=="error") {
              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#error_div").html("Please insert all field.");
              $("#error_div").show();
              $("#success_div").hide();                                  
          } else if(response=="folder_exists") {
              $("#error_div").hide();
              $("#show_message").slideDown();
              $("#error_div").html("Folder you enter already exists. Please try with diffrent name.");
              $("#error_div").show();
              $("#success_div").hide();                                  
          }
      });
    };
   
}]);

MyApp.controller('RepoAddImageController',["$scope", "PlaceholderTextService", "ngTableParams", "$filter", "$http", "fileToUpload", "$route", function (a, b, c, d, x, fu, $route) {          
    
    a.upload_repo_img_disabled = false;
    a.upload_repo_img_btn = "Upload";
    // a.showPreview = false;
    a.cropMe = false;
    a.crp_data = "";
    $("#pre").hide();


    a.cropper = {};
    a.cropper.sourceImage = null;
    a.cropper.croppedImage   = null;

    if(localStorage.getItem('pdid')) {
      a.new_dir_id = localStorage.getItem('pdid');
    } else {
      a.new_dir_id = 0;
    }


    x.get("../directory/directorylist").success(function(response){
      a.cnames = response;
      //alert("A");
     // a.setBackButton = response[0].previous_id;
      //a.file_path = site_path;
     // alert(JSON.stringify(response,null,4));
    });
    // x.get("../directory/alllisting/"+a.new_dir_id).success(function(response){
    //   alert("V");
    //   console.log("response :: "+JSON.stringify(response, null, 4));
    //   a.onlyfolders = response;
    //   a.cnames = [];
    //   angular.forEach(a.onlyfolders, function(result, index) {
    //     if(result.length){
    //       var fileName = result.file_name;
    //       if(fileName.indexOf(".") < 0){
    //         a.cnames.push(result);
    //       }
    //     }
    //   });
    //   // a.cnames = response;
    // });

    // $("#file").on('change',function(){
    //   a.showPreview = true;
    // });

    // a.enable_cropping = function(){
    //   a.cropMe = true;
    // };

    // a.cancel_cropping = function(){
    //   a.cropMe = false;
    // };

$("#lock_frm").click(function(){
var db = $('.cropper-crop-box').attr("style");
$("#d_mask").attr("style",db);
var wid = $("#d_mask").width();
var hei = $("#d_mask").height();
var tp = $("#d_mask").css("top");
var lf = $("#d_mask").css("left");
lf = parseInt(lf) - 5;
tp = parseInt(tp) - 5;
console.log(tp);
wid = wid + 10 ;
hei = hei + 10 ;
$("#d_mask").css({"width":wid,"height":hei,"top":tp,"left":lf});

$("#d_mask").css({"position":"absolute"});
});

$("#unlock_frm").click(function(){
  $("#d_mask").attr("style","");
});

var console = window.console || { log: function () {} };
  var $image = $('#image');
  var $download = $('#download');
  var $dataX = $('#dataX');
  var $dataY = $('#dataY');
  var $dataHeight = $('#dataHeight');
  var $dataWidth = $('#dataWidth');
  var $dataRotate = $('#dataRotate');
  var $dataScaleX = $('#dataScaleX');
  var $dataScaleY = $('#dataScaleY');
  var options = {
        aspectRatio: 1.42 / 1,
        minCanvasWidth:100,
        minCanvasHeight:100,
        minCropBoxWidth:100,
        minCropBoxHeight:100,
        preview: '.img-preview',
        crop: function (e) {
          $dataX.val(Math.round(e.x));
          $dataY.val(Math.round(e.y));
          $dataHeight.val(Math.round(e.height));
          $dataWidth.val(Math.round(e.width));
          $dataRotate.val(e.rotate);
          $dataScaleX.val(e.scaleX);
          $dataScaleY.val(e.scaleY);
        }
      };
// console.log($download );
// console.log(typeof $download[0].download );

  // Tooltip
  $('[data-toggle="tooltip"]').tooltip();


  // Cropper
  $image.on({
    'build.cropper': function (e) {
      console.log(e.type);
    },
    'built.cropper': function (e) {
      console.log(e.type);
    },
    'cropstart.cropper': function (e) {
      console.log(e.type, e.action);
    },
    'cropmove.cropper': function (e) {
      console.log(e.type, e.action);
    },
    'cropend.cropper': function (e) {
      console.log(e.type, e.action);
    },
    'crop.cropper': function (e) {
      console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
    },
    'zoom.cropper': function (e) {
      console.log(e.type, e.ratio);
    }
  }).cropper(options);


  // Buttons
  if (!$.isFunction(document.createElement('canvas').getContext)) {
    $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
  }

  if (typeof document.createElement('cropper').style.transition === 'undefined') {
    $('button[data-method="rotate"]').prop('disabled', true);
    $('button[data-method="scale"]').prop('disabled', true);
  }
sessionStorage.past_val = "";
$( "#slider_data" ).slider({
      orientation: "vertical",
      range: "min",
      value: 1,
      min: 0,
      max: 360,
      slide: function( event, ui ) {
        if(sessionStorage.past_val == "")
        {
          sessionStorage.past_val  =  ui.value;
          $("#image").cropper("rotate", sessionStorage.past_val);
        }else
        {
          var lst = ui.value -  sessionStorage.past_val;
          $("#image").cropper("rotate", lst);
          sessionStorage.past_val = ui.value;
        }
      }
    });
//console.log($( "#slider_data" ).slider( "value" ) );
  // Download
  // if (typeof $download[0].download === 'undefined') {
  //   $download.addClass('disabled');
  // }


  // Options
  $('.docs-toggles').on('change', 'input', function () {
    var $this = $(this);
    var name = $this.attr('name');
    var type = $this.prop('type');
    var cropBoxData;
    var canvasData;

    if (!$image.data('cropper')) {
      return;
    }

    if (type === 'checkbox') {
      options[name] = $this.prop('checked');
      cropBoxData = $image.cropper('getCropBoxData');
      canvasData = $image.cropper('getCanvasData');

      options.built = function () {
        $image.cropper('setCropBoxData', cropBoxData);
        $image.cropper('setCanvasData', canvasData);
      };
    } else if (type === 'radio') {
      options[name] = $this.val();
    }

    $image.cropper('destroy').cropper(options);
  });


  // Methods
  $('.docs-buttons').on('click', '[data-method]', function () {
    var $this = $(this);
    var image_type = $(this).attr("image_type");
    var data = $this.data();
    var $target;
    var result;

    if ($this.prop('disabled') || $this.hasClass('disabled')) {
      return;
    }

    if ($image.data('cropper') && data.method) {
      data = $.extend({}, data); // Clone a new one

      if (typeof data.target !== 'undefined') {
        $target = $(data.target);

        if (typeof data.option === 'undefined') {
          try {
            data.option = JSON.parse($target.val());
          } catch (e) {
            console.log(e.message);
          }
        }
      }

      console.log($image);

      result = $image.cropper(data.method, data.option, data.secondOption);
      // $("#img_canvas").append(result);
     
      switch (data.method) {
        case 'scaleX':
        case 'scaleY':
          $(this).data('option', -data.option);
          break;

        case 'getCroppedCanvas':
          if (result) {

            // Bootstrap's Modal
            // $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);

            // if (!$download.hasClass('disabled')) {
            //   $download.attr('href', result.toDataURL('image/jpeg'));
            // }
            //console.log(result.toDataURL('image/jpeg'));

            var xp = result.toDataURL('image/png');

            if(image_type == "home_main")
            {
            $('#home_main').attr("src",xp);
            }
            if(image_type == "home_main_thumb")
            {
            $('#home_main_thumb').attr("src",xp);
            }
            if(image_type == "deal_details")
            {
            $('#deal_details').attr("src",xp);
            }
            if(image_type == "deal_details_thumb")
            {
            $('#deal_details_thumb').attr("src",xp);
            }

          }

          break;
      }

      if ($.isPlainObject(result) && $target) {
        try {
          $target.val(JSON.stringify(result));
        } catch (e) {
          console.log(e.message);
        }
      }

    }
  });


  // Keyboard
  $(document.body).on('keydown', function (e) {

    if (!$image.data('cropper') || this.scrollTop > 300) {
      return;
    }

    switch (e.which) {
      case 37:
        e.preventDefault();
        $image.cropper('move', -1, 0);
        break;

      case 38:
        e.preventDefault();
        $image.cropper('move', 0, -1);
        break;

      case 39:
        e.preventDefault();
        $image.cropper('move', 1, 0);
        break;

      case 40:
        e.preventDefault();
        $image.cropper('move', 0, 1);
        break;
    }

  });


  // Import image
  var $inputImage = $('#upload1');
  var URL = window.URL || window.webkitURL;
  var blobURL;

  if (URL) {
    $inputImage.change(function () {
      var files = this.files;
      var file;
      if (!$image.data('cropper')) {
        return;
      }

      if (files && files.length) {
        file = files[0];
        if (/^image\/\w+$/.test(file.type)) {
          blobURL = URL.createObjectURL(file);
          $image.one('built.cropper', function () {

            // Revoke when load complete
            URL.revokeObjectURL(blobURL);
          }).cropper('reset').cropper('replace', blobURL);
          $inputImage.val('');
        } else {
          window.alert('Please choose an image file.');
        }
      }
    });
  } else {
    $inputImage.prop('disabled', true).parent().addClass('disabled');
  }






    a.upload_repo_img = function(){
      console.log($('#upload1')[0]);
      localStorage.getItem('pdid');

      var token=$("#token").val();

      // if(a.cropMe){
      //   var imageData = a.cropper.croppedImage;
      // } else {
      //   var imageData = a.cropper.sourceImage;
      // }

      //var imageData = $('#img_canv').attr("src"); ;
 
      
        var data = {
        'dir_id' : $("#dir_id").val(),
        'image_name' : $("#image_name").val(),
        'home_img' : $("#home_main").attr("src"),
        'image_type' : "image/png",
        'home_thumb_img' : $("#home_main_thumb").attr("src"),
        'deal_details_img' : $("#deal_details").attr("src"),
        'deal_details_thumb_img' : $("#deal_details_thumb").attr("src")
      }

      if(data.dir_id == null ||  data.dir_id == "? undefined:undefined ?" || data.image_name == null || data.image_name == null  ){
        $("#error_div").hide();
        $("#show_message").slideDown();
        $("#error_div").html("Please insert required fields.");
        $("#error_div").show();
        $("#success_div").hide();

        $('#add_inventory').prop('disabled', false);
        $("#add_inventory").text('Save');
      } else {

        if( data.home_img == "" || data.home_thumb_img == "" || data.deal_details_img == "" || data.deal_details_thumb_img == "" )
        {

        $("#error_div").hide();
        $("#show_message").slideDown();
        $("#error_div").html("Please crop all types.");
        $("#error_div").show();
        $("#success_div").hide();

        $('#add_inventory').prop('disabled', false);
        $("#add_inventory").text('Save');

        }else
        {

                    x.post('../directory/upload', data).success(function(response){
             //console.log(response);
             //alert(JSON.stringify(response,null,4));
              switch(response.status) {
                case 'success':
                    a.upload_repo_img_disabled = true;
                    a.upload_repo_img_btn = "Uploading ...";

                    $("#error_div").hide();
                    $("#show_message").slideDown();
                    $("#success_div").html("Data inserted successfully. <br />Please wait,we will redirect you to listing page.");
                    $("#success_div").show();              

                    var redirect_url='dashboard#/repository/list';
                    window.location = redirect_url; 
                    break;
                case 'already_exists':
                    $("#error_div").hide();
                    $("#show_message").slideDown();
                    $("#error_div").html("Image with same name already exists. <br /> Please try with a diffrent name.");
                    $("#error_div").show();
                    $("#success_div").hide();
                    break;
              }
          });

        }
        

      }
         
    }

 

    $('#upload1').on('change', function () { 
        $("#pre").show();
        console.log($('#upload1')[0]);

    });
   
    // $('.upload-result').on('click', function (ev) {
    //   $uploadCrop.croppie('result', {
    //     type: 'canvas',
    //     size: 'viewport'
    //   }).then(function (resp) {
    //    console.log(resp);
    //   });
    // });




    
   
}]);


