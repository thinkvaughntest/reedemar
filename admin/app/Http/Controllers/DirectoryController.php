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
use File; 
use Session ;
use App\Model\Directory;


class DirectoryController extends Controller {
	
	

	public function __construct( )
	{
		if($this->middleware('auth'))
		//if(!Auth::user()->id)
		{
			Auth::logout();
    		return redirect('auth/login');	
    	}
		
	}
	
	// public function postShow()
	// {
	// 	$id=Auth::user()->id;
	// 	// Get current logged in user TYPE
	// 	$type=Auth::user()->type;
	// 	if($type==1)
	// 	{
	// 		$directory = Directory::where('directory_id',0)
	// 					 ->orderBy('id','DESC')
	// 					 ->get();
	// 	}
	// 	else
	// 	{
	// 		$directory = Directory::where('directory_id',0)
	// 					 ->where('created_by',$id)
	// 					 ->orderBy('id','DESC')
	// 					 ->get();
	// 	}
	// 	return $directory;
	// }

	// public function getAlldirectory()
	// {
	// 	$created_by=Auth::user()->id;
	// 	$directory = Directory::where('status',1)
	// 				 ->where('created_by',$created_by)
	// 				 ->where('directory_id',0)
	// 				 ->orderBy('id','DESC')
	// 				 ->get();
	// 	return $directory;
	// }

	public function getDirectorylist()
	{
		$created_by=Auth::user()->id;

		$directory = Directory::where('status',1)
					 ->where('created_by',$created_by)
					 ->orderBy('id','DESC')
					 ->get();

		return $directory;
	}
	public function getAlllisting($id)
	{
		$created_by=Auth::user()->id;
		$directory = Directory::where('status',1)					 
					 ->where('directory_id',$id)
					 ->where('created_by',$created_by)
					 ->orderBy('id','DESC')
					 ->get();

		if($id != "0" || $id != 0)
		{
			$di = Directory::where('id',$id)
					->get();
			$di['directory_id'] = $di[0]->directory_id;
		}
		else
		{
			$di['directory_id'] = null;
		}
		
		if($directory->count() >0)
		{

			$dir_arr=[];
			
			foreach($directory as $dir)
			{

				$dir_arr[]=array(
					'id' => $dir['id'],
					'directory_id' => $dir['directory_id'],
					'original_name' => $dir['original_name'],
					'file_name' => $dir['file_name'],
					'directory_base_path' => $dir['directory_base_path'],
					'directory_url' => $dir['directory_url'],
					'directory' => $dir['directory'],
					'status' => $dir['status'],
					'created_by' => $dir['created_by'],
					'created_at' => $dir['created_at'],
					'updated_at' => $dir['updated_at'],
					'previous_id' => $di['directory_id'],
				);
			}
		}
		else
		{	
			$dir_arr[]=array('previous_id' => $di['directory_id']);
		}
		
		return $dir_arr;
	}

	public function postStore(Request $request)
	{
		//dd($request->all());
		
		$created_by=Auth::user()->id;
		$upload_dir = env('UPLOADS');

		//uploads folder url
		$base_dir=$upload_dir."/".$created_by;	
		//Check if dir not exists create
		if(!file_exists($base_dir))
		{
			//create base folder
			mkdir($base_dir, 0777);
		}

		//Filemanager Url	
		$dest_dir="../../filemanager/userfiles/".$created_by;
		//Check if copy dir not exists create
		if(!file_exists($dest_dir))
		{
			//create base folder
			mkdir($dest_dir, 0777);
		}

		//Directory name given by user
		$directory_name=$request->get('dir_name');
		//$directory_url=url()."/".$directory_name;
		$directory_path=$base_dir."/".$directory_name;
		$copy_directory_path=$dest_dir."/".$directory_name;

		//Check if dir not exists create
		if(!file_exists($directory_path))
		{
			//create base folder
			mkdir($directory_path, 0777);
		}

		//Check if dir not exists create
		if(!file_exists($copy_directory_path))
		{
			//create base folder
			mkdir($copy_directory_path, 0777);
		}

		// if($request->get('new_dir_id'))
		// {
		// 	dd("V");
		// }
		// else
		// {
		$directory_id=0;
		//}

		//dd($directory_path."--".$copy_directory_path);
		// if($request->get('new_dir_id'))
		// {
		// 	//dd("A");
		// 	$directory_id=$request->get('new_dir_id');
		// 	$directory = Directory::where('status',1)->where('id',$directory_id)->orderBy('id','DESC')->get();
		// 	$base_dir = $directory[0]['directory_base_path'];
		// 	//$dest_dir = $directory[0]['directory_url'];
		// 	$directory_url = $directory[0]['directory_url']."/".$directory_name;
		// }
		// else
		// {
		// 	$directory_id=0;
		// }
		// //check if base folder exists
		// if(!file_exists($base_dir))
		// {
		// 	//create base folder
		// 	mkdir($base_dir, 0777);
		// }
		// if(!file_exists($dest_dir))
		// {
		// 	//create base folder
		// 	mkdir($dest_dir, 0777);
		// }

		// check if folder exists
		// if(!file_exists($base_dir."/".$directory_name))
		// {	
		// 	//create folder		
		// 	mkdir($base_dir."/".$directory_name, 0777);
		// }
		// else
		// {
		// 	return 'folder_exists';
		// }

		// if(!file_exists($dest_dir."/".$directory_name))
		// {	
		// 	//create folder		
		// 	mkdir($dest_dir."/".$directory_name, 0777);
		// }
		//dd($base_dir);
		$status=1;	
		

		$directory = new Directory();
		$directory->directory_id 		= $directory_id;			
		$directory->original_name 		= $directory_name;	
		$directory->file_name 			= $directory_name;				
		$directory->directory_base_path = $base_dir."/".$directory_name;	
		$directory->directory_url 		= $directory_path;
		$directory->copy_url 			= $copy_directory_path;
		$directory->directory 			= 1;
		$directory->status 				= $status;
		$directory->created_by 			= $created_by;


		//$directory = new Directory();
		//$directory->directory_name 			= $directory_name;			
		//$directory->directory_base_path 	= $base_dir."/".$directory_name;	
		//$directory->status 					= $status;	
		//$directory->created_by 				= $created_by;		
		if($directory->save())
		{
			return 'success';
		}
		else
		{
			return 'error';
		}
		
	}

	public function getDelete($id = Null)
	{			
		$directory = Directory::find($id);	

		//dd($id);

		if($directory->directory==0)
		{
			//dd($directory->copy_url);
			//$get_explode=explode("/",$directory->copy_url);
			//dd($get_explode[6]);
			//dd($directory->directory_url);
			//dd(count($get_explode));
			$file_name=$directory->file_name;
			$file_path=$directory->directory_base_path."/".$directory->file_name;
			$copy_file_path="../../filemanager/userfiles/".$directory->created_by."/".$directory->file_name;

			if(file_exists($file_path))
			{
				@unlink($file_path);
			}
			if(file_exists($copy_file_path))
			{
				@unlink($copy_file_path);
				//echo "BBB";
			}
		}
		else
		{
			
			$file_name=$directory->file_name;
			$file_path=$directory->directory_base_path;
			//dd($file_path);
			//$copy_file_path="../../filemanager/userfiles/".$directory->created_by."/".$directory->file_name;

			//dd($file_path);
			$this->removeDirectory($file_path);
			//$this->removeDirectory($file_path);
		}
		//dd("c");
		//dd($directory->directory_base_path);	
		//dd($file_path->directory);	
		//dd($directory->directory_base_path);
		//dd($directory->directory);
		// $thubm_path=env('UPLOADS')."/inventory/thumb/";
		// $medium_path=env('UPLOADS')."/inventory/medium/";
		// $original_path=env('UPLOADS')."/inventory/original/";

		// if(file_exists($thubm_path.$inventory->campaign_image))
		// {
		// 	@unlink($thubm_path.$inventory->campaign_image);
		// } 
		// if(file_exists($medium_path.$inventory->campaign_image))
		// {
		// 	@unlink($medium_path.$inventory->campaign_image);
		// }
		//======================
		 // if($directory->directory==1)
		 // {
			// $file=$directory->directory_base_path."/";
			// File::deleteDirectory($file);
		 // }
		 // else
		 // {
		 // 	$file=$directory->directory_base_path."/".$directory->file_name;
		 // 	if(file_exists($file))
			// {
			// 	unlink($file);
			// }
		 // }
		 //============================
		 
		 //dd($file);
		

		// if($directory->directory==1)
		// {
		// 	if (is_dir($directory->directory_base_path)) 
		// 	{
		// 		rmdir($directory->directory_base_path);
		// 	}
		// }
		//unlink();
		//$directory->delete();
		if($directory->delete())
		{
			return 'success';
		}		
		else{
			return 'error';
		}		
	}

	function removeDirectory($path) {
		//dd("B");
		$files = glob($path . '/*');
		//dd($files);
		foreach ($files as $file) {
			is_dir($file) ? removeDirectory($file) : unlink($file);
		}
		rmdir($path);
		return;
	}

	public function postUpload(Request $request)
	{			

		//dd($request->dir_id)	;
		//dd($request->input('home_thumb_img'));
		//dd($request->input('deal_details_img'));
		//dd($request->input('deal_details_thumb_img'));
		$created_by=Auth::user()->id;
		$upload_dir = env('UPLOADS');
		$copy_base_path=env('REPOSITORY_IMAGE_COPY').$created_by;
		$upload_path=$upload_dir."/".$created_by;
		
		if(!file_exists($upload_path))
		{
			//create base folder
			mkdir($upload_path, 0777);
		}	
		if(!file_exists($copy_base_path))
		{
			//create base folder
			mkdir($copy_base_path, 0777);
		}	
		//dd("check");	
		
		if($request->dir_id==0)
		{
			$dir_id=$request->dir_id;
			$base_dir=$upload_dir."/".$created_by;
			//Copy same into filemanager			
			$copy_dir=$copy_base_path;
		}
		else
		{
			$dir_id=$request->dir_id;
			$directory=Directory::find($dir_id);
			$base_dir=$directory->directory_base_path;
			$dir_extract=explode("../uploads/",$base_dir);
			$extra_dir=$dir_extract[1];			
			$copy_dir=env('REPOSITORY_IMAGE_COPY').$extra_dir;
		}	

		$folder_1="home_page_image";
		$folder_2="home_page_thumb";
		$folder_3="deal_details";
		$folder_4="deal_details_thumb";

		// Folder path to admin
		$folder_path_1=$base_dir."/".$folder_1;
		$folder_path_2=$base_dir."/".$folder_2;
		$folder_path_3=$base_dir."/".$folder_3;
		$folder_path_4=$base_dir."/".$folder_4;

		// Folder path to filemanager
		$filemanager_folder_path_1=$copy_dir."/".$folder_1;
		$filemanager_folder_path_2=$copy_dir."/".$folder_2;
		$filemanager_folder_path_3=$copy_dir."/".$folder_3;
		$filemanager_folder_path_4=$copy_dir."/".$folder_4;

		//dd($copy_dir);
		// Makeing folder to admin
		$this->make_folder($folder_path_1);
		$this->make_folder($folder_path_2);
		$this->make_folder($folder_path_3);
		$this->make_folder($folder_path_4);

		// Makeing folder to filemanager
		$this->make_folder($filemanager_folder_path_1);
		$this->make_folder($filemanager_folder_path_2);
		$this->make_folder($filemanager_folder_path_3);
		$this->make_folder($filemanager_folder_path_4);
		
		//dd($base_dir);
		$image_type="png";
		//dd("V");
		//dd($image_type);
		//Get desire image name by user
		$upload_image_name=$request->image_name.".jpg";		

		$home_page_image_path=$folder_path_1."/".$upload_image_name;
		$home_page_image_path_copy=$filemanager_folder_path_1."/".$upload_image_name;

		$home_page_image_thumb_path=$folder_path_2."/".$upload_image_name;
		$home_page_image_thumb_path_copy=$filemanager_folder_path_2."/".$upload_image_name;

		$deal_details_image_path=$folder_path_3."/".$upload_image_name;
		$deal_details_image_path_copy=$filemanager_folder_path_3."/".$upload_image_name;

		$deal_details_image_thumb_path=$folder_path_4."/".$upload_image_name;
		$deal_details_image_thumb_path_copy=$filemanager_folder_path_4."/".$upload_image_name;

		//$request->input('home_thumb_img')

		//$upload_img_url=$base_dir."/".$upload_image_name;
		//$copy_img_url=$copy_dir."/".$upload_image_name;

		//dd($request->all());
		//dd($request->input('home_thumb_img'));
		//dd($request->input('deal_details_img'));
		//dd($request->input('deal_details_thumb_img'));
		//$base64_to_jpeg=$this->base64_to_jpeg($request->image_data,$upload_img_url);
		//$upload_to_filemanager=$this->base64_to_jpeg($request->image_data,$copy_img_url);

		$obj = new helpers();
		//Actually uploadingimage
		$src=$request->input('home_thumb_img');
		//dd($thumb_page);
		//$original_path=$upload_img_url;
		//$medium_path=$original_path;
		//dd($original_path);
		////$medium=$obj->create_jpg_from_png($src, $original_path);
		//$medium=$obj->create_thumb($src, $original_path, '1000');
		//$small=$obj->create_thumb($thumb_page, $original_path, '1000');		
		//$original=$obj->base64_to_jpeg($thumb_page, $original_path);
		//$src=$original_path;
		//dd("a");
		//$small=$obj->create_thumb($src, $thumb_path, $thumb_size);	
		//dd($src);
		//dd("a");
		//if($image_type=="png")
		//{
			$home_page_img=$obj->convertImage($src, $home_page_image_path, $image_type);
			$home_page_img_copy=$obj->convertImage($src, $home_page_image_path_copy, $image_type);

			$home_page_img_thumb=$obj->convertImage($src, $home_page_image_thumb_path, $image_type);
			$home_page_img_copy_thumb=$obj->convertImage($src, $home_page_image_thumb_path_copy, $image_type);

			$deal_details_img=$obj->convertImage($src, $deal_details_image_path, $image_type);
			$deal_details_img_copy=$obj->convertImage($src, $deal_details_image_path_copy, $image_type);

			$deal_details_img_thumb=$obj->convertImage($src, $deal_details_image_thumb_path, $image_type);
			$deal_details_img_copy_thumb=$obj->convertImage($src, $deal_details_image_thumb_path_copy, $image_type);
			//$medium_path_new=$medium_path;
			//$medium=$obj->create_thumb($medium_path_new, $original_path, '1000');
		//}
		// else if($image_type=="gif")
		// {
		// 	$medium=$obj->convertImage($src, $medium_path, $image_type);
		// 	//$medium_path_new=$medium_path;
		// 	//$medium=$obj->create_thumb($medium_path_new, $medium_path, $medium_size);
		// }
		// else
		// {
		// 	$medium=$obj->create_thumb($src, $medium_path, $medium_size);
		// }
		//dd("ccheck");
		$directory_save = new Directory();
		$directory_save->directory_id 		= $dir_id;			
		$directory_save->original_name 		= $upload_image_name;	
		$directory_save->file_name 			= $upload_image_name;				
		$directory_save->directory_base_path= $home_page_image_path;	
		$directory_save->copy_url= $home_page_image_path_copy;
		$directory_save->directory_url 		= '';
		$directory_save->directory = 0;
		$directory_save->status = 1;
		$directory_save->created_by = $created_by;

		

		if($directory_save->save())
		{
			$upload_arr=array('status'=>'success','image_name'=>$home_page_image_path,'image_id'=>$directory_save->id);
		 	return $upload_arr;
		}
		else{
			$upload_arr=array('status'=>'error');
		 	return $upload_arr;
		}

		//echo $_FILES['image_file']['name']."---".$request->input('dir_id');
		//exit;
		//dd($request->all());
		//return $request;
	}

	public function getUpdatestatus($id)
	{
		$directory = Directory::find($id);
		if($directory->status ==0)
		{
			$new_status=1;
		}
		else
		{
			$new_status=0;
		}
		$directory->status = $new_status;			
		if($directory->save())
		{
			return 'success';
		}
	}

	public function getUploadrepoform()
	{
		$getAlldirectory=$this->getAlldirectory();
		$directory_list=$getAlldirectory;
		//dd($directory_list);
		return view('admin.directory.upload_file')->with('directory_list',$directory_list);
		//return view('admin.directory.upload_file','directory_list');
	}

	//Lis all directory
	public function getAlldirectory()
	{
		$created_by=Auth::user()->id;
		$directory = Directory::where('status',1)
					 ->where('created_by',$created_by)
					 ->where('directory',1)
					 ->orderBy('id','DESC')
					 ->get();
		return $directory;
	}
	
	public function postUploadofferp(Request $request)
	{	
		$created_by=Auth::user()->id;
		$upload_dir = env('UPLOADS');

		$base_dir=$upload_dir."/".$created_by;		
		$dest_dir="../../filemanager/userfiles/".$created_by;

		//check if base folder exists
		if(!file_exists($base_dir))
		{
			//create base folder
			mkdir($base_dir, 0777);
		}
		if(!file_exists($dest_dir))
		{
			//create base folder
			mkdir($dest_dir, 0777);
		}
		
		$dir_id_table=$request->input('directory_id');
		$directory =Directory::find($dir_id_table);		
		$upload_path=$directory->directory_base_path;
		$image_name=$request->input('file');		
		$created_by=Auth::user()->id;
		
		$obj = new helpers();
		$folder_name=env('UPLOADS');
		$file_name=$_FILES[ 'file' ][ 'name' ];
		$temp_path = $_FILES[ 'file' ][ 'tmp_name' ];
		//$request->input('dir_id');
		
		
		$original_path= $upload_path."/";		
		$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if($image_name)
		{
			$new_file_name = $image_name;
		}
		else
		{
			$new_file_name = time()."_".rand(111111111,999999999).'.'.$extension; // renameing image
		}		
		$directory_url=url()."/".$original_path.$new_file_name;
		$check_url=$original_path.$new_file_name;
		
		if (File::exists($check_url))		
		{
			return 'already_exists';
			echo "has";
		}
		
		
		$file_ori = $_FILES[ 'file' ][ 'tmp_name' ];	

		$file_name_arr=explode("uploads/",$upload_path);		
		$up_folder=$directory->file_name;
		$up_folder_path="../../filemanager/userfiles/".$created_by."/".$up_folder;		
		$copy_file_url=$up_folder_path."/".$new_file_name;		
		$up_path=$upload_path."/".$new_file_name;		
		
		//check if base folder exists
		if(!file_exists($up_folder_path))
		{
			//create base folder
			mkdir($up_folder_path, 0777);
		}
		//dd($up_folder_path);
		if(!file_exists($upload_path))
		{
			//create base folder
			mkdir($upload_path, 0777);
		}
		
		copy($file_ori, $copy_file_url);			
		move_uploaded_file($file_ori, $up_path);		
		
		
		
		//dd()
		$directory_save = new Directory();
		$directory_save->directory_id 		= $dir_id_table;			
		$directory_save->original_name 		= $_FILES[ 'file' ][ 'name' ];	
		$directory_save->file_name 		= $new_file_name;				
		$directory_save->directory_base_path 		= $up_path;	
		$directory_save->directory_url 		= $directory_url;
		$directory_save->directory = 0;
		$directory_save->status = 1;
		$directory_save->created_by = $created_by;

		if($directory_save->save())
		{
			return 'success';
		}

		//echo $_FILES['image_file']['name']."---".$request->input('dir_id');
		//exit;
		//dd($request->all());
		//return $request;
	}

	public function postUploadoffer(Request $request)
	{

		$created_by=Auth::user()->id;
		$upload_dir = env('UPLOADS');

		$base_dir=$upload_dir."/".$created_by;
		$directory_name=$request->get('dir_name');
		$dest_dir="../../filemanager/userfiles/".$created_by;
		$directory_url=url()."/".$directory_name;
		if($request->get('new_dir_id'))
		{
			
			$directory_id=$request->get('new_dir_id');
			$directory = Directory::where('status',1)->where('id',$directory_id)->orderBy('id','DESC')->get();
			$base_dir = $directory[0]['directory_base_path'];
			$dest_dir = $directory[0]['directory_url'];
			$directory_url = $directory[0]['directory_url']."/".$directory_name;
		}
		else
		{
			$directory_id=0;
		}
		//check if base folder exists
		if(!file_exists($base_dir))
		{
			//create base folder
			mkdir($base_dir, 0777);
		}
		if(!file_exists($dest_dir))
		{
			//create base folder
			mkdir($dest_dir, 0777);
		}

		// check if folder exists
		if(!file_exists($base_dir."/".$directory_name))
		{	
			//create folder		
			mkdir($base_dir."/".$directory_name, 0777);
		}
		else
		{
			return 'folder_exists';
		}

		if(!file_exists($dest_dir."/".$directory_name))
		{	
			//create folder		
			mkdir($dest_dir."/".$directory_name, 0777);
		}

		$status=1;	
		

		$directory = new Directory();
		$directory->directory_id 		= $directory_id;			
		$directory->original_name 		= $directory_name;	
		$directory->file_name 		= $directory_name;				
		$directory->directory_base_path 		= $base_dir."/".$directory_name;	
		$directory->directory_url 		= $directory_url;
		$directory->directory = 1;
		$directory->status = $status;
		$directory->created_by = $created_by;


		//$directory = new Directory();
		//$directory->directory_name 			= $directory_name;			
		//$directory->directory_base_path 	= $base_dir."/".$directory_name;	
		//$directory->status 					= $status;	
		//$directory->created_by 				= $created_by;		
		if($directory->save())
		{
			return 'success';
		}
		else
		{
			return 'error';
		}
	}


	function base64_to_jpeg($base64_string, $output_file) {
	    $ifp = fopen($output_file, "wb"); 

	    $data = explode(',', $base64_string);

	    fwrite($ifp, base64_decode($data[1])); 
	    fclose($ifp); 

	    return $output_file; 
	}

	function make_folder($recent_folder_path)
	{		
		if(!file_exists($recent_folder_path))
		{
			//create base folder
			//mkdir($recent_folder_path, 0777);
			$oldmask = umask(0);
			mkdir($recent_folder_path, 0777);
			umask($oldmask);
		}
	}

	public function getRepository($directory_id='')
	{
		//dd($directory_id);
	//	echo $directory_id."VV";
		$created_by=Auth::user()->id;
		if($directory_id>0)
		{
			$directory=Directory::where('created_by',$created_by)
					->where('directory_id',$directory_id)
					->get();
		}
		else
		{
			$directory=Directory::where('created_by',$created_by)
					->where('directory_id','0')	
					->get();
		}
					
		$site_path=env('SITE_PATH');
		// return view('partner.list',[
		// 				'logo_details' =>$logo_details,
		// 				'url' =>$url
		// 		   ]);
		//dd($directory->toArray());
		//return view('');
		return view('admin.promotion.list',[
						'directory_list' =>$directory,
						'site_path' =>$site_path,
						'directory_id' =>$directory_id
				   ]);
	}


	public function getRepositoryimage($image_id='')
	{
		//dd("a");
		$directory=Directory::find($image_id);
		//dd($image_id);
		return $directory;
	}

	
}
