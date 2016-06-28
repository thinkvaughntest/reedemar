<?php namespace App\Http\Controllers;
use Auth;
use DB;
use App\Model\Wptoken;
use App\Model\Demotest;
use App\Model\Pp;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response; 
use Redirect;
use Input;
use Session;
use App\Helper\vuforiaclient;
use App\Model\User;
use App\Model\Video;
use App\Model\Logo;
use App\Model\Category;
use App\Model\UserPassedOffer;
use App\Model\UserBankOffer;
use App\Model\RedeemptionOffer;
use App\Model\Offer;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Encryption\Encrypter;

class BridgeController extends Controller {

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
	public function postIndex(Request $request)
	{		
		$data=json_decode($request->get('data'));
		$target_id=$data->target_id;
		$webservice_name=$data->webservice_name;

		// Put all response to database for testing purpose
		// Will have to remove this later
		
	 	$demotest=new Demotest();
	 	$demotest->target_id=$target_id;
	 	$demotest->save();

		if($webservice_name=='')
		{
			$response['status']='failure';
			$response['messageCode']='R0001'; //Webservice name is missing
		}
		if($target_id=='')
		{
			$response['status']='failure';
			$response['messageCode']='R0002'; //Target ID is missing
		}
		
		$base_path=getenv('WEBSERVICE');
		$webservice_name=$webservice_name;
		$target_id=$target_id;

		 $data = array(
		   	'target_id' => urlencode($target_id)
		 );		

		switch ($webservice_name) {

		case "check_target":
			$url=$base_path."checktarget";
		break;

		case "userregister":
			$url=$base_path."userregister";
		break;

		case "userlogin":
			$url=$base_path."userlogin";
		break;

		case "userdetail":
			$url=$base_path."userdetail";
		break;

		case "showoffers":
			$url=$base_path."offerlist";
		break;	
		case "offerdetail":
			$url=$base_path."offerdetail";
		break;

		case "validateofferdetail":
			$url=$base_path."validateofferdetail";
		break;


		case "alloffers":
			$url=$base_path."alloffers";
		break;

		case "mapalloffers":
			$url=$base_path."mapalloffers";
		break;	

		case "myoffer":
			$url=$base_path."myoffer";
		break;	

		case "bankoffer":
			$url=$base_path."bankoffer";
		break;

		case "passoffer":
			$url=$base_path."passoffer";
		break;	

		case "mypassedoffer":
			$url=$base_path."mypassedoffer";
		break;

		case "redeemption":
			$url=$base_path."redeemption";
		break;	

		case "socialsignup":
			$url=$base_path."socialsignup";
		break;	



		default:
			$url=$base_path."not_found";
		}

		$response= $this->post_to_url($url, $data);
		$json = json_decode($response, true);		

		return $response;		
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


	// User Registration

	public function postUserregister(Request $request)

	{

 		$data=json_decode($request->get('data'));

 		 $email = $data->email;
 		 $device_token = $data->device_token;
 		 $password = bcrypt($data->password);
 		 $status = 1;
 		 $approve = 1;
 		 $source = $data->source;
 		 $type = 3;

 		$encrypter = app('Illuminate\Encryption\Encrypter');
		$encrypted_token = $encrypter->encrypt(csrf_token());

		$userdata=User::where('email',$email)->first();

		if(count($userdata)>0)
		{
 
			$datalist['messageCode']="R01002";

			$datalist['data']="User with this email id already exist.";

		}
		else
		{

	$userdatalist = User::create(['email' => $email, 'password' => $password, 'status' => $status , 'approve' => $approve ,  'device_token' => $device_token , 'source' => $source , 'type' => 3]);

		$datalist['messageCode']="R01001";

		$datalist['data']=$userdatalist;

       }
			return $datalist;

	}

	//function for social signup
	public function postSocialsignup(Request $request)

	{

		$seed = str_split('abcdefghijklmnopqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'0123456789!@#'); 
		shuffle($seed); 
		$randpassword = '';
		foreach (array_rand($seed, 5) as $k) $randpassword .= $seed[$k];

 		$data=json_decode($request->get('data'));

 		 $email = $data->email;
 		 $device_token = $data->device_token;
 		 $password = bcrypt($randpassword);
 		 $status = 1;
 		 $approve = 1;
 		 $source = $data->source;
 		 $social_type = $data->social_type;
 		 $type = 3;

 		$encrypter = app('Illuminate\Encryption\Encrypter');
		$encrypted_token = $encrypter->encrypt(csrf_token());

		$userdata=User::where('email',$email)
					->where('type',3)
					->first();

		if(count($userdata)>0)
		{
 
			$datalist['messageCode']="R01002";

			$datalist['data']=$userdata;

			if($social_type=='facebook')
			{

				$facebook_id = $data->facebook_id;

				$updateSocialType = User::where('id',$userdata->id)->update(array('facebook_token'=> $facebook_id,'status' => 1 , 'approve' => 1 ,'source' => $source , 'device_token' => $device_token));
			}
			else
			{
				$google_id = $data->google_id;

				$updateSocialType = User::where('id',$userdata->id)->update(array('googleplus_token'=> $google_id ,'status' => 1 , 'approve' => 1 ,'source' => $source , 'device_token' => $device_token));

			}

			//$datalist['data']="User with this email id already exist.";

		}
		else
		{
			if($social_type=='facebook')
			{
				$facebook_id = $data->facebook_id;

				$userdatalist = User::create(['email' => $email, 'password' => $password, 'status' => $status , 'approve' => $approve ,  'device_token' => $device_token , 'source' => $source , 'type' => 3 ,'facebook_token'=> $facebook_id]);

			}
			elseif($social_type=='google')
			{
				$google_id = $data->google_id;

				$userdatalist = User::create(['email' => $email, 'password' => $password, 'status' => $status , 'approve' => $approve ,  'device_token' => $device_token , 'source' => $source , 'type' => 3 ,'googleplus_token'=> $google_id]);

			}
			else
			{
              	$userdatalist = User::create(['email' => $email, 'password' => $password, 'status' => $status , 'approve' => $approve ,  'device_token' => $device_token , 'source' => $source , 'type' => 3 , ]);

			}

			$datalist['messageCode']="R01001";

			$datalist['data']=$userdatalist;

			$this->sendRegisterEmail($email,$randpassword);

       }
	   return $datalist;

	}


	public function sendRegisterEmail($contactemail,$registerpassword)
	{


		$user_email=$contactemail;
		$admin_email="admin@mailinator.com";
		
		$data = array('user_email' => $user_email,'registerpassword' => $registerpassword);
		\Mail::send('emails.register', $data, function($message) use ($admin_email,$user_email,$registerpassword){ 
			$subject="Thank You For Joining Redeemar";
			$message->from($admin_email, $user_email);
			$message->to($user_email)->subject($subject);
		}); 
	}

	// User Login

	public function postUserlogin(Request $request)
	{
 
      $data=json_decode($request->get('data'));

      $credentials['email'] = $data->email;
        
      $credentials['password'] = $data->password;


      if (Auth::attempt($credentials)) {

            $user = Auth::user();
            

            if($user->status==0)
            {

             $userdetail['messageCode']="R01003";

		     $userdetail['data']='You account is deactivated.';

            }
            else if($user->approve==0)
            {
            
             $userdetail['messageCode']="R01005";

		     $userdetail['data']='You account is not approve yet.';

            }

            else
            {
            
           if($user->status==1 && $user->type==3)
            {

            //Get Count of Bank offer

            $currentdate=date('Y-m-d h:i:s');

            $userbankoffer=UserBankOffer::where('user_id',$user->id)->where('validate_within','>=', $currentdate)->get();

            $totalbankoffer=count($userbankoffer);


			$dataArr=array('user_id'=>$user->id,'email' => $user->email, 'totalbankoffer' => $totalbankoffer, 'userStatus' => 1, 'device_token'=>$user->device_token);

			$userdata=json_encode($dataArr);

			$userdetail['messageCode']="R01001";

			$userdetail['data']=$userdata;

            }

            else if($user->type==1)

            {
             
             $userdetail['messageCode']="R01002";

		     $userdetail['data']='You are not allowed to login from mobile device, login from web';

            }

          }
       }
        else
        {

        $userdetail['messageCode']="R01004";

	    $userdetail['data']='Invalid Username and Password.';

        }

		return $userdetail;
	}


	// Show user detail


	public function postUserdetail(Request $request)

	{
      
      $data=json_decode($request->get('data'));

      $device_token=$data->device_token;

      $source=$data->source;

      $userdata=User::where('source',$source)->where('device_token',$device_token)->first();

      if(count($userdata)>0)
		{

		$userdetail['messageCode']="R01001";

		$userdetail['data']=$userdata;

	    }
	    else
	    {

	    $userdetail['messageCode']="R01002";

		$userdetail['data']="No record found.";

	    }


		return $userdetail;

	}


	// Show Offer List 

	public function postAlloffers(Request $request)
	{
      
		$data=json_decode($request->get('data'));

		$now=date('Y-m-d h:i:s');

		$lat=$data->lat;

		$long=$data->long;

		$radius=$data->radius;

		$user_id=$data->user_id;

		$zipcodes= $this->getDistance($lat, $long ,$radius);

		$zipval=[];

		foreach($zipcodes as $zip)
		{

		$zipval[]=$zip->zipcode;

		 }


		 // Offer List


		if($user_id>0)
		{
		// Get passed users list offer

		$userbankoffer=UserBankOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

		$userpassedoffer=UserPassedOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

		$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->whereNotIn('id',$userbankoffer)->whereNotIn('id',$userpassedoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

		}
		else
		{

		$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();


		}



		// $offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();


		if(count($offer_list)>0)
		{

		$datalist['messageCode']="R01001";

		$datalist['data']=$offer_list;

	    }
	    else
	    {

	    $datalist['messageCode']="R01002";

		$datalist['data']="No record found.";

	    }


		return $datalist;

	}

	// Map Offer

	public function postMapalloffers(Request $request)
	{
      
		$data=json_decode($request->get('data'));

		$now=date('Y-m-d h:i:s');

		$lat=$data->lat;

		$long=$data->long;


		 // Offer List


		/*$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('max_redeemar','>',0)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();*/

		$users=User::where('type', 2)->with('profile')->get();

		//dd("Hello");



		if(count($users)>0)
		{

			$datalist['messageCode']="R01001";
			$datalist['data']=$users;

	    }
	    else
	    {

		    $datalist['messageCode']="R01002";
			$datalist['data']="No record found.";

	    }


		return $datalist;

	}


	function getDistance($lat, $lng , $radius) {


		$sql=DB::select("SELECT zipcode, ( 6371 * acos( cos( radians( {$lat} ) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( latitude)))) AS distance FROM reedemer_offer  HAVING distance <= {$radius} ORDER BY distance");

			return $sql;
	}


 function getPostcode($lat, $lng , $radius) {

		$returnValue = NULL;
		$ch = curl_init();
		$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&sensor=false";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		$json = json_decode($result, TRUE);

		if (isset($json['results'])) {
		foreach    ($json['results'] as $result) {
		foreach ($result['address_components'] as $address_component) {
		$types = $address_component['types'];
		if (in_array('postal_code', $types) && sizeof($types) == 1) {
		$returnValue = $address_component['short_name'];
		}
		}
		}
		}
		return $returnValue;
}

	// Show Offer List of User

	public function postOfferlist(Request $request)
	{
      
		$data=json_decode($request->get('data'));
        
		$user_id=$data->user_id;

		$reedemer_id=$data->reedemer_id;

		$campaign_id = $data->campaign_id;

		$now=date('Y-m-d h:i:s');

		// Calculated Postcode 

        $lat=$data->lat;

		$long=$data->long;

		$radius=$data->radius;

		$zipcodes= $this->getDistance($lat, $long ,$radius);

		$zipval=[];

		foreach($zipcodes as $zip)
		{

		$zipval[]=$zip->zipcode;

		}

		if($user_id>0)
		{
			// Get passed users list offer

			$userbankoffer=UserBankOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

			$userpassedoffer=UserPassedOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

			if($campaign_id != 0)
				$offer_list = Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('campaign_id',$campaign_id)->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->whereNotIn('id',$userbankoffer)->whereNotIn('id',$userpassedoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

			else
				$offer_list = Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('created_by',$reedemer_id)->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->whereNotIn('id',$userbankoffer)->whereNotIn('id',$userpassedoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

			$datalist['messageCode']="R01001";
		
	}
	else
	{

			if($campaign_id != 0)
				$offer_list = Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('campaign_id',$campaign_id)->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();
			else
				$offer_list = Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('created_by',$reedemer_id)->where('max_redeemar','>',0)->whereIn('zipcode',$zipval)->whereNotIn('status',array(2,4))->where('end_date','>=',$now)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

			$datalist['messageCode']="R01002";

	}

	    $datalist['data']=$offer_list;
	    

		return $datalist;

	}

    // Show User Bank Offer

	public function postMyoffer(Request $request)
	{

			// Get passed users list offer

			$data=json_decode($request->get('data'));

			$user_id=$data->user_id;

			$userbankoffer=UserBankOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');


			$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->whereNotIn('status',array(2,4))->where('max_redeemar','>',0)->whereIn('id',$userbankoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail','myofferDetails')->orderBy('created_at','desc')->get();

				if(count($offer_list)>0)
				{

					$datalist['messageCode']="R01001";
					$datalist['data']=$offer_list;

				}
				else
				{

					$datalist['messageCode']="R01002";
					$datalist['data']="No record found.";

				}

			return $datalist;

	}


	public function postBankoffer(Request $request)
	{

			// Get passed users list offer

			$data=json_decode($request->get('data'));

			$user_id=$data->user_id;

			$offer_id=$data->offer_id;

			// Check where already bank offer 

			$userbankoffer=UserBankOffer::where('user_id',$user_id)->where('offer_id',$offer_id)->get();

			if(count($userbankoffer)>0)
			{
				$datalist['data']="You already bank this offer.";
				$datalist['messageCode']="R01002";
			}else
			{

			$offer_detail=Offer::where('id',$offer_id)->first();

			$date = date("Y-m-d H:i:s");

			$date = strtotime($date);

			$currenthour=date('H', $date);

			//$validate_after=$offer_detail['validate_after'] + $currenthour;

			//$validate_within=$offer_detail['validate_within'] + $currenthour; 


			$validate_a=$offer_detail['validate_after'] + $currenthour;

			$validate_w=$offer_detail['validate_within'] + $currenthour; 

            
            $val_after=strtotime('+'.$validate_a.' hours');

            $val_within=strtotime('+'.$validate_w. ' hours');
		

			$validate_after=date("Y-m-d H:i:s",$val_after);

			$validate_within=date("Y-m-d H:i:s",$val_within);


			 $max_redeemar=$offer_detail['max_redeemar'] - 1;

            $redeem_offer=$offer_detail['redeem_offer'] + 1;


			$datalist = UserBankOffer::create(['user_id' => $user_id, 'offer_id' => $offer_id , 'validate_within' => $validate_within, 'validate_after' => $validate_after]);

			//$updateOffer = Offer::where('id',$offer_id)->update(array('max_redeemar'=> $max_redeemar));

			//$redeemOffer = Offer::where('id',$offer_id)->update(array('redeem_offer'=> $redeem_offer));


			$datalist['messageCode']="R01001";
		}



			return $datalist;

	}

	public function postPassoffer(Request $request)
	{

			// Get passed users list offer

			$data=json_decode($request->get('data'));

			$user_id=$data->user_id;

			$offer_id=$data->offer_id;

			//Check already pass offer

			$userpassoffer=UserPassedOffer::where('user_id',$user_id)->where('offer_id',$offer_id)->get();

           if(count($userpassoffer)>0)
			{
				$datalist['data']="You already passed this offer.";

				$datalist['messageCode']="R01002";
			}else
			{

			$offer_detail=Offer::where('id',$offer_id)->first();

            $max_redeemar=$offer_detail['max_redeemar'] - 1;

            $redeem_offer=$offer_detail['redeem_offer'] + 1;

			$datalist = UserPassedOffer::create(['user_id' => $user_id, 'offer_id' => $offer_id]);

			//$updateOffer = Offer::where('id',$offer_id)->update(array('max_redeemar'=> $max_redeemar));

			//$redeemOffer = Offer::where('id',$offer_id)->update(array('redeem_offer'=> $redeem_offer));

			$datalist['messageCode']="R01001";
             
            }

			return $datalist;

	}


	// User Offer Redeemption


	public function postRedeemption(Request $request)
	{

		$data=json_decode($request->get('data'));

		$user_id=$data->user_id;

		$target_id=$data->target_id;


		$logo=Logo::where('target_id',$target_id)->where('status',1)->where('action_id',4)->first();	

        if(count($logo)>0)
        {

			$reedemer_id=$logo->reedemer_id;

			$offer_id=$data->offer_id;


			$offer_detail=Offer::where('id',$offer_id)->where('created_by',$reedemer_id)->where('max_redeemar','>','0')->whereNotIn('status',array(2,4))->first();

			if(count($offer_detail) >0) {

			$max_redeemar=$offer_detail['max_redeemar'] - 1;

			$redeem_offer=$offer_detail['redeem_offer'] + 1;

			$updateOffer = Offer::where('id',$offer_id)->update(array('max_redeemar'=> $max_redeemar, 'redeem_offer'=> $redeem_offer));

			$datalist = RedeemptionOffer::create(['user_id' => $user_id, 'offer_id' => $offer_id]);

			$datalist['messageCode']="R01001";

		}
		else
		{
         	$datalist['messageCode']="R01002";

		}

		}
		else

		{
            $datalist['messageCode']="R01003";

		}


		return $datalist;

	}

	// Show User Passed Offer

	public function postMypassedoffer(Request $request)
	{

			// Get passed users list offer

			$data=json_decode($request->get('data'));


			$user_id=$data->user_id;

			$userpassedoffer=UserPassedOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

			$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->whereNotIn('status',array(2,4))->where('max_redeemar','>',0)->whereIn('id',$userpassedoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

			$datalist['messageCode']="R01001";

			$datalist['data']=$offer_list;

			return $datalist;

	}

   // Show Offer Details

	public function postOfferdetail(Request $request)
	{

			$data=json_decode($request->get('data'));
			$offer_id=$data->offer_id;
			$user_id=$data->user_id;

			// check offer bank or passed

			$userbankoffer=UserBankOffer::where('user_id',$user_id)->where('offer_id',$offer_id)->get();
			$userpassoffer=UserPassedOffer::where('user_id',$user_id)->where('offer_id',$offer_id)->get();

			if(count($userbankoffer)>0)
			{
				$datalist['messageCode']="R01002";
			}
			elseif(count($userpassoffer)>0)
			{

				$datalist['messageCode']="R01003";

			}
			else
			{
             $datalist['messageCode']="R01001";

			}

			$now=date('Y-m-d h:i:s');

			$offer_list=Offer:: select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->where('end_date','>=',$now)->where('id',$offer_id)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')->orderBy('created_at','desc')->get();

			$datalist['data']=$offer_list;

			return $datalist;

	}


	public function postValidateofferdetail(Request $request)
	{

			$data=json_decode($request->get('data'));
			$offer_id=$data->offer_id;
			$user_id=$data->user_id;
			$now=date('Y-m-d h:i:s');

			$userbankoffer=UserBankOffer::where('user_id',$user_id)->where('offer_id',$offer_id)->with('userDetail')->lists('offer_id');


			$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->whereNotIn('status',array(2,4))->where('max_redeemar','>',0)->whereIn('id',$userbankoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail','myofferDetails')->orderBy('created_at','desc')->get();

			$datalist['data']=$offer_list;

			return $datalist;

	}


	public function postChecktarget(Request $request)
	{
	    //$target_id=$request->get('target_id');
		$data=json_decode($request->get('data'));
		$target_id=$data->target_id;
		$logo=Logo::where('target_id',$target_id)
				->where('status',1)
				->get()
				->first();	
		if(count($logo) >0)
		{
			if($logo->reedemer_id)
			{
				$company_name=User::where('id',$logo->reedemer_id)
							->first()
							->company_name;
				$logo_name= $logo->logo_name;

				// Get Default Logo
				$defaultlogo=Logo::where('reedemer_id',$logo->reedemer_id)
							->where('default_logo',1)
							->where('status',1)
							->first();	
				//dd($defaultlogo->action_id);
				
				if($logo->action_id==2)
				{

					// $offer_list=Offer::where('created_by',$logo->reedemer_id)
					// 		->where('campaign_id',$defaultlogo->particular_id)
					// 		->where('status',1)
					// 		->get();
					$offer_list=null;
				}
				else if($logo->action_id==3)
				{
					$offer_list=Offer::where('created_by',$logo->reedemer_id)
							->where('id',$logo->particular_id)
							->where('status',1)
							->get();
				}
				else
				{
					$offer_list=null;
				}
				// get video links 
				$video_list=Video::where('uploaded_by',$logo->reedemer_id)
							->orderBy('default_video','desc')
							->where('status',1)
							->get();
				$dataArr=array(
								'reedemer_id'=>$logo->reedemer_id,
								'companyName' => $company_name, 
								'default_logo' => $defaultlogo->logo_name , 
								'logoImage' => $logo_name, 
								'videoList' => $video_list,
								'action_id' => $logo->action_id,
								'campaign_id' => $logo->particular_id,
								'offerList' => $offer_list
							);
				$dataStr=json_encode($dataArr);
				$response['status']='success';
				$return['messageCode']="R01001";
				$return['data']=$dataStr;

				// Put all response to database for testing purpose
				// Will have to remove this later
				$pp=new Pp();
				$pp->val=$dataStr;
				$pp->save();
			}
			else
			{
				$response['status']='success';
				$this->sendEmail($logo->contact_email,$logo->logo_text);
		 		$return['messageCode']="R01002";
			}

		}
		else
		{
			$response['data']='Logo not found';
			$return['messageCode']="R01003";

		}	
	 	
		return $return;
	}

	public function sendEmail($contactemail,$logo_text)
	{


		$user_email=$contactemail;
		$admin_email="admin@mailinator.com";
		
		$user_name=$logo_text;
		$data = array('user_name' => $user_name, 'user_email' => $user_email);
		\Mail::send('emails.nopartner', $data, function($message) use ($admin_email,$user_email,$user_name){ 
			$subject="Join with Redeemar";
			$message->from($admin_email, $user_name);
			$message->to($user_email)->subject($subject);
		}); 
	}
	

}
