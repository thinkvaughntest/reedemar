<?php namespace App\Http\Controllers;
use Auth;
//use App\Model\Logo;
use App\Model\Wptoken;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Http\Response; 
use Redirect;
use Input;
use Session;
use App\Helper\vuforiaclient;
use App\Model\User;
use App\Model\Inventory;
use App\Model\Campaign;
use App\Model\Logo;
use App\Model\Category;
use Illuminate\Routing\Route;
use App\Helper\helpers;
use App\Model\PasswordReset;

class PartnerController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	// public function __construct()
	// {
	// 	$this->middleware('guest');
	// }

	public function __construct()
	{
		//$this->middleware('auth');
		//$this->menuItems				= $menu->where('active' , '1')->orderBy('weight' , 'asc')->get();
 				
		
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{

		$logo_details=Logo::where('status',1)
						  ->orderBy('id','DESC')
						  ->get();	
		$category_details=$this->getCategory('0');
		$url=url();

		$logo_details_unused=Logo::where('status',1)
				  ->whereNull('reedemer_id')
				  ->orderBy('id','DESC')
				  ->get();
				 // dd($category_details->toArray());
		return view('partner.list',[
					'logo_details' =>$logo_details,
					'logo_details_unused' =>$logo_details_unused,
					'category_details' =>$category_details,
					'url' =>$url
			   ]);
		
	}

	

	public function postSearch(Request $request)
	{
		//dd($request->all());
		//exit;
		$logo_details=Logo::where('status',1)
					  ->orderBy('id','DESC')
					  ->get();					 
		$url=url();
		
		return view('partner.list',[
						'logo_details' =>$logo_details,
						'url' =>$url
				   ]);
	}

	public function getAdd($logo_id)
	{	
		$logo_details=Logo::where('id',$logo_id)->first();		
		$category_details=$this->getCategory('0');
		//dd($category_details->toArray());
		return view('partner.add',[
						'logo_id' =>$logo_id,
						'logo_details' =>$logo_details,
						'category_details' =>$category_details
				   ]);
	}

	//Listing to show only 
	public function getCategory($parent_id='')
	{		
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

	public function postStore(Request $request)
	{	
	//dd($request->all())	;
		$wptoken=$this->getWptoken();
		//dd($wptoken->toArray());
		$logo_id=$request->get('logo_id');
		//dd($logo_id);

		$zipcode=$request->get('zipcode');
		$location_arr=$this->get_lat_lng($zipcode);			
		$lat=$location_arr['lat'];
		$lng=$location_arr['lng'];		
		//dd($lng);
		// Data Array
		$data = array(
			//'logo_id' => urlencode($request->get('logo_id')),
			'company_name' => urlencode($request->get('company_name')),
			'first_name' => urlencode($request->get('first_name')),
			'last_name' => urlencode($request->get('last_name')),
			'address' => urlencode($request->get('address')),
			'zipcode' => urlencode($request->get('zipcode')),
			'lat' => urlencode($lat),
			'lng' => urlencode($lng),
			'email' => urlencode($request->get('user_email')),
			'web_address' => urlencode($request->get('web_address')),
			'password' => urlencode($request->get('user_password')),
			'confirm_user_password' => urlencode($request->get('confirm_user_password')),
			'category_id' => urlencode($request->get('category_id')),
			'subcat_id' => urlencode($request->get('subcat_id')),
			'owner' => urlencode($request->get('owner')),
			'create_offer_permission' => urlencode($request->get('create_offer_permission')),
			'token_value' => $wptoken->token_value
		);

		
		$url = getenv('WEBSERVICE_PATH');
		$result= $this->post_to_url($url, $data);
		//dd($result);
		$result_arr=json_decode($result);
		
		if($result_arr->success=='false')
		{
			return redirect()->back()	
					->withInput($request->only('company_name','address','user_email', 'web_address'))								
					->withErrors([
						'message' => $result_arr->message,
					]);
		}
		else
		{
			$reedemer_id = $result_arr->reedemer_id;

			if($logo_id==0)
			{				
     			return view('partner.addlogo',[
					 'reedemer_id' =>$reedemer_id,
					 'logo_text' =>$request->get('company_name')
			    ]);					
			}
			else
			{			

				$logo=Logo::find($logo_id);
				$logo->reedemer_id 	= $reedemer_id;	
				$logo->save();
				
				Session::flash('message', $result_arr->message);

				return Redirect::back();
			}
		}

		


	}

	public function post_to_url($url, $data) {
	    $fields = '';
	    foreach ($data as $key => $value) {
	        $fields .= $key . '=' . $value . '&';
	    }
	    rtrim($fields, '&');

	    $post = curl_init();

	    curl_setopt($post, CURLOPT_URL, $url);
	    curl_setopt($post, CURLOPT_POST, count($data));
	    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
	    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

	    $result = curl_exec($post);

	    curl_close($post);
	    return $result;
	}

	public function getWptoken() {
		$wptoken=Wptoken::first();
		//dd($wptoken->toArray());
		return $wptoken;
	}

	//This function is use for frontend upload
	public function postUploadlogo(Request $request)
	{
		//dd($request->all());
		//$image_type_all=explode("/",$request[0]['image_type']);
		$mime_type=$request->input('image_type');
	

		$obj = new helpers();
		$rand=rand(111111,999999);
		$image_name = "Logo_".time()."_".$rand.".".$mime_type;
		$image_name_jpg = "Logo_".time()."_".$rand.".jpg";
					
		$thumb_path= env('UPLOADS')."/thumb"."/".$image_name;
		$medium_path= env('UPLOADS')."/medium"."/".$image_name_jpg;
		$original_path= env('UPLOADS')."/original"."/".$image_name;

		$medium_size=env('MEDIUM_SIZE');
		$thumb_size=env('THUMB_SIZE');

		$src=$request->input('logo_image');
		//dd($src);
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


		$client = new vuforiaclient();
		//$rand=rand(111111,999999);
		$send[0] = $image_name;
		$send[1] = '../uploads/medium/'.$image_name_jpg;
		$send[2] = '../uploads/medium/'.$image_name_jpg;
		$send[3] = 'Redeemar';
		$send[4] = 'Redeemar';		
		$response=$client->addTarget($send);
		$response_arr=json_decode($response);		

		if($response_arr->result_code=="TargetCreated")
		{
			$target_id=$response_arr->target_id;
			//dd("a");
			$logo_id=2;
			//Get image rating from vuphoria
			$tracking_rating=$this->getVuforiarate($target_id,$logo_id);
			//echo $tracking_rating;
			//return $tracking_rating;
			$logo = new Logo();
			//$logo->reedemer_id=$reedemer_id;
			$logo->logo_name=$image_name_jpg;
			$logo->logo_text="Default Company";
			$logo->status=1;
			$logo->default_logo=1;
			$logo->target_id=$target_id;
			//$logo->uploaded_by=$reedemer_id;
			$logo->tracking_rating=$tracking_rating;
			if($logo->save())
			{
				$message=array('success'=>'true','logo_image'=>$image_name_jpg,'tracking_rating'=>$tracking_rating,'logo_id'=>$logo->id);
				return $message;
			}
			else
			{
				$message=array('success'=>'false');
				return $message;
			}	
		}




	}

	//backend logo upload
	//this function use for reedemar logo upload
	public function postUploadlogoback(Request $request)
	{
		//dd($request->all());
		$image_type=$request->input('image_type');

	

		$obj = new helpers();
		$rand=rand(111111,999999);
		$image_name = "Logo_".time()."_".$rand.".".$image_type;
		$image_name_jpg = "Logo_".time()."_".$rand.".jpg";
			
		//dd($image_name);		
		$thumb_path= env('UPLOADS')."/thumb"."/".$image_name;
		$medium_path= env('UPLOADS')."/medium"."/".$image_name_jpg;
		$original_path= env('UPLOADS')."/original"."/".$image_name;

		$medium_size=env('MEDIUM_SIZE');
		$thumb_size=env('THUMB_SIZE');

		$src=$request->input('logo_image');;

		//Actually uploadingimage
		$original=$obj->base64_to_jpeg($src, $original_path);
		$src=$original_path;
		$small=$obj->create_thumb($src, $thumb_path, $thumb_size);		
		if($image_type=="png")
		{
			$medium=$obj->convertImage($src, $medium_path,$image_type);
			$medium_path_new=$medium_path;
			$medium=$obj->create_thumb($medium_path_new, $medium_path, $medium_size);
		}
		else if($image_type=="gif")
		{
			$medium=$obj->convertImage($src, $medium_path,$image_type);
			$medium_path_new=$medium_path;
			$medium=$obj->create_thumb($medium_path_new, $medium_path, $medium_size);
		}
		else
		{
			$medium=$obj->create_thumb($src, $medium_path, $medium_size);
		}	


		$client = new vuforiaclient();
		//$rand=rand(111111,999999);
		$send[0] = $image_name;
		$send[1] = '../uploads/medium/'.$image_name_jpg;
		$send[2] = '../uploads/medium/'.$image_name_jpg;
		$send[3] = 'Redeemar';
		$send[4] = 'Redeemar';		
		$response=$client->addTarget($send);
		$response_arr=json_decode($response);		

		if($response_arr->result_code=="TargetCreated")
		{
			//Create Thumb			
			
			$reedemer_id=Auth::user()->id;
			$logo=Logo::where('reedemer_id',$reedemer_id)->first()->get();
			//dd($logo->toArray());

			$contact_email=$logo[0]['contact_email'];
			$logo_text=$logo[0]['logo_text'];
			$enhance_logo=1;
			$cat_id=$logo[0]['cat_id'];
			$subcat_id=$logo[0]['subcat_id'];
			$status=1;
			$company_name=$logo[0]['company_name'];
			$first_name=$logo[0]['first_name'];
			$last_name=$logo[0]['last_name'];
			$address=$logo[0]['address'];
			$city=$logo[0]['city'];
			$state=$logo[0]['state'];
			$zipcode=$logo[0]['zipcode'];
			$lat=$logo[0]['lat'];
			$lng=$logo[0]['lng'];
			$action_id=1;
			$mobile=$logo[0]['mobile'];
			$web_address=$logo[0]['web_address'];
			$uploaded_by=$reedemer_id;
			$target_id=$response_arr->target_id;	
			//dd($response_arr);


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
			$logo->action_id 		= 1;
			$logo->uploaded_by 		= $reedemer_id;
			$logo->company_name 	= $company_name;
			$logo->first_name 		= $first_name;
			$logo->last_name 		= $last_name;
			$logo->address 			= $address;
			$logo->zipcode 			= $zipcode;
			$logo->lat 				= $lat;
			$logo->lng 				= $lng;
			
			$logo->mobile 			= $mobile;
			$logo->web_address 		= $web_address;			
			if($logo->save())
			{
				$logo_id = $logo->id;
				return array('response'=>'success','target_id'=>$target_id,'logo_id'=>$logo_id);
			}			
		}

	}

	public function postAddreedemar(Request $request)
	{		
		$chk_user=User::where('image_id',$request->get('logo_id'))->get();
		$zipcode=$request->get('zipcode');
		$location_arr=$this->get_lat_lng($zipcode);
		$lat=$location_arr['lat'];
		$lng=$location_arr['lng'];

		//If image_id exists into database then update DB 
		// Otherwise create
		if($chk_user->count() > 0)
		{
			$user = User::find($chk_user[0]->id);
		}
		else
		{
			$user = new User();
		}
		$user->company_name = $request->get('company_name');			
		$user->first_name = $request->get('first_name');
		$user->last_name = $request->get('last_name');
		$user->address = $request->get('address');
		$user->city = $request->get('city');
		$user->state = $request->get('state');
		$user->zipcode = $zipcode;
		$user->lat = $lat;
		$user->lng = $lng;
		$user->email 		= $request->get('user_email');
		$user->mobile = $request->get('mobile');
		$user->web_address = $request->get('web_address');		
		$user->password = bcrypt($request->get('user_password'));
		$user->cat_id = $request->get('category_id');
		$user->subcat_id = $request->get('subcat_id');
		$user->subcat_id = 0;
		$user->owner = $request->get('owner');
		//$user->create_offer_permission = $request->get('create_offer_permission');
		$user->offer_permission 		= $request->get('offer_permission');
		$user->status 		= 1;
		$user->approve 		= 1;
		$user->type 		= 2;			
		$user->source 		= 1;  //1:Web, 2:Android, 3:IOS		
		if($user->save())
		{
			// Create user with same email if user checked
			if($request->get('create_user')==1)
			{
				$reedemar_user=new User();
				$reedemar_user->first_name = $request->get('first_name');
				$reedemar_user->last_name  = $request->get('last_name');
				$reedemar_user->email 	   = $request->get('user_email');
				$user->mobile 			   = $request->get('mobile');
				$reedemar_user->password   = bcrypt($request->get('user_password'));
				$reedemar_user->status 	   = 1;
				$reedemar_user->approve    = 1;
				$reedemar_user->type 	   = 3;
				$reedemar_user->save();
			}

			$reedemer_id=$user->id;
			$company_name=$request->get('company_name');

			// Insert in logo table
			$logo = Logo::find($request->get('logo_id'));
			$logo->reedemer_id=$reedemer_id;			
			$logo->logo_text=$company_name;			
			$logo->uploaded_by=$reedemer_id;
			$logo->cat_id=$request->get('category_id');
			$logo->subcat_id=$request->get('subcat_id');
			$logo->enhance_logo=$request->get('enhance_logo');
			if($logo->save())
			{
				return 'success';				
			}
		}


	}


	public function postAddlogo(Request $request)
	{
		//Upload logo to server		
		if($_FILES['logo_image']['name']!="")
		{
			if($_FILES['logo_image']['type']=="image/jpeg" || $_FILES['logo_image']['type']=="image/jpg")
			{
				if((($_FILES['logo_image']['size']/1024)/1024) <=2)
				{
					$destinationPath ='../uploads/original/'; // upload path			
					$extension = Input::file('logo_image')->getClientOriginalExtension(); // getting image extension
					$fileName = time()."_".rand(111111111,999999999).'.'.$extension;
					Input::file('logo_image')->move($destinationPath, $fileName); // uploading file to given path
				}
				else
				{
					return redirect()->back()	
					->withInput()								
					->withErrors([
						'message' => 'Unable to upload your logo. Please try again',
					]);
				}
			}
			else
			{
					return redirect()->back()	
					->withInput()								
					->withErrors([
						'message' => 'Upload only jpg file within 2MB size',
					]);
			}
		}
		else
		{
			return redirect()->back()	
			->withInput()								
			->withErrors([
				'message' => 'Upload only jpg file within 2MB size',
			]);
		}


		$client = new vuforiaclient();
		
		$send[0] = $fileName;
		$send[1] = '../uploads/original/'.$fileName;
		$send[2] = '../uploads/original/'.$fileName;
		$send[3] = 'Redeemar';
		$send[4] = 'Redeemar';		
		$response=$client->addTarget($send);
		$response_arr=json_decode($response);		
		
		$reedemer_id = $request->get('reedemer_id');
		$target_id = $response_arr->target_id;
		$logo_text = $request->get('logo_text');
		
		 $logo = new Logo();
		 $logo->reedemer_id=$reedemer_id;
		 $logo->logo_name=$fileName;
		 $logo->logo_text=$logo_text;
		 $logo->status=0;
		 $logo->default_logo=1;
		 $logo->target_id=$target_id;
		 $logo->uploaded_by=$reedemer_id;
		 $logo->tracking_rating=-1;
		 
		 if($logo->save())
		 {
		 	//Add demo inventory item autometically
		 	$inventory_image=$this->add_default_inventory($reedemer_id);

		 	//Add demo campaign item autometically
		 	$campaign=$this->add_default_campaign($reedemer_id);
		 	
		 	Session::flash('message', "Your account created successfully. We will notify you via email after it activated.");

			//return Redirect::to('partner/msg')->with('reedemer_id',$reedemer_id);
			//Redirect::route('partner/msg', [$reedemer_id]);
			//dd($reedemer_id);
			//return Redirect::route('partner.msg')->with('reedemer_id', $reedemer_id);
			//return redirect()->intended( '/partner/msg' )->with('reedemer_id', $reedemer_id);
			//return Redirect::url('partner/msg')->with('reedemer_id', $reedemer_id);

			//return Redirect::to('partner/msg')->with('reedemer_id','555')	;
			return redirect('partner/msg/');
		 }
		 else
		 {

			return redirect()->back()	
					->withInput()								
					->withErrors([
						'message' => 'Unable to upload your logo. Please try again',
					]);
		 }
	}


	public function getVuforiarate($target_id,$logo_id)
	{
		//dd($logo_id);
		$client = new vuforiaclient();
		//$target_id=$target_id->target_id;

		$target_res_details=$client->getTarget($target_id); 
		//$response_arr=json_decode($target_res_details);
		$response_arr=json_decode($target_res_details);
		$tracking_rating=$response_arr->target_record->tracking_rating;
		
		return $tracking_rating;		
	}

	public function getMsg()
	{
//		$user=User::find($id);

		//Use Crypt::decrypt();
//		$value = Crypt::decrypt($user->password);
//
//		dd($value);
		//$this->getVuforiarate();
		return view('partner.msg');
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
	
	function add_default_campaign($reedemer_id)
	{		
		$start_date=date('Y-m-d');
		$end_date=date('Y-m-d', strtotime("+30 days"));

		$campaign = new Campaign();
		$campaign->campaign_name="Default Campaign";
		$campaign->start_date=$start_date;
		$campaign->end_date=$end_date;
		$campaign->status=1;
		$campaign->created_by=$reedemer_id;
		if($campaign->save())
		{
			return $campaign->id;
		}
		else
		{
			return 'error';
		}

	}

	function add_default_inventory($reedemer_id)
	{
		$base_image_path='../uploads/default_pizza.jpg'; // upload path			
		$destinationPath ='../uploads/inventory/original/'; // upload path			
		$destinationPathThumb ='../uploads/inventory/thumb/'; // upload path			
		$destinationPathMedium ='../uploads/inventory/medium/'; // upload path	
		
		$extension="jpg";
		$fileName = "demo_".time()."_".rand(111111111,999999999).'.'.$extension;
		$original_image=$destinationPath.$fileName;
		$thumb_image=$destinationPathThumb.$fileName;
		$medium_image=$destinationPathMedium.$fileName;


		copy($base_image_path, $original_image);
		copy($base_image_path, $thumb_image);
		copy($base_image_path, $medium_image);		
		
		$inventory = new Inventory();
		$inventory->inventory_name="Default Product";
		$inventory->inventory_image=$fileName;
		$inventory->sell_price="100";
		$inventory->cost="40";
		$inventory->status=1;
		$inventory->created_by=$reedemer_id;
		if($inventory->save())
		{
			return $inventory->id;
		}
		else
		{
			return 'error';
		}

	}

	public function getForgotpassword()
	{
		return view('auth.forgetpassword');
	}
	
	public function getResetpassword($token)
	{
		if($token){
			$userData = PasswordReset::where('token', $token)->get();
			// dd($userData->toArray());
			if($userData){
				return view('auth.changepassword',$userData[0]);
			} else {
				return view('/auth/login');
			}
		} else {
			return view('/auth/login');
		}
		//return view('auth.changepassword');
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

	public function getLogodetails($logo_id)
	{
		//dd($logo_id);
		//$user=User::where('image_id',$logo_id)->first();
		$user=Logo::find($logo_id);
		//dd($logo);
		return $user;
	}

}
