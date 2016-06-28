<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Logo;
use App\Model\Price;
use App\Model\Partnersetting;
use App\Model\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Hash;
use Validator; 
use Input; /* For input */
use App\Helper\helpers;
use Auth;
use App\Helper\vuforiaclient;
//use App\Helper\gettarget;
//use App\Helper\signaturebuilder;

class DashboardController extends Controller {

	//protected $dashboard;
	
	//public function __construct(  )
	//{
		//$this->dashboard = $dashboard;
	//	$this->middleware('auth');
	//	dd("Ag");
	//}	


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	 
	public function getIndex()
	{
		return view('admin.dashboard.index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//dd("a");
		//
		//dd($request->all());
		//return 'c';
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow()
	{
		//dd("dashboard->show");
		//Auth::logout();

    	//return redirect()->back();
	}

	public function postReedemarlist(Request $request)
	{
		$user=User::where('type',2)->get();
		return $user;
	}
	public function postShow(Request $request)
	{
		//dd($request->all());

		$id=Auth::user()->id;
		$created_by=Auth::user()->id;

		// Get current logged in user TYPE
		$type=Auth::user()->type;
		if($request[0]!="")
		{
			$id=$request[0];
			$user=User::where('status',1)
						  ->where('id',$id)						 
						  ->get();	
		}
		else
		{
			if($type==1)
			{	
				$user=User::where('type',2)->orderBy('id','DESC')->get();			
			}
			else
			{
				$user=User::where('id',$id)->orderBy('id','DESC')->get();	
			}
		}


		// $id=Auth::user()->id;
		// $type=Auth::user()->type;

		// dd($id);
		// if($type!=1)
		// {
		// 	$user=User::where('id',$id)->orderBy('id','DESC')->get();	
		// }
		// else
		// {
		// 	$user=User::where('type',2)->orderBy('id','DESC')->get();		
					
		// }
		 return $user;
	}

	public function getStatusupdate($id,$approve)
	{		
		if($approve==1)
		{
			$new_status=0;
		}
		else
		{
			$new_status=1;	
		}
		$user = User::find($id);
		$user->approve=$new_status;
		$user->save();
		return $new_status;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	function get_lat_lng($zip)
	{
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($zip)."&sensor=false";
		$result_string = file_get_contents($url);
		$result = json_decode($result_string, true);
		$result1[]=$result['results'][0];
		$result2[]=$result1[0]['geometry'];
		$result3[]=$result2[0]['location'];
		return $result3[0];
	}
	public function postStorereedemer(Request $request)
	{
		$zipcode=$request->input('postal_code');
		$location_arr=$this->get_lat_lng($zipcode);
		//dd($location_arr['lng']);
		//dd($zipcode)	;
		//dd($request->input('address').'--'.$request->input('web_address').'--'.$request->input('company_name').'--'.$request->input('email').'--'.$request->input('password').'--'.$request->input('category_id'));
		// if($request->input('address')=='' || $request->input('web_address')=='' || $request->input('company_name')=='' || $request->input('email')=='' ||  $request->input('password')=='' ||  $request->input('category_id')=='')
		// {
		// 	return 'error';
		// 	exit;
		// }
		
		$lat=$location_arr['lat'];
		$lng=$location_arr['lng'];
		$c_c=strtolower($request->input('company_name'));
		$user_check = User::where('company_name',$c_c)->count();
		//dd($user_check);
		if($user_check >0)
		{
			return 'already_company_exists';
			exit;
		}
		if($request->input('address')=='' || $request->input('web_address')=='' || $request->input('company_name')=='' || $request->input('email')=='' ||  $request->input('password')=='')
		{
		 	return 'error';
		 	exit;
		}

		$rules = array(
				'company_name'     => 'required',  
				'email'            => 'required|email|unique:users',   
				'password'         => 'required|min:6'

			);	
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {				
			$messages = $validator->messages();
			// redirect our user back to the form with the errors from the validator			
			return redirect()->back()
							 ->withInput($request->only('company_name'))
							 ->withErrors('Please insert all field');
			
			exit;	
		} else {
			// create the data for our user
			$user = new User();
			$user->company_name = $request->input('company_name');			
			$user->first_name 	= $request->input('first_name');	
			$user->last_name 	= $request->input('last_name');	
			$user->address 		= $request->input('address');
			$user->zipcode 		= $zipcode;
			$user->lat 			= $lat;
			$user->lng 			= $lng;
			$user->type 		= 2; // Type 2 for redeemar partner		
			$user->approve 		= 1; // Autometically approve redeemar
			$user->email 		= $request->input('email');
			$user->web_address 	= $request->input('web_address');
			$user->password 	= bcrypt($request->input('password'));
			$user->cat_id 		= '';
			$user->subcat_id 	= '';
			$user->save();
			//Get latest redeemar ID
			$user_id = $user->id;
			
			// Insert dummy data into Table: reedemer_partner_settings
			// $price = new Partnersetting();
			// $price->setting_val 	= env('DEFAULT_PRICE_SETTING_VAL');			
			// $price->price_range_id 	= env('DEFAULT_PRICE_RANGE');		
			// $price->status 			= 1;			
			// $price->created_by 		= $user_id;
			
			// $price->save();
				
			
			return 'success';		
			exit;	
		}
		
	}

	/*public function getCreatereedemer()
	{
		return view('admin.reedemer.add');
	}*/

	public function postUploadlogo(Request $request)
	{		
		dd($request->all());
		$obj = new helpers();
		$folder_name=env('UPLOADS');
		$file_name=$_FILES[ 'file' ][ 'name' ];
		$temp_path = $_FILES[ 'file' ][ 'tmp_name' ];

		

		if (!file_exists($folder_name)) {			
			$create_folder= mkdir($folder_name, 0777);
			$thumb_path= mkdir($folder_name."/thumb", 0777);
			$medium_path= mkdir($folder_name."/medium", 0777);
			$original_path= mkdir($folder_name."/original", 0777);
		}
		else
		{			
			$thumb_path= env('UPLOADS')."/thumb"."/";
			$medium_path= env('UPLOADS')."/medium"."/";
			$original_path= env('UPLOADS')."/original"."/";
		}

		//echo "PP".$file_name;
		//die();
		$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$new_file_name = time()."_".rand(111111111,999999999).'.'.$extension; // renameing image

		$file_ori = $_FILES[ 'file' ][ 'tmp_name' ];
		
		move_uploaded_file($file_ori, "$original_path$new_file_name");
		
		//$obj->createThumbnail($original_path,$thumb_path,env('THUMB_SIZE'));
		//$obj->createThumbnail($original_path,$medium_path,env('MEDIUM_SIZE'));		
		
		return $new_file_name;

	}

	public function getVuforiarate($target_id,$logo_id,$contact_email)
	{
		//dd($logo_id);
		$client = new vuforiaclient();
		//$target_id=$target_id->target_id;

		$target_res_details=$client->getTarget($target_id); 
		//$response_arr=json_decode($target_res_details);
		$response_arr=json_decode($target_res_details);
		$tracking_rating=$response_arr->target_record->tracking_rating;
		
		$logo = Logo::find($logo_id);

		$logo->tracking_rating = $tracking_rating;
		$logo->contact_email = $contact_email;

		if($logo->save())
		{
			//$logo_id = $logo->id;
			return array('response'=>'success','rating'=>$tracking_rating);
		}
	}
	
	public function postLogo()
	{
		$id=Auth::user()->id;
		$type=Auth::user()->type;
		//dd($type);
		if($type!=1)
		{
			$logo_details = Logo::where('reedemer_id',$id)							
							->orderBy('id','DESC')
							->get();	
		}
		else
		{
			$logo_details = Logo::orderBy('id','DESC')
							->get();
				
		}

		$logo_arr=array();	
		$company_name="N/A";
		$target_id=NULL;
		foreach($logo_details as $logo_details)
		{	

			if($logo_details['reedemer_id'] >0)
			{
				$company_details=User::find($logo_details['reedemer_id']);
				$company_name=$company_details['company_name'];
			}			

			$logo_arr[]=array(
						'id'=>$logo_details['id'],
						'reedemer_id'=>$logo_details['reedemer_id'],
						'tracking_rating'=>$logo_details['tracking_rating'],
						'reedemer_company'=>$company_name,
						'logo_name'=>$logo_details['logo_name'],
						'logo_text'=>$logo_details['logo_text'],
						'status'=>$logo_details['status'],
						'uploaded_by'=>Auth::user()->id,
						'created_at'=>$logo_details['created_at'],
						'updated_at'=>$logo_details['updated_at'],
					  );
		}
		$logo_json=json_encode($logo_arr);		
		return $logo_json;		
			
	}

	public function getAddlogo($logo_text='',$image_name,$enhance_logo=0)
	{	
	
		$id=Auth::user()->id;
		$type=Auth::user()->type;	

		$prev_logo =Logo::where("reedemer_id",$id)->first();	
		//dd($prev_logo->cat_id);
		if($type==2)
		{
			$user_details=User::find($id);
			//dd($user_details->company_name);
			$reedemer_id=$id;
			//dd()
			$status=0;
			$logo_text=$user_details->company_name;
		}
		else
		{
			$reedemer_id=null;
			$status=1;
			//$logo_text="";
				
		}

		$client = new vuforiaclient();
		$rand=rand(111111,999999);
		$send[0] = "Logo_".time()."_".$rand;
		$send[1] = '../uploads/original/'.$image_name;
		$send[2] = '../uploads/original/'.$image_name;
		$send[3] = 'Redeemar';
		$send[4] = 'Redeemar';		
		$response=$client->addTarget($send);
		$response_arr=json_decode($response);		

		if($response_arr->result_code=="TargetCreated")
		{
			//dd("A");
			$target_id=$response_arr->target_id;					
			$logo = new Logo();
			$logo->reedemer_id 		= $reedemer_id;	
			$logo->target_id 		= $target_id;
			$logo->logo_name 		= $image_name;	
			$logo->logo_text 		= $logo_text;
			$logo->cat_id 			= $prev_logo->cat_id;
			$logo->subcat_id 		= $prev_logo->subcat_id;
			$logo->status 			= $status;			
			$logo->enhance_logo 	= $enhance_logo;
			$logo->uploaded_by 		= $id;
			if($logo->save())
			{
				$logo_id = $logo->id;
				return array('response'=>'success','target_id'=>$target_id,'logo_id'=>$logo_id);
			}			
		}
		else
		{
			return array('response'=>'image_problem','target_id'=>'');			
		}
	}

	public function getUpdatedefault($id='')
	{		
		$uploaded_by=Auth::user()->id;

		Logo::where('uploaded_by', $uploaded_by)->update(array('default_logo' => 0));

		$logo = Logo::find($id);
		$logo->default_logo	= 1;
		if($logo->save())
		{		
			return 'success';
		}
		else
		{
			return 'success'; 	
		}
	}


	public function postAddlogo(Request $request)
	{			
		$image_type_all=explode("/",$request[0]['image_type']);
		$mime_type=$image_type_all[1];
	

		$obj = new helpers();
		$rand=rand(111111,999999);
		$image_name = "Logo_".time()."_".$rand.".".$mime_type;
		$image_name_jpg = "Logo_".time()."_".$rand.".jpg";
					
		$thumb_path= env('UPLOADS')."/thumb"."/".$image_name;
		$medium_path= env('UPLOADS')."/medium"."/".$image_name_jpg;
		$original_path= env('UPLOADS')."/original"."/".$image_name;

		$medium_size=env('MEDIUM_SIZE');
		$thumb_size=env('THUMB_SIZE');

		$src=$request[0]['image_data'];

		//Actually uploadingimage
		$original=$obj->base64_to_jpeg($src, $original_path);
		$src=$original_path;
		$small=$obj->create_thumb($src, $thumb_path, $thumb_size);		
		if($mime_type=="png")
		{
			$medium=$obj->convertImage($src, $medium_path,$mime_type);
			$medium_path_new=$medium_path;
			$medium=$obj->create_thumb($medium_path_new, $medium_path, $medium_size);
		}
		else
		{
			$medium=$obj->create_thumb($src, $medium_path, $medium_size);
		}
		
		$logo_text=$request[0]['logo_text'];
		$enhance_logo=0;
		$image_name=$image_name;
		$cat_id=$request[0]['category_id'];
		$subcat_id=$request[0]['subcat_id'];
		$contact_email=$request[0]['contact_email'];
		
		$id=Auth::user()->id;
		$type=Auth::user()->type;		
		if($type==2)
		{
			$user_details=User::find($id);			
			$reedemer_id=null;
			
			$status=0;
			$logo_text=$user_details->company_name;
		}
		else
		{
			$reedemer_id=null;
			$status=1;
		}

		$client = new vuforiaclient();
		$rand=rand(111111,999999);
		$send[0] = "Logo_".time()."_".$rand;
		$send[1] = '../uploads/medium/'.$image_name_jpg;
		$send[2] = '../uploads/medium/'.$image_name_jpg;
		$send[3] = 'Redeemar';
		$send[4] = 'Redeemar';		
		$response=$client->addTarget($send);
		$response_arr=json_decode($response);		

		if($response_arr->result_code=="TargetCreated")
		{
			//Create Thumb			
			if($request[0]['postal_code'])
			{
				$zipcode=$request[0]['postal_code']	;
				$location_arr=$this->get_lat_lng($zipcode);		
				$lat=$location_arr['lat'];
				$lng=$location_arr['lng'];	
			}
			else
			{
				$zipcode='';				
				$lat='';
				$lng='';
			}
			//dd("A");
			$target_id=$response_arr->target_id;					
			$logo = new Logo();
			$logo->reedemer_id 		= $reedemer_id;	
			$logo->target_id 		= $target_id;
			$logo->logo_name 		= $image_name;	
			$logo->logo_text 		= $logo_text;
			$logo->contact_email	= $contact_email;
			$logo->cat_id 			= $cat_id;
			$logo->subcat_id 		= $subcat_id;
			$logo->status 			= $status;			
			$logo->enhance_logo 	= $enhance_logo;
			$logo->uploaded_by 		= $id;
			$logo->company_name 	= $request[0]['company_name'];
			$logo->first_name 		= $request[0]['first_name'];
			$logo->last_name 		= $request[0]['last_name'];
			$logo->address 			= $request[0]['address'];
			$logo->zipcode 			= $zipcode;
			$logo->lat 				= $lat;
			$logo->lng 				= $lng;
			$logo->contact_email 	= $request[0]['contact_email'];
			//$logo->mobile 			= $request[0]['mobile'];
			$logo->web_address 		= $request[0]['web_address'];			
			if($logo->save())
			{
				$logo_id = $logo->id;
				return array('response'=>'success','target_id'=>$target_id,'logo_id'=>$logo_id);
			}			
		}
		else
		{
			return array('response'=>'image_problem','target_id'=>'');			
		}
	}

	public function getAlllogo()
	{
		$id=Auth::user()->id;
		$type=Auth::user()->type;		
		if($type==2)
		{
			$logo_details = Logo::orderBy('id','DESC')
						->where('status',1)
						->whereNull('reedemer_id')
						->get();	
		}
		else
		{
			$logo_details = Logo::orderBy('id','DESC')						
						->get();
				
		}		

		$logo_arr=array();	
		$company_name="N/A";
		$target_id=NULL;
		foreach($logo_details as $logo_details)
		{	

			if($logo_details['reedemer_id'] >0)
			{
				$company_details=User::find($logo_details['reedemer_id']);
				$company_name=$company_details['company_name'];
			}			

			$logo_arr[]=array(
						'id'=>$logo_details['id'],
						'reedemer_id'=>$logo_details['reedemer_id'],
						'tracking_rating'=>$logo_details['tracking_rating'],
						'reedemer_company'=>$company_name,
						'logo_name'=>$logo_details['logo_name'],
						'logo_text'=>$logo_details['logo_text'],
						'status'=>$logo_details['status'],
						'uploaded_by'=>Auth::user()->id,
						'created_at'=>$logo_details['created_at'],
						'updated_at'=>$logo_details['updated_at'],
					  );
		}
		$logo_json=json_encode($logo_arr);		
		return $logo_json;		
			
	}

	public function getLogodetails($logo_id)
	{
		
		$logo_details = Logo::where('id',$logo_id)->get();
		//dd($logo_details);
		$logo_arr=array();	
		$company_name="N/A";
		$target_id=NULL;
		foreach($logo_details as $logo_details)
		{	

			if($logo_details['reedemer_id'] >0)
			{
				$company_details=User::find($logo_details['reedemer_id']);
				$company_name=$company_details['company_name'];
			}			

			$logo_arr[]=array(
						'id'=>$logo_details['id'],
						'reedemer_id'=>$logo_details['reedemer_id'],
						'tracking_rating'=>$logo_details['tracking_rating'],
						'target_id'=>$logo_details['target_id'],
						'reedemer_company'=>$company_name,
						'logo_name'=>$logo_details['logo_name'],
						'logo_text'=>$logo_details['logo_text'],
						'status'=>$logo_details['status'],
						'uploaded_by'=>Auth::user()->id,
						'created_at'=>$logo_details['created_at'],
						'updated_at'=>$logo_details['updated_at'],
					  );
		}
		$logo_json=json_encode($logo_arr);		
		return $logo_json;		
			
	}

	public function getRate()
	{
		
		 $rand=rand(1,5)	;
		return $rand;		
			
	}

	public function getDeletereedemer($id)
	{
		$user = User::find($id); 		
		if($user->delete())
		{
			return 'success';
		}
	}

	public function getDeletelogo($id)
	{		
		$logo = Logo::find($id); 		
		
		$logo_original_path="../uploads/thumb/".$logo->logo_name;
		$logo_medium_path="../uploads/thumb/".$logo->logo_name;
		$logo_thumb_path="../uploads/thumb/".$logo->logo_name;
		if(file_exists($logo_original_path))
		{
			@unlink($logo_original_path);
		}
		if(file_exists($logo_medium_path))
		{
			@unlink($logo_medium_path);
		}
		if(file_exists($logo_thumb_path))
		{
			@unlink($logo_thumb_path);
		}
		
		$client = new vuforiaclient();
			
		$response=$client->deleteTargets($logo->target_id);  
		 
		$response_arr=json_decode($response);

		$logo->delete();
		
		if($response_arr->result_code=="UnknownTarget")
		{
			return "UnknownTarget";
		}
		else
		{
			return 'success';
		}		
	}

	public function postUserdetails()
	{		
		$user_id=Auth::user()->id;
		$type=Auth::user()->type;
		$user_details=User::findOrFail($user_id);
		if($type==2)
		{

			$logoDetails=Logo::where('reedemer_id',$user_details->id)
						->get();

          if(count($logoDetails)>0)
			{

				$logo_Details=Logo::where('reedemer_id',$user_details->id)
							  ->where('default_logo',1)
							  ->get();

				if(count($logo_Details)>0)
				{
                  $logo_details=Logo::where('reedemer_id',$user_details->id)
                  				->where('default_logo',1)
                  				->first()
                  				->logo_name;

				}
				else
				{
					$logo_details=Logo::where('reedemer_id',$user_details->id)
									->first()
									->logo_name;

				}

           
			}else
			
			{  

				$logo_details="no_logo.gif";
			
			}			
		}
		//dd($logo_details);
		$user_arr=array();
		$user_arr['company_name']=$user_details->company_name;
		$user_arr['email']=$user_details->email;
		$user_arr['type']=$user_details->type;
		if($type==2)
		{
			$user_arr['logo_image']=$logo_details;
		}
		
		return $user_arr;
	}
	
	public function postUpdatestatus(Request $request)
	{

		$id=$request->get('user_logo_id');
		$target_id=$request->get('user_logo_target_id');
		$reedemer_id=Auth::user()->id;
		$default_logo=0;
		//dd($target_id);
		//dd($reedemer_id);
		$check_default=$this->check_default($reedemer_id);
		if($check_default==0)
		{
			$default_logo=1;
		}
		$logo = Logo::find($id);
		$logo->reedemer_id=$reedemer_id;
		$logo->uploaded_by=$reedemer_id;
		$logo->default_logo=$default_logo;
		//dd($check_default);
		//die();
		if($logo->save())
		{
			return 'success';
		}
	}

	public function postEditredeemar(Request $request)
	{
		$user = User::find($request[0]['id']);		

		$company_name=$request[0]['company_name'];
		$web_address=$request[0]['web_address'];		
		$address=$request[0]['address'];
		$first_name=$request[0]['first_name'];
		$last_name=$request[0]['last_name'];
		$zipcode=$request[0]['zipcode'];
		$id=$request[0]['id'];

		$location_arr=$this->get_lat_lng($zipcode);		
		$lat=$location_arr['lat'];
		$lng=$location_arr['lng'];		
		
		if($request[0]['id']=="")
		{
			return 'invalid_id';
		}
		else if($company_name=="" || $web_address=="" || $address=="" || $first_name=="" || $last_name=="" || $zipcode=="")
		{
			return 'error';
		}
		else
		{			
			
			$user->company_name 	= $company_name;	
			$user->first_name 		= $first_name;	
			$user->last_name 		= $last_name;			
			$user->web_address 		= $web_address;							
			$user->address 			= $address;	
			$user->zipcode 			= $zipcode;	
			$user->lat 				= $lat;	
			$user->lng 				= $lng;	
			if($user->save())
			{
				return 'success';
			}
			else
			{
				return 'error';
			}			
		}
		
		
	}

	public function postCategory(Request $request)
	{
		//dd($request->all());
		if($request[0]['sub_cat'])
		{
			$category = Category::where('parent_id',$request[0]['parent_id'])
						->where('visibility',1)
						->orderBy('id','DESC')
						->get();
		}
		else
		{
			$id=null;
			if($request[0])
			{
				$id=$request[0];
				$category = Category::where('id',$id)
							->where('visibility',1)
							->orderBy('id','DESC')
							->get();
			}
			else
			{
				$category = Category::where('parent_id',0)
							->where('visibility',1)
							->orderBy('id','DESC')
							->get();
			}
		}
		//dd($category->toArray());
		return $category;
	}

	//Listing to show only 
	public function getCategory($parent_id='')
	{
		//dd($parent_id);
		//$id=null;
		if($parent_id<=0)
		{
			
			//$id=$request[0];
			$category = Category::where('parent_id',$parent_id)
						->where('visibility',1)
						->get();

			//dd($parent_id);
		}
		else
		{
			$category = Category::where('visibility',1)
						->orderBy('id','DESC')
						->get();
		}
		//dd($category->toArray());
		return $category;
	}

	public function getAllcategory($parent_id='')
	{
		//dd($parent_id);
		//$id=null;
		//if($parent_id<=0)
		//{
			
			//$id=$request[0];
			$category = Category::where('visibility',1)
						->get();

			//dd($parent_id);
		//}
		//else
		//{
		//	$category = Category::where('visibility',1)
		//				->orderBy('id','DESC')
		//				->get();
		//}
		//dd($category->toArray());
		return $category;
	}

	public function getOwncategory($parent_id='')
	{
		$category = Category::where('parent_id',$parent_id)
					->where('visibility',1)
					->get();

		return $category;
	}

	public function getCategoryupdate($id,$approve)
	{	
	
		if($approve==1)
		{
			$new_status=0;
		}
		else
		{
			$new_status=1;	
		}
		$user = Category::find($id);
		$user->status=$new_status;
		$user->save();
		return $new_status;
	}

	public function postStorecategory(Request $request)
	{	
		//dd($request->all());
		$cat_name=$request->get('cat_name');
		if($request->get('parent_id'))
		{
			$parent_id=$request->get('parent_id');
		}
		else
		{
			$parent_id=0;
		}

		$category = new Category();
		$category->cat_name 		= $cat_name;	
		$category->parent_id 		= $parent_id;
		$category->status 		= 1;		
		if($category->save())
		{
			return 'success';
		}
		else
		{
			return 'error';
		}
		//dd(4)
		//dd($request->get('cat_name'));
	}

	public function postEditcategory(Request $request)
	{
		//dd($request->all());
		$category = Category::find($request[0]['id']);		

		$cat_name=$request[0]['cat_name'];
		//$web_address=$request[0]['web_address'];		
		//$address=$request[0]['address'];
		$id=$request[0]['id'];
		
		//dd($id."--".$cat_name);
		if($request[0]['id']=="")
		{
			return 'invalid_id';
		}
		else if($cat_name=="")
		{
			return 'error';
		}
		else
		{			
			
			$category->cat_name 	= $cat_name;		
			if($category->save())
			{
				return 'success';
			}
			else
			{
				return 'error';
			}			
		}
	}

	public function getDeletecategory($id)
	{
		$category = Category::find($id); 
		
		$chk_subcat=Category::where('parent_id',$category->id)
					->where('visibility','1')
					->count();
		//dd($chk_subcat);
		if($chk_subcat >0)
		{
			return 'subcat_exists';
		}
		else
		{
			$category->visibility = 0;
			if($category->save())
			{
				return 'success';
			}
			else
			{
				return 'error';
			}
		}
		//if($category->delete())
		//{
		//	return 'success';
		//}
	}

	
	//Listing to show only 
	public function getSubcategory($parent_id='')
	{	
		//dd($parent_id)	;
		if($parent_id!='')
		{
			//$id=$request[0];
			$category = Category::where('parent_id',$parent_id)
						->where('visibility',1)
						->get();
		}
		else
		{
			$category = Category::where('visibility',1)
						->orderBy('id','DESC')
						->get();
		}
		//dd($category->toArray());
		return $category;
	}


	function create_thumb($src, $dest, $desired_width)
	{
		/* read the source image */
		$source_image = imagecreatefromjpeg($src);
		$width = imagesx($source_image);
		$height = imagesy($source_image);
		
		/* find the "desired height" of this thumbnail, relative to the desired width  */
		$desired_height = floor($height * ($desired_width / $width));
		
		/* create a new, "virtual" image */
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
		
		/* copy source image at a resized size */
		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		
		/* create the physical thumbnail image to its destination */
		imagejpeg($virtual_image, $dest);
	}


	public function postLogobyuser()
	{
		$reedemer_id=Auth::user()->id;
		//dd($reedemer_id);
		$logo=Logo::where('reedemer_id',$reedemer_id)
			  ->with('action')
			  ->orderBy('id','DESC')
			  ->get();
		//return 'vvv';
		//dd($logo->toArray());
		return $logo;
	}

	function check_default($reedemer_id)
	{
		$logo=Logo::where('reedemer_id',$reedemer_id)->get();
		$prev_logo_count=$logo->count();

		return $prev_logo_count;
	}

	public function postChecklogin()
	{
		//dd("A");
		//$user_id=Auth::User()->id;
		if (!Auth::check())
		{
			return 'logout';
		}
		else
		{
			return 'login';
		}
	}
	
}
