<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Http\Response; 
use App\Model\User;
use Hash;
use Validator;
use App\Model\Logo;
use App\Helper\vuforiaclient;
use App\Helper\helpers;
use Auth; 
use Session ;
use App\Model\Campaign;
use App\Model\Inventory;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use File;


class InventoryController extends Controller {
	
	
	//public function __construct( )
	//{
		//$this->middleware('auth');
		//Auth::logout();
    	//return redirect('auth/login');	
		
	//}

	public function __construct( )
	{
		if($this->middleware('auth'))
		//if(!Auth::user()->id)
		{
			Auth::logout();
    		return redirect('auth/login');	
    	}
		
	}

	public function postList(Request $request)
	{		
		//dd($request[0]);
		// Get current logged in user ID
		$created_by=Auth::user()->id;

		// Get current logged in user TYPE
		$type=Auth::user()->type;
		if($request[0]!="")
		{
			$id=$request[0];
			$inventory=Inventory::where('status',1)
						  ->where('id',$id)	
						  ->orderBy('id','DESC')					 
						  ->get();	
		}
		else
		{
			if($type==1)
			{	
				$inventory=Inventory::where('status',1)
						   ->orderBy('id','DESC')
						   ->orderBy('id','DESC')
						   ->get();		
			}
			else
			{
				$inventory=Inventory::where('status',1)
						  ->where('created_by',$created_by)
						  ->orderBy('id','DESC')
						  ->get();
			}
		}
		return $inventory;	
	}

	public function getDelete($id = Null)
	{		
		$inventory = Inventory::find($id);		
		
		$thubm_path=env('UPLOADS')."/inventory/thumb/";
		$medium_path=env('UPLOADS')."/inventory/medium/";
		$original_path=env('UPLOADS')."/inventory/original/";
		//dd($thubm_path.$inventory->inventory_image);
		if(file_exists($thubm_path.$inventory->inventory_image))
		{
			@unlink($thubm_path.$inventory->inventory_image);
		} 
		if(file_exists($medium_path.$inventory->inventory_image))
		{
			@unlink($medium_path.$inventory->inventory_image);
		}
		if(file_exists($original_path.$inventory->inventory_image))
		{
			@unlink($original_path.$inventory->inventory_image);
		}
		$inventory->delete();
		if($inventory->delete())
		{
			return 'success';
		}	
	}

	public function postUploadlogo(Request $request)
	{	
		//dd($_FILES[ 'file' ][ 'name' ]."------".$_FILES[ 'file' ][ 'tmp_name' ]);
		$obj = new helpers();
		$folder_name=env('UPLOADS');
		$file_name=$_FILES[ 'file' ][ 'name' ];
		$temp_path = $_FILES[ 'file' ][ 'tmp_name' ];

		

		if (!file_exists($folder_name)) {			
			$create_folder= mkdir($folder_name, 0777);
			$thumb_path= mkdir($folder_name."/inventory/thumb", 0777);
			$medium_path= mkdir($folder_name."/inventory/medium", 0777);
			$original_path= mkdir($folder_name."/inventory/original", 0777);
		}
		else
		{			
			$thumb_path= env('UPLOADS')."/inventory/thumb"."/";
			$medium_path= env('UPLOADS')."/inventory/medium"."/";
			$original_path= env('UPLOADS')."/inventory/original"."/";
		}


		$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$new_file_name = time()."_".rand(111111111,999999999).'.'.$extension; // renameing image

		$file_ori = $_FILES[ 'file' ][ 'tmp_name' ];
		
		move_uploaded_file($file_ori, $original_path.$new_file_name);
		
		$obj->createThumbnail($original_path,$thumb_path,env('THUMB_SIZE'));
		$obj->createThumbnail($original_path,$medium_path,env('MEDIUM_SIZE'));		
		
		return $new_file_name;

	}

	public function postStore(Request $request)
	{
		//dd($request->all());
		$obj = new helpers();
		$created_by=Auth::user()->id;

		// Get value from env file
		$upload_dir = env('UPLOADS');
		$medium_size=env('MEDIUM_SIZE');
		$thumb_size=env('THUMB_SIZE');		

		//Creating image name dynamically
		//$extension=$request->input('image_type');
		$extension=".jpg";
		//dd($extension);
		$fileName = time()."_".rand(111111111,999999999).'.'.$extension;

		//convertImage
		//dd($fileName);
		//Image path
		$original_image_path="../uploads/inventory/original/".$fileName;
		$thumb_image_path="../uploads/inventory/thumb/".$fileName;
		$medium_image_path="../uploads/inventory/medium/".$fileName;
		
		$src=$request->input('image_data');

		dd($request->all());
		//Actually uploadingimage
		// $small=$obj->create_thumb($src, $thumb_image_path, $thumb_size);
		// $medium=$obj->create_thumb($src, $medium_image_path, $medium_size);		
		// $original=$obj->create_thumb($src, $original_image_path, 1000);		

		$small=$obj->base64_to_jpeg($src, $thumb_image_path);
		$medium=$obj->base64_to_jpeg($src, $medium_image_path);	
		$original=$obj->base64_to_jpeg($src, $original_image_path);
		//$original=$obj->base64_to_jpeg($src,$original_image_path);
		//dd($original_image_path);
		//dd("A");
		$inventory_name=$request['inventory_data']['inventory_name'];
		$sell_price=$request['inventory_data']['sell_price'];
		$cost=$request['inventory_data']['cost'];
		$status=1;
		
		if(file_exists($medium_image_path))
		{			
			$inventory = new Inventory();
			$inventory->inventory_name 	= $inventory_name;	
			$inventory->sell_price 		= $sell_price;
			$inventory->cost 			= $cost;	
			$inventory->retail_price 	= $retail_price;	
			$inventory->inventory_image = $fileName;		
			$inventory->status 			= 1;			
			$inventory->created_by 		= $created_by;
			if($inventory->save())
			{
				return 'success';
			}
			else
			{
				return 'error';
			}
		}
		else
		{
			return 'image_not';
		}
	}
	public function postAddlogo(Request $request)
	{
		//dd($request->all());
		$rules = array(
				'inventory_name'     => 'required',  
				'sell_price'         => 'required',   
				'cost'         		 => 'required',
				'inventory_image'    => 'required'

			);	
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {				
			$messages = $validator->messages();
			// redirect our user back to the form with the errors from the validator			
			return redirect()->back()
							 ->withInput()
							 ->withErrors($validator);
		}
		else
		{
			$inventory_name=$request->input('inventory_name');
			$sell_price=$request->input('sell_price');
			$cost=$request->input('cost');
			$inventory_image=$request->input('inventory_image');
			//dd("a");
			//dd($request->all());
			// Get current logged in user ID
			$created_by=Auth::user()->id;

			if($inventory_name=="" || $sell_price=="" || $cost=="")
			{
				return 'error';
			}
			else
			{
				$original_path= env('UPLOADS')."/inventory/original"."/".$inventory_image;
				if(file_exists($original_path))
				{
					$campaign = new Inventory();
					$campaign->inventory_name 		= $inventory_name;	
					$campaign->sell_price 		= $sell_price;
					$campaign->cost 		= $cost;	
					$campaign->inventory_image 		= $inventory_image;		
					$campaign->status 			= 1;			
					$campaign->created_by 		= $created_by;
					if($campaign->save())
					{
						return 'success';
					}
					else
					{
						return 'error';
					}
				}
				else
				{
					return 'image_not';
				}
			} //
		}
	}

	public function postEditinventory(Request $request)
	{
		//dd($request[0]['inventory_name']);
		//dd($request->all());
		$inventory = Inventory::find($request[0]['id']);
		//dd($request[0]['campaign_name']);

		$inventory_name=$request[0]['inventory_name'];
		$sell_price=$request[0]['sell_price'];
		$cost=$request[0]['cost'];
		//$updated_at=$request[0]['updated_at'];
		//$campaign_image=$request->input('campaign_image');

		// Get current logged in user ID
		//$created_by=Auth::user()->id;
		if($request[0]['id']=="")
		{
			return 'invalid_id';
		}
		else if($inventory_name=="" || $sell_price=="" || $cost=="")
		{
			return 'error';
		}
		else
		{			
			
			$inventory->inventory_name 	= $inventory_name;			
			$inventory->sell_price 		= $sell_price;	
			$inventory->cost 			= $cost;			
			if($inventory->save())
			{
				return 'success';
			}
			else
			{
				return 'error';
			}			
		}
		
	}

	public function postInventorydetails(Request $request)
	{		
		//dd($request[0]);
		
		$id=$request[0];
		$inventory=Inventory::find($id);
		
		return $inventory;	
	}

	public function getShowaddform()
	{
		return view('admin.reedemer.add_inventory');
	}

	public function postStoreaddform(Request $request)
	{	
		$obj = new helpers();
		$folder_name=env('UPLOADS');
		if($_FILES[ 'file' ][ 'name' ]=="")
		{
			return 'error';
		}
		$file_name=$_FILES[ 'file' ][ 'name' ];
		$temp_path = $_FILES[ 'file' ][ 'tmp_name' ];

		

		if (!file_exists($folder_name)) {			
			$create_folder= mkdir($folder_name, 0777);
			$thumb_path= mkdir($folder_name."/inventory/thumb", 0777);
			$medium_path= mkdir($folder_name."/inventory/medium", 0777);
			$original_path= mkdir($folder_name."/inventory/original", 0777);
		}
		else
		{			
			$thumb_path= env('UPLOADS')."/inventory/thumb"."/";
			$medium_path= env('UPLOADS')."/inventory/medium"."/";
			$original_path= env('UPLOADS')."/inventory/original"."/";
		}


		$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$new_file_name = time()."_".rand(111111111,999999999).'.'.$extension; // renameing image

		$file_ori = $_FILES[ 'file' ][ 'tmp_name' ];
		
		move_uploaded_file($file_ori, "$original_path$new_file_name");
		
		$obj->createThumbnail($original_path,$thumb_path,env('THUMB_SIZE'));
		$obj->createThumbnail($original_path,$medium_path,env('MEDIUM_SIZE'));
		
		$created_by=Auth::user()->id;
		// Get current logged in user TYPE
		$type=Auth::user()->type;

		$inventory_name=$request->get('inventory_name');
		$sell_price=$request->get('sell_price');
		$cost=$request->get('cost');
		$inventory_image=$new_file_name;	

		if($inventory_name=="" || $sell_price=="" || $cost=="" || $inventory_image=="")
		{
			return 'error';
		}
		
		//dd($inventory_name);

		$inven = new Inventory();
		$inven->inventory_name 		= $inventory_name;	
		$inven->sell_price 		= $sell_price;
		$inven->cost 		= $cost;	
		$inven->inventory_image 		= $inventory_image;		
		$inven->status 			= 1;			
		$inven->created_by 		= $created_by;
		$inven->save();
			
			
		$inventory=Inventory::where('status',1)
				  ->where('created_by',$created_by)	
				  ->orderBy('id','DESC')					 
				  ->get();
		$select='<option value="">-- choose an option --</option>';
		foreach($inventory as $inv)
		{			
			$select.='<option value="'.$inv['id'].'" ng-repeat="inventory in inventory_list" >'.$inv['inventory_name'].'</option>';
		}        
		return $select;
	}
}
