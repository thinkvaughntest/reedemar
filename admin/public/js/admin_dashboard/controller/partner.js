"use strict";

var MyApp = angular.module("partner-app", []);
MyApp.controller('PartnerListController',["$scope", "$http", "$route",function (a, b, c) {
	
	//Get Url
	var site_path=$("#site_path").val();   

	//Get list of all redeemar
	b.post("../admin/dashboard/reedemarlist").success(function(data_response){              
		a.reedemar_details = data_response;                
        a.file_path=site_path; 
        a.img_file_path =site_path;       
    });

	//Update status of Reedemar
	a.update_status=function(itemId,itemStatus){
		b.get("../admin/dashboard/statusupdate/"+itemId+"/"+itemStatus).success(function(response){
			a.status=response;                 
			window.location.reload();             
		})
	}

	// a.redirect_edit=function(itemId){            
	// 	$("#update_id").val(itemId);
	// 	//alert(itemId)              ;
	// 	var main_site_url=$("#main_site_url").val(); 
	// 	var edit_url=main_site_url+'/admin/dashboard#/tables/edit/';    
	// 	window.location.href = edit_url;             
	// }

}]);