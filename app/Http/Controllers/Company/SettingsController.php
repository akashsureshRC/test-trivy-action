<?php

namespace App\Http\Controllers\Company;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Country;
use App\Models\Hrm\Sic7Code;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        $timezones = config('timezones');
        $activatedModules = activatedModule();
        $countries = Country::where('status', 'Active')->pluck('name', 'id');
        $sic7Categories = Sic7Code::getCategories();
        return view('company.settings.index',compact('settings','timezones', 'countries', 'sic7Categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request){
        if(Auth::user()->isAbleTo('setting manage'))
        {
            $post = $request->all();
            $company_settings = getCompanyAllSetting();

            unset($post['_token']);
            unset($post['_method']);

            if(!isset($post['site_rtl'])){
                $post['site_rtl'] = 'off';
            }
            if(!isset($post['site_transparent'])){
                $post['site_transparent'] = 'off';
            }
            if(!isset($post['cust_darklayout'])){
                $post['cust_darklayout'] = 'off';
            }
            if(isset($request->color) && $request->color_flag == 'false')
            {
                $post['color'] = $request->color;
            }
            else
            {
                $post['color'] = $request->custom_color;
            }

              if (!isset($post['category_wise_sidemenu'])) {
                $post['category_wise_sidemenu'] = 'off';
            }

            // Upload logo_dark to S3
            if($request->hasFile('logo_dark'))
            {
                $upload = uploadToS3($request->file('logo_dark'), S3_FOLDER_LOGOS);
                if($upload['flag'] == 1)
                {
                    // Store only filename
                    $post['logo_dark'] = $upload['filename'];

                    // Delete old logo from S3
                    $old_logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : '';
                    if(!empty($old_logo_dark) && strpos($old_logo_dark, 'uploads/') === false)
                    {
                        deleteFromS3($old_logo_dark, S3_FOLDER_LOGOS);
                    }
                }else{
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }
            
            // Upload logo_light to S3
            if($request->hasFile('logo_light'))
            {
                $upload = uploadToS3($request->file('logo_light'), S3_FOLDER_LOGOS);
                if($upload['flag'] == 1)
                {
                    // Store only filename
                    $post['logo_light'] = $upload['filename'];

                    // Delete old logo from S3
                    $old_logo_light = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : '';
                    if(!empty($old_logo_light) && strpos($old_logo_light, 'uploads/') === false)
                    {
                        deleteFromS3($old_logo_light, S3_FOLDER_LOGOS);
                    }
                }else{
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }
            
            // Upload favicon to S3
            if($request->hasFile('favicon'))
            {
                $upload = uploadToS3($request->file('favicon'), S3_FOLDER_LOGOS);
                if($upload['flag'] == 1)
                {
                    // Store only filename
                    $post['favicon'] = $upload['filename'];

                    // Delete old favicon from S3
                    $old_favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : '';
                    if(!empty($old_favicon) && strpos($old_favicon, 'uploads/') === false)
                    {
                        deleteFromS3($old_favicon, S3_FOLDER_LOGOS);
                    }
                }else{
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkspace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            companySettingCacheForget();
            sideMenuCacheForget();

            // When Default Language changes, apply to all users in this workspace
            if (isset($post['defult_language']) && !empty($post['defult_language'])) {
                $newLang = $post['defult_language'];
                $workspace = getActiveWorkspace();
                $companyId = creatorId();

                // Update all users belonging to this company/workspace
                User::where(function ($query) use ($companyId) {
                    $query->where('id', $companyId)
                          ->orWhere('created_by', $companyId);
                })->where('type', '!=', 'super admin')
                  ->update(['lang' => $newLang]);

                // Update current session so the change is visible immediately
                session(['locale' => $newLang]);
            }

            return redirect()->back()->with('success', __('Setting save sucessfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function SystemStore(Request $request)
    {
        if(Auth::user()->isAbleTo('setting manage'))
        {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkspace(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
             // Settings Cache forget
             companySettingCacheForget();
            return redirect()->back()->with('success','Setting save sucessfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function CompanySettingStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'company_name' => 'required',
            'company_address' => 'required',
            'company_city' => 'required',
            'company_state' => 'required',
            'company_zipcode' => 'required',
            'company_country' => 'required',
            'company_telephone' => 'required',
            'company_email' => 'required|email',
            'company_email_from_name' => 'required|string|max:255',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        else
        {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            $post['sdl_number'] = !empty($request->sdl_number) ? $request->sdl_number : '';
            $post['tax_number'] = !empty($request->tax_number) ? $request->tax_number : '';
            $post['uif_number'] = !empty($request->uif_number) ? $request->uif_number : '';

            // if (isset($request->sdl_tax_uif_number_switch) && $request->sdl_tax_uif_number_switch == 'on') {
            //     $post['sdl_tax_uif_number_switch'] = 'on';


            //     $taxTypes = [];


            //     if (!empty($request->sdl_number)) {
            //         $taxTypes[] = 'sdl';
            //     }
            //     if (!empty($request->tax_number)) {
            //         $taxTypes[] = 'tax';
            //     }
            //     if (!empty($request->uif_number)) {
            //         $taxTypes[] = 'uif';
            //     }


            //     $post['tax_type'] = !empty($taxTypes) ? implode(',', $taxTypes) : '';


            //     $post['sdl_number'] = !empty($request->sdl_number) ? $request->sdl_number : '';
            //     $post['tax_number'] = !empty($request->tax_number) ? $request->tax_number : '';
            //     $post['uif_number'] = !empty($request->uif_number) ? $request->uif_number : '';
            // } else {

            //     $post['sdl_tax_uif_number_switch'] = 'off';
            //     $post['tax_type'] = '';
            //     $post['sdl_number'] = !empty($request->sdl_number) ? $request->sdl_number : '';
            //     $post['tax_number'] = !empty($request->tax_number) ? $request->tax_number : '';
            //     $post['uif_number'] = !empty($request->uif_number) ? $request->uif_number : '';
            // }

            foreach ($post as $key => $value) {

                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkspace(),
                    'created_by' => creatorId(),
                ];


                Setting::updateOrInsert($data, ['value' => $value]);
            }

            companySettingCacheForget();
            return redirect()->back()->with('success','Company setting save sucessfully.');
        }

    }

    public function getSic7CodesByCategory(Request $request)
    {
        $category = $request->get('category');
        
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category is required']);
        }
        $codes = Sic7Code::byCategory($category)
                    ->orderBy('code')
                    ->get()
                    ->map(function ($code) {
                        return [
                            'id' => $code->id,
                            'code' => $code->code,
                            'description' => $code->description,
                            'display_text' => $code->code . ' - ' . $code->description
                        ];
                    });

        return response()->json(['success' => true, 'codes' => $codes]);
    }

    public function getProvinces(string $id)
    {
        $countryId = $id;
        $provinces = \App\Models\Hrm\Province::where('country_id', $countryId)->where('status', 'Active')->pluck('name', 'id');
        return response()->json($provinces);
    }
}
