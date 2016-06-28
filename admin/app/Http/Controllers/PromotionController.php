<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Http\Response; 
use App\Model\User;
use App\Model\Video;
use App\Model\Directory;
use App\Model\Category;
use App\Model\Offer;
use App\Model\OfferDetail;
use Hash;
use Validator;
use App\Model\Logo;
use App\Helper\vuforiaclient;
use App\Helper\helpers;
use Auth; 
use Session ;
use App\Model\Campaign;
use App\Model\Inventory;
use App\Model\UserBankOffer;
use DB;


class PromotionController extends Controller {	
	
	
	public function __construct( )
	{
		if($this->middleware('auth'))		
		{
			Auth::logout();
    		return redirect('auth/login');	
    	}
		
	}

	public function getIndex()
	{
		$reedemer_id=Auth::User()->id;
		$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))
					->where('created_by',$reedemer_id)
					->where('status','1')
					->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail')
					->orderBy('created_at','desc')
					->get();			
		return $offer_list;

	}


	public function getList()
	{
		$user_id=Auth::User()->id;

		$userbankoffer=UserBankOffer::where('user_id',$user_id)->with('userDetail')->lists('offer_id');

			$offer_list=Offer::select(array('*',DB::raw('DATEDIFF(CAST(end_date as char), NOW()) AS expires')))->whereNotIn('status',array(2,4))->where('max_redeemar','>',0)->whereIn('id',$userbankoffer)->with('categoryDetails','subCategoryDetails','partnerSettings','companyDetail','myofferDetails')->orderBy('created_at','desc')->get();		
		return $offer_list;	
		

	}

	

	public function postImageid(Request $request)
	{
		$user_id=Auth::User()->id;
		//dd($user_id);
		//dd($request->get('file_name'));
		dd($request->all());
		$str=explode("filemanager/userfiles/",$request->get('file_name'));
		$str_value=explode("/",$str[1]);	
		//dd($str_value)	;
		// Get name of image
		$image_name=array_pop($str_value);

		//$last_ele_len=strlen($image_name);
		//$base_dir=substr($str[1], 0, $last_ele_len-4);
		//$base=env('UPLOADS')."/".$base_dir;
		//dd($str[1]);
		$str_wot_name=rtrim(str_replace($image_name, '', $str[1]),"/");
		$base=env('UPLOADS')."/".$str_wot_name;
		dd($base);
		$directory=Directory::where('created_by',$user_id)
		   		   ->where('file_name',$image_name)
		 		   ->where('updated_at',$base)
		 		   ->first();
		$id=$directory->id;
		//dd($id);
		return $id;
	}

	public function postStoreoffer(Request $request)
	{		
		//dd($request->all());
		$user_id=Auth::User()->id;

		$campaign_id=$request->get('campaign_id');		
		$offer_description=$request->get('offer_description');
		$total_redeemar=$request->get('total_redeemar');
		$total_redeemar_price=$request->get('total_redeemar_price');
		$c_s_date_user=explode("/",$request->get('c_s_date'));
		$c_s_date=$c_s_date_user[2]."-".$c_s_date_user[0]."-".$c_s_date_user[1];
		$c_e_date_user=explode("/",$request->get('c_e_date'));
		$c_e_date=$c_e_date_user[2]."-".$c_e_date_user[0]."-".$c_e_date_user[1];
		
		
		$total_payment=$request->get('total_payment');
		if($total_payment==0.65)
		{
			$pay=1;
		}
		else
		{
			$pay=2;	
		}
		$what_you_get=$request->get('what_you_get');
		$more_information=$request->get('more_information');
		$created_by=Auth::user()->id;
		$pay_value=$request->get('pay_value');
		$retails_value=$request->get('retails_value');
		$include_product_value=$request->get('include_product_value');
		$discount=$request->get('discount');
		$value_calculate=$request->get('value_calculate');
		$product_id_arr=explode(",",$request->get('product_id_str'));
		$camp_img_id=$request->get('camp_img_id');
		$validate_after=$request->get('validate_after');
		$validate_within=$request->get('validate_within');		
		$choose_image=$request->get('choose_image');

		$logo=Logo::where('reedemer_id',$user_id)->first();
		if($choose_image==1)
		{
			//dd($camp_img_id."A");
			$directory=Directory::find($camp_img_id);

			
			$directory_base_path=$directory->directory_base_path;

			$logo_image_path=env("UPLOADS")."/thumb/".$logo->logo_name;
			$offer_image_old=$directory->file_name;
			$offer_image_path_old=$directory_base_path."/".$offer_image_old;

			$ext = pathinfo($offer_image_path_old, PATHINFO_EXTENSION);

			$desired_width=env("OFFER_IMAGE_SIZE");
			$dest_dir=$directory_base_path."/thumb/";

			if(!file_exists($dest_dir))
			{
				//create base folder
				mkdir($dest_dir, 0777);
			}
			$dest=$dest_dir.$offer_image_old;			
			$thumb_name=$this->create_thumb($offer_image_path_old, $dest, $desired_width);			
			$source_file=$dest;			
			$offer_image_name="offer_".time().rand(99,99999).$user_id.".".$ext;

			$output_file_path="../uploads/offer/".$offer_image_name;	
			//dd($logo_image_path)		
			$this->watermark($source_file, $output_file_path, $logo_image_path);

			$offer_image=$offer_image_name;
			$offer_image_path=env("IMAGE_URL")."uploads/offer/".$offer_image;			
		}
		else
		{
			//dd("B");
			$offer_image=$logo->logo_name;
			$offer_image_path=env("IMAGE_URL")."uploads/original/".$offer_image;			
		}
		
		$user=User::find($logo->reedemer_id);



		// echo $campaign_id."A<br>";
		// echo $offer_description."B<br>";
		// echo $total_redeemar."C<br>";
		// echo $total_redeemar_price."D<br>";
		// //echo $c_s_date_user."E<br>";
		// echo $c_s_date."F<br>";
		// //echo $c_e_date_user."G<br>";
		// echo $c_e_date."H<br>";
		
		
		// echo $total_payment."I<br>";
		// echo $pay."J<br>";
		// echo $what_you_get."K<br>";
		// echo $more_information."L<br>";
		// echo $created_by."M<br>";
		// echo $pay_value."N<br>";
		// echo $retails_value."O<br>";
		// echo $include_product_value."P<br>";
		// echo $discount."Q<br>";
		// echo $value_calculate."R<br>";
		// print_r($product_id_arr)."S<br>";
		// echo $camp_img_id."T<br>";
		// echo $validate_after."U<br>";
		// echo $validate_within."V<br>";
		// echo $choose_image."W<br>";
		// die();
		//dd($user->lat);
		$zipcode = $user->zipcode;
		$latitude =$user->lat;
		$longitude =$user->lng;

		$category_id =$logo->cat_id;
		$subcat_id =$logo->subcat_id;

		//dd($category_id);
		$offer = new Offer();
		$offer->campaign_id				= $campaign_id;			
		$offer->cat_id 					= $category_id;	
		$offer->subcat_id 				= $subcat_id;	
		$offer->offer_description 		= $offer_description;	
		$offer->max_redeemar 			= $total_redeemar;	
		$offer->price 					= $total_redeemar_price;	
		$offer->pay 					= $pay;	
		$offer->start_date 				= $c_s_date;
		$offer->end_date 				= $c_e_date;			
		$offer->what_you_get 			= $what_you_get;		
		$offer->more_information 		= $more_information;
		$offer->pay_value 				= $pay_value;
		$offer->retails_value 			= $retails_value;
		$offer->include_product_value 	= $include_product_value;
		$offer->discount 				= $discount;
		$offer->validate_after 			= $validate_after;
		$offer->validate_within 		= $validate_within;
		$offer->zipcode 				= $zipcode;
		$offer->latitude 				= $latitude;
		$offer->longitude 				= $longitude;
		$offer->value_calculate 		= $value_calculate;
		$offer->offer_image 			= $offer_image;	
		$offer->offer_image_path 		= $offer_image_path;	
		$offer->created_by 				= $created_by;	
		if($offer->save())
		{
			$offer_id = $offer->id;
			//dd($offer_id);
			foreach($product_id_arr as $product_id)
			{
				//dd($product_id);
				$data[] = array('offer_id'=>$offer_id, 'inventory_id'=>$product_id, 'created_at'=>date("Y-m-d H:i:s"), 'updated_at'=>date("Y-m-d H:i:s"));
			}

			OfferDetail::insert($data); // Eloquent

			return 'success';
		}
		else
		{
			return 'error';
		}
	}

	function watermark($source_file_path, $output_file_path, $stamp)
	{
		define('WATERMARK_OVERLAY_IMAGE', $stamp);
		define('WATERMARK_OVERLAY_OPACITY', 80);
		define('WATERMARK_OUTPUT_QUALITY', 90);
		//dd($source_file_path);
		list($source_width, $source_height, $source_type) = getimagesize($source_file_path);

	    if ($source_type === NULL) {
	        return false;
	    }
	    //dd($source_type);
	    switch ($source_type) {
	        case IMAGETYPE_GIF:
	       // dd("A");
	            $source_gd_image = imagecreatefromgif($source_file_path);
	            break;
	        case IMAGETYPE_JPEG:
	        //dd("B");
	            $source_gd_image = imagecreatefromjpeg($source_file_path);
	            break;
	        case IMAGETYPE_PNG:
	        //dd("C");
	            $source_gd_image = imagecreatefrompng($source_file_path);
	            break;
	        default:
	            return false;
	    }
	    $overlay_gd_image = imagecreatefrompng(WATERMARK_OVERLAY_IMAGE);
		$overlay_width = imagesx($overlay_gd_image);
		$overlay_height = imagesy($overlay_gd_image);
	    //$overlay_width = 100;
	    //$overlay_height = 100;
	    imagecopymerge(
	        $source_gd_image,
	        $overlay_gd_image,
	        10,  //x position
	        10,  //y position
	        0,
	        0,
	        $overlay_width,
	        $overlay_height,
	        WATERMARK_OVERLAY_OPACITY
	    );
	    imagejpeg($source_gd_image, $output_file_path, WATERMARK_OUTPUT_QUALITY);
	    imagedestroy($source_gd_image);
	    imagedestroy($overlay_gd_image);
	}

	

	public function getFolderid()
	{
		
		$id=Auth::User()->id;

		return $id;
	}

	public function postDefaultlogo()
	{
		$user_id=Auth::User()->id;
		
		$logo = Logo::where('reedemer_id',$user_id)->where('default_logo', 1)->first();
		//dd($logo->logo_name);
		return $logo->logo_name;
	}

	public function postLogodetails()
	{
		$user_id=Auth::User()->id;
		
		$logo=Logo::where('reedemer_id',$user_id)->first();
		//dd($logo->toArray());
		$logo_arr=array(
			'target_id'=>$logo->target_id,
			'original_cat_id'=>$logo->cat_id,
			'cat_id'=>Category::where('id',$logo->cat_id)->first()->cat_name,
			'original_subcat_id'=>$logo->subcat_id,
			'subcat_id'=>$logo->subcat_id >0 ? Category::where('id',$logo->subcat_id)->first()->cat_name:'Not Applicable'
		);
		return $logo_arr;
	}

	public function postSoftdeloffer(Request $request)
	{
		//dd($request[0]);
		$id=$request[0];
		$promotion=Offer::findOrFail($id);
		$promotion->status 	= 4; //Soft Delete			
		//$campaign->created_by 		= $created_by;
		$promotion->save();
		//dd($promotion);


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

		//return $dest;
	}

	public function getCampaignbyuser()
	{
		$user_id=Auth::User()->id;
		$campaign=Campaign::where('created_by',$user_id)->get();

		return $campaign;
	}
	
	

	
}
