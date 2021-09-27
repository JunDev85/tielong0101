<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

use App\Block;
use App\Block_manager;
use App\Business_category;
use App\Maintenance;
use App\Maintenance_matter;
use App\Maintenance_progress;
use App\Quotation_info;
use App\Accounting_info;
use App\Photo_file;
use App\Report_file;
use App\Quotation_file;
use App\Bmcategory_table;
use App\Customer_information;
use App\General_manager;
use App\Department;
use App\District;
use App\District_manager;
use App\Order_reason;
use App\Role;
use App\Shop;
use App\User;
use App\Sub_category;
use DB;
use Validator;

class MaintenanceController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->input('action');
        $business_category_id = $request->input('business_category_id', 0);
        $shop_id = $request->input('shop_id', 0);
        $progress_id = $request->input('progress_id', 0);

        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        $offset = $limit * ($page - 1);

        $qb = Maintenance::with(['shop.business_category', 'orderType', 'progress', 'user'])->whereNotNull('shop_id');

        if ($shop_id != 0) {
            $qb->where('shop_id', $shop_id);
        } else {
            if ($business_category_id != 0) {
                $qb = Maintenance::with(['shop.business_category', 'orderType', 'progress', 'user'])->join('shops', 'shops.shop_id', '=', 'maintenances.shop_id')->join('business_categories', 'business_categories.business_category_id', '=', 'shops.business_category_id')->where('shops.business_category_id', $business_category_id);
            }
        }

        if ($progress_id != 0) {
            $progress_id = explode(',', $progress_id);
            $qb->whereIn('progress_id', $progress_id);
        }

        $total = $qb->count();

        $qb->offset($offset)->limit($limit);

        $maintenances = $qb->orderBy('maintenance_id', 'desc')->take($limit)->get();
        return response(['data' => $maintenances, 'meta' => ['total' => $total]]);
    }

    public function classHistory(Request $request)
    {
        $request->input('action');

        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        $offset = $limit * ($page - 1);

        $qb = Maintenance::with(['shop.business_category', 'orderType', 'progress', 'user'])->whereNotNull('shop_id')->where('sub_category_id', $request->input('sub_category_id'));

        $total = $qb->count();

        $qb->offset($offset)->limit($limit);

        $maintenances = $qb->orderBy('maintenance_id', 'desc')->take($limit)->get();
        return response(['data' => $maintenances, 'meta' => ['total' => $total]]);
    }

    public function shopHistory(Request $request)
    {
        $request->input('action');

        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        $offset = $limit * ($page - 1);

        $qb = Maintenance::with(['shop.business_category', 'orderType', 'progress', 'user'])->whereNotNull('shop_id')->where('shop_id', $request->input('shop_id'));

        $total = $qb->count();

        $qb->offset($offset)->limit($limit);

        $maintenances = $qb->orderBy('maintenance_id', 'desc')->take($limit)->get();
        return response(['data' => $maintenances, 'meta' => ['total' => $total]]);
    }

    public function createProgress(Request $request, $maintenance_id)
    {
        $row = new Maintenance_progress();
        $row->maintenance_id = $maintenance_id;
        $row->progress_id = $request->input('progress_id');
        $row->comment = $request->input('comment');
        $row->faxed_to_client = $request->input('faxed_to_client');
        $row->faxed_to_shop = $request->input('faxed_to_shop');
        $row->entered_by = $request->user()->user_id;
        $row->save();

        $maintenance = Maintenance::find($maintenance_id);
        $maintenance->progress_id = $row->progress_id;
        $maintenance->save();

        // $maintenance_progress = Maintenance_progress::with('entered_by')->where('maintenance_id', $maintenance_id)->orderBy('updated_at', 'desc')->get();
        $maintenance_progress = Maintenance_progress::where('maintenance_id', $maintenance_id)->orderBy('updated_at', 'desc')->get();
        return response($maintenance_progress);
    }

//9.5 tietie add
    public function createQuotation(Request $request, $maintenance_id)
    {
        $row = new Quotation_info();
        $row->maintenance_id = $maintenance_id;
        $row->date = $request->input('date');
        $row->comment = $request->input('comment');
        $row->amount = $request->input('amount');
        $row->photo_files_cnt = $request->input('photo_files_cnt');
        $row->report_files_cnt = $request->input('report_files_cnt');
        $row->quotation_files_cnt = $request->input('quotation_files_cnt');
        $row->editor = $request->input('editor');
        // $row->entered_by = $request->user()->user_id;
        $row->save();

        // $maintenance = Maintenance::find($maintenance_id);
        // $maintenance->progress_id = $row->progress_id;
        // $maintenance->save();

        $quotation_info = Quotation_info::where('maintenance_id', $maintenance_id)->get();
        return response($quotation_info);
    }

    public function createAccounting(Request $request, $maintenance_id)
    {
        $row = new Accounting_info();
        $row->maintenance_id = $maintenance_id;
        $row->accounting_year = $request->input('accounting_year');
        $row->relation_code = $request->input('relation_code');
        $row->relation_name = $request->input('relation_name');
        $row->accounting_amount = $request->input('accounting_amount');
        $row->including_price = $request->input('including_price');
        $row->unincluding_price = $request->input('unincluding_price');
        $row->employee = $request->input('employee');
        $row->editor = $request->input('editor');
        // $row->entered_by = $request->user()->user_id;
        $row->save();

        // $maintenance = Maintenance::find($maintenance_id);
        // $maintenance->progress_id = $row->progress_id;
        // $maintenance->save();

        $accounting_info = Accounting_info::where('maintenance_id', $maintenance_id)->get();
        return response($accounting_info);
    }

    public function uploadReport(Request $request, $maintenance_id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:pdf|max:204800',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if ($request->file('file')) {

            //store file into document folder
            $file = $request->file->store('public/reports');

            //store your file into database
            $reportFile = new Report_file();
            $reportFile->file_path = $file;
            $reportFile->file_name = $request->file('file')->getClientOriginalName();;
            $reportFile->maintenance_id = $maintenance_id;
            $reportFile->save();

            return response()->json([
                "success" => true,
                "message" => "File successfully uploaded",
                "file" => $file
            ]);
        }
    }

    public function uploadPhoto(Request $request, $maintenance_id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:jpg,jpeg,png|max:204800',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if ($request->file('file')) {

            //store file into document folder
            $file = $request->file->store('public/photos');

            //store your file into database
            $reportFile = new Photo_file();
            $reportFile->file_path = $file;
            $reportFile->file_name = $request->file('file')->getClientOriginalName();;
            $reportFile->maintenance_id = $maintenance_id;
            $reportFile->save();

            return response()->json([
                "success" => true,
                "message" => "File successfully uploaded",
                "file" => $file
            ]);
        }
    }

    public function uploadQuotation(Request $request, $maintenance_id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:pdf|max:204800',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if ($request->file('file')) {

            //store file into document folder
            $file = $request->file->store('public/reports');

            //store your file into database
            $reportFile = new Quotation_file();
            $reportFile->file_path = $file;
            $reportFile->file_name = $request->file('file')->getClientOriginalName();;
            $reportFile->maintenance_id = $maintenance_id;
            $reportFile->save();

            return response()->json([
                "success" => true,
                "message" => "File successfully uploaded",
                "file" => $file
            ]);
        }
    }

    public function getPhotoFiles(Request $request, $maintenance_id)
    {
        $files = Photo_file::where('maintenance_id', $maintenance_id)->get();
        return response($files);
    }

    public function customsearch(Request $request, $custom_code)
    {
        $result = Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        ->distinct()
        ->where('customer_code', $custom_code)
        ->get();
        if(Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        ->distinct()
        ->where('customer_code', $custom_code)->exists()) {
            return response($result);
        }
        else return response(0);
        // var_export($result->customer_code); die;
        // echo $result;
        
    }
    

    public function customsearch_again(Request $request)
    {
        $result = Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        ->distinct();

        if($request->input('customer_code')) {
            $result->where('customer_code', $request->input('customer_code'));
        }

        if($request->input('customer_name')) {
            $result->where('customer_name', $request->input('customer_name'));
        }

        if($request->input('customer_tel')) {
            $result->where('TEL', $request->input('customer_tel'));
        }

        if($request->input('customer_alias')) {
            $result->where('customer_alias', $request->input('customer_alias'));
        }

        if($request->input('customer_fax')) {
            $result->where('FAX', $request->input('customer_fax'));
        }

        if($request->input('customergroup_code')) {
            $result->where('customergroup_code', $request->input('customergroup_code'));
        }

        if($request->input('customergroup')) {
            $result->where('customergroup', $request->input('customergroup'));
        }

        $result_again = $result->get();
        if($result_again->exists()) {
            return response($result);
        }
        else return response(0);
        // $result = Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        // ->distinct()
        // ->where('customer_code', $custom_code)
        // ->get();
        // if(Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        // ->distinct()
        // ->where('customer_code', $custom_code)->exists()) {
        //     return response($result);
        // }
        // else return response(0);
        // var_export($result->customer_code); die;
        // echo $result;
        
    }
    

    public function big_middleconnect(Request $request, $category_id)
    {
        $result = Sub_category::select('sub_category_id', 'category_id', 'sub_category_name')
        ->distinct()
        ->where('category_id', $category_id)
        ->get();
        // echo $result;
        return response($result);
    }

    public function selectreason(Request $request, $maintenance_id)
    {
        $maintenance = Maintenance::find($maintenance_id);
        $maintenance->order_reason_id = $request->input('reason'); 

        if($request->input('other')) $maintenance->order_type_other_text = $request->input('other');
        $maintenance->save();

        $order_reason = Order_reason::find($request->input('reason'));       
        // $order_reason['other'] = $request->input('other');
        
        return response($order_reason);  

    }

    public function update_customerid(Request $request, $maintenance_id)
    {
        $id = $request->input('id');
        if($id > 0) {
            $aa = $request->input('customer_code');
            // echo "cdod == >>>> ".$aa.">>>>>>>".$maintenance_id; die;
       
        }
        else {


            $row = new Customer_information();
            $row->customer_code = $request->input('customer_code');
            $row->customer_name = $request->input('customer_name');
            $row->customer_alias = $request->input('customer_alias');
            $row->customergroup = $request->input('customergroup');
            $row->customergroup_code = $request->input('customergroup_code');
            $row->TEL = $request->input('customer_tel');
            $row->FAX = $request->input('customer_fax');
            $row->save();
            // echo "input";
            // die;

            $aa = $request->input('customer_code');
            $maintenance = Maintenance::find($maintenance_id);
            $maintenance->customer_code = $request->input('customer_code');
            $maintenance->save();
        }
        // echo $aa;

        $files = Maintenance::where('maintenance_id', $maintenance_id)->update(['customer_code'=> $aa]);


        $result = Customer_information::select('customer_code', 'customer_name', 'id', 'TEL', 'FAX', 'customer_alias', 'customergroup', 'customergroup_code')
        ->distinct()
        ->where('customer_code', $aa)
        ->get();
        return response($result);    
    }

	public function getImage(Request $request, $maintenance_id)
	{
        $file_name = $request->input('file_name');
        // var_export($file_name); die;
		$image = Storage::disk('s3')->get("zensho-mainte/images/$maintenance_id/$file_name");  
		header('Content-type: image/jpeg');
		echo $image;
        // return response($image);
        // echo "dkdkdkdk//ddkkddkdk";
	}

    public function getReportFiles(Request $request, $maintenance_id)
    {
        $files = Report_file::where('maintenance_id', $maintenance_id)->get();
        return response($files);
    }

    public function getQuotationFiles(Request $request, $maintenance_id)
    {
        $files = Quotation_file::where('maintenance_id', $maintenance_id)->get();
        return response($files);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request)
    {
        $request_all = $request->all();
        $request_keys = array_keys($request_all);
        $request_images = preg_grep('/^image/', $request_keys);
        $request_images_count = count($request_images);

        $images = [];
        for ($i = 1; $i <= $request_images_count; $i++) {
            $image_name = uniqid('tmp_') . "." . $request->file('image_' . $i)->guessExtension();
            $request->file('image_' . $i)->move(public_path('/img/tmp'), $image_name);
            $images[] = $image_name;
        }

        $order_type_id = $request->input('order_type_id');
        if ($order_type_id === '1') {
            $order_type = '修理をしてほしい';
        }
        if ($order_type_id === '2') {
            $order_type = '部品を送ってほしい';
        }
        if ($order_type_id === '3') {
            $order_type = '調査してほしい';
        }
        if ($order_type_id === '4') {
            $order_type = 'その他';
        }

        $request->session()->flash('form', [
            'business_category_id'  => $request->input('business_category_id'),
            'shop_id'               => $request->input('shop_id'),
            'applicant_id'          => $request->input('applicant_id'),
            'equipment'             => $request->input('equipment'),
            'manufacturer'          => $request->input('manufacturer'),
            'model_number'          => $request->input('model_number'),
            'when'                  => $request->input('when'),
            'situation'             => $request->input('situation'),
            'order_type_id'         => $request->input('order_type_id'),
            'order_type'            => $order_type,
            'order_reason_ids'      => $request->input('order_reason_ids'),
            'order_type_other_text' => $request->input('order_type_other_text'),
        ]);
        $request->session()->flash('images', $images);
        return response()->json([
            'url' => url('maintenance/add/confirm'),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($maintenance_id)
    {
        $maintenance = Maintenance::with([
            'shop.business_category',
            'orderType', 'progress',
            'user',

            'maintenanceProgress.entered_by',
            'maintenanceImages',
            'orderReasons',
            'category', 'subCategory',
            'maintenanceMatters.matter_value',
            'maintenanceMatters.matter_option',
            'photoFiles', 'reportFiles', 'quotationFiles', 'quotationInfo', 'accountingInfo'
        ])->find($maintenance_id);


        $cc1 = Shop::select('block_id')->where('shop_id', $maintenance['shop_id'])->get();
        $cc_name = Block::select('block_name')->where('block_id', $cc1[0]['block_id'])->get();
        $cc3 = Block_manager::select('user_id')->where('block_id', $cc1[0]['block_id'])->get();
        $cc_user = User::select('name', 'email')->where('user_id', $cc3[0]['user_id'])->get();
        $maintenance['mail_data3']= $cc_user;
        $maintenance['mail_data33']= $cc_name;


        $bb1 = Block::select('district_id')->where('block_id', $cc1[0]['block_id'])->get();
        $bb_name = District::select('district_name')->where('district_id', $bb1[0]['district_id'])->get();
        $bb2 = District_manager::select('user_id')->where('district_id', $bb1[0]['district_id'])->get();
        $bb_user = User::select('name', 'email')->where('user_id', $bb2[0]['user_id'])->get();


        $maintenance['mail_data2']= $bb_user;
        $maintenance['mail_data22']= $bb_name;

        $aa1 = District::select('department_id')->where('district_id', $bb1[0]['district_id'])->get();
        $aa_name = Department::select('department_name')->where('department_id', $aa1[0]['department_id'])->get();
        $aa2 = General_manager::select('user_id')->where('department_id', $aa1[0]['department_id'])->get();
        $aa_user = User::select('name', 'email')->where('user_id', $aa2[0]['user_id'])->get();

        $maintenance['mail_data1']= $aa_user;
        $maintenance['mail_data11']= $aa_name;



        // $aa1 = User::select('user_id')->where('shop_id',$maintenance['shop_id'])->get();
        // $aa2 = General_manager::select('department_id')->where('user_id', $aa1[0]['user_id'])->get();
        // $aa3 = Department::select('department_name')->where('department_id', $aa2[0]['department_id'])->get();
        // $maintenance['mail_data2'] = $aa3;

        // $bb2 = District_manager::select('district_id')->where('user_id', $aa1[0]['user_id'])->get();
        // $bb3 = District::select('district_name')->where('district_id', $bb2[0]['district_id'])->get();
        // $maintenance['mail_data3']= $bb3;

        // $cc2 = Block_manager::select('block_id')->where('user_id', $aa1[0]['user_id'])->get();
        // $cc3 = Block::select('block_name')->where('block_id', $cc2[0]['block_id'])->get();
        // $maintenance['mail_data1']= $cc3;
        // $quotation = Bmcategory_table::select('big_id','big_name')->distinct()->get();
        // $maintenance['bmcategoryTable_big'] = $quotation; 

        // $quotationm = Bmcategory_table::select('middle_id', 'middle_name')->distinct()->get();
        // $maintenance['bmcategoryTable_middle'] = $quotationm; 

        $quotationcus = Customer_information::select('customer_code', 'customer_name', 'customer_id', 'TEL', 'FAX')
            ->distinct()
            ->where('customer_code', $maintenance['customer_code'])
            ->get();
        
        
        if($quotationcus->isEmpty()){
            $quotationcus[0] = array(
                'customer_code' => '',
                'customer_name' => '',
                'customer_id' => '',
                'TEL' => '',
                'FAX' => '',
            );
        }
        
        $maintenance['customerInformation'] = $quotationcus; 
 
        $customgroup_list = Customer_information::select('customergroup',  'customergroup_code')
            ->distinct()
            ->whereNotNull('customergroup_code')
            ->get();

        if($customgroup_list->isEmpty()){
            $customgroup_list[0] = array(
                'customergroup' => '',
                'customergroup_code' => '',
            );
        }

        $maintenance['customgroup_list'] = $customgroup_list; 

        $order_reason = Order_reason::select('order_reason_id', 'reason')
            ->distinct()
            ->where('order_reason_id', $maintenance['order_reason_id'])
            ->get();

        if($order_reason->isEmpty()){
            $order_reason[0] = array(
                'order_reason_id' => '',
                'reason' => '',
            );
        }

        $maintenance['order_reason'] = $order_reason; 


        // $quotation = DB::table('quotation_files')
        //     ->where('maintenance_id',$maintenance_id)
        //     ->get();
        // $maintenance['quotation_files'] = $quotation; 
        return response($maintenance);
    }

    public function update(Request $request, $maintenance_id)
    {
        // $order                 = $equipment . ' ' . $manufacturer . ':' . $model_number . ' ' . $when . ' ' . $situation . '手配お願いします。';

        $maintenance = Maintenance::find($maintenance_id);
        $maintenance->is_disaster     = $request->input('is_disaster');
        $maintenance->is_emergency    = $request->input('is_emergency');
        $maintenance->category_id     = $request->input('category_id');
        $maintenance->sub_category_id = $request->input('sub_category_id');
        $maintenance->order_type_id   = $request->input('order_type_id');
        $maintenance->remark          = $request->input('remark');
        $maintenance->save();

        $maintenance_matters = $request->input('maintenance_matters', []);
        foreach ($maintenance_matters as $item) {
            $maintenance_matter = null;
            if (empty($item['maintenance_matter_id'])) {
                if (empty($item['matter_option_id']) || empty($item['matter_value_id'])) {
                    continue;
                }
                $maintenance_matter = new Maintenance_matter();
            } else {
                $maintenance_matter = Maintenance_matter::find($item['maintenance_matter_id']);
                if (empty($item['matter_option_id']) || empty($item['matter_value_id'])) {
                    $maintenance_matter->delete();
                    continue;
                }
            }
            $maintenance_matter->maintenance_id  = $maintenance_id;
            $maintenance_matter->matter_option_id  = $item['matter_option_id'];
            $maintenance_matter->matter_value_id  = $item['matter_value_id'];
            $maintenance_matter->save();
        }

        return response([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function error(Request $request)
    {
        $business_category_id = Auth::user()->business_category_id;
        $shop_id              = Auth::user()->shop_id;
        $role                 = Role::find(Auth::user()->role_id)->role_name;
        $block_name           = '';
        if ('BM' === $role) {
            $block_id   = Block_manager::where('user_id', Auth::user()->user_id)->first()->block_id;
            $block_name = Block::find($block_id)->block_name;
        }

        $errorText = 'エラーが発生しました。';
        if ($request->session()->get('errorText')) {
            $errorText = $request->session()->get('errorText');
        }

        return view('maintenance.error', [
            'page_title'        => '新規メンテナンス申請',
            'text'              => $errorText,
            'business_category' => Business_category::find($business_category_id),
            'block_name'        => $block_name,
            'shop'              => Shop::find($shop_id),
            'role'              => $role,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($maintenance_id)
    {
        Maintenance::find($maintenance_id)->delete(); // 論理削除です
        return redirect('/maintenance/' . $maintenance_id . '/delete/completed');
    }


    public function changeProgress(Request $request, $maintenance_id)
    {
        $progress_id = $request->input('progress_id');
        $role = Role::find(Auth::user()->role_id)->role_name;
        if ($role === '管理者') {

            DB::beginTransaction();
            try {
                $maintenance = Maintenance::find($maintenance_id);
                $maintenance->progress_id = $progress_id;
                $maintenance->save();

                $maintenance_progress = new Maintenance_progress;
                $maintenance_progress->maintenance_id = $maintenance_id;
                $maintenance_progress->progress_id    = $progress_id;
                $maintenance_progress->comment        = '';
                $maintenance_progress->entered_by     = Auth::user()->user_id;
                $maintenance_progress->save();

                DB::commit();

                $request->session()->flash('flash_message', 'ID:' . $maintenance_id . 'のステータスを更新しました');
            } catch (\Exception $e) {

                DB::rollback();
                $request->session()->flash('flash_message', $e->getMessage());
                return redirect('maintenance');
            }

            return redirect('maintenance');
        }
    }
}
