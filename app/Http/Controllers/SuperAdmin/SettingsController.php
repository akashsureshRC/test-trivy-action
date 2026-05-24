<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Mail\TestMail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        $file_type = config('files_types');
        $timezones = config('timezones');

        return view('super-admin.settings.index', compact('settings', 'file_type', 'timezones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('setting manage')) {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            if (!isset($post['site_rtl'])) {
                $post['site_rtl'] = 'off';
            }
            if (!isset($post['signup'])) {
                $post['signup'] = 'off';
            }
            if (!isset($post['email_verification'])) {
                $post['email_verification'] = 'off';
            }
            if (!isset($post['site_transparent'])) {
                $post['site_transparent'] = 'off';
            }
            if (!isset($post['cust_darklayout'])) {
                $post['cust_darklayout'] = 'off';
            }
            if (isset($request->color) && $request->color_flag == 'false') {
                $post['color'] = $request->color;
            } else {
                $post['color'] = $request->custom_color;
            }

             if (!isset($post['category_wise_sidemenu'])) {
                $post['category_wise_sidemenu'] = 'off';
            }

            $admin_settings = getAdminAllSetting();
            
            // Upload logo_dark to S3
            if ($request->hasFile('logo_dark')) {
                $upload = uploadToS3($request->file('logo_dark'), S3_FOLDER_LOGOS);
                if ($upload['flag'] == 1) {
                    // Store only filename
                    $post['logo_dark'] = $upload['filename'];

                    // Delete old logo from S3
                    $old_logo_dark = isset($admin_settings['logo_dark']) ? $admin_settings['logo_dark'] : null;
                    if (!empty($old_logo_dark) && strpos($old_logo_dark, 'uploads/') === false) {
                        deleteFromS3($old_logo_dark, S3_FOLDER_LOGOS);
                    }
                } else {
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }
            
            // Upload logo_light to S3
            if ($request->hasFile('logo_light')) {
                $upload = uploadToS3($request->file('logo_light'), S3_FOLDER_LOGOS);
                if ($upload['flag'] == 1) {
                    // Store only filename
                    $post['logo_light'] = $upload['filename'];

                    // Delete old logo from S3
                    $old_logo_light = isset($admin_settings['logo_light']) ? $admin_settings['logo_light'] : null;
                    if (!empty($old_logo_light) && strpos($old_logo_light, 'uploads/') === false) {
                        deleteFromS3($old_logo_light, S3_FOLDER_LOGOS);
                    }
                } else {
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }
            
            // Upload favicon to S3
            if ($request->hasFile('favicon')) {
                $upload = uploadToS3($request->file('favicon'), S3_FOLDER_LOGOS);
                if ($upload['flag'] == 1) {
                    // Store only filename
                    $post['favicon'] = $upload['filename'];

                    // Delete old favicon from S3
                    $old_favicon = isset($admin_settings['favicon']) ? $admin_settings['favicon'] : null;
                    if (!empty($old_favicon) && strpos($old_favicon, 'uploads/') === false) {
                        deleteFromS3($old_favicon, S3_FOLDER_LOGOS);
                    }
                } else {
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
            adminSettingCacheForget();
            companySettingCacheForget();
            sideMenuCacheForget();

            // When Default Language changes, apply to all super admin users
            if (isset($post['defult_language']) && !empty($post['defult_language'])) {
                $newLang = $post['defult_language'];

                // Update all super admin users
                User::where('type', 'super admin')
                    ->update(['lang' => $newLang]);

                // Update current session so the change is visible immediately
                session(['locale' => $newLang]);
            }

            return redirect()->back()->with('success', __('Setting save sucessfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function SystemStore(Request $request)
    {
        if (Auth::user()->isAbleTo('setting manage')) {
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
            adminSettingCacheForget();
            companySettingCacheForget();
            return redirect()->back()->with('success', 'Setting save sucessfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function CookieSetting(Request $request)
    {
        if ($request->has('enable_cookie')) {
            $validator = \Validator::make($request->all(), [
                'cookie_title' => 'required',
                'cookie_description' => 'required',
                'strictly_cookie_title' => 'required',
                'strictly_cookie_description' => 'required',
                'more_information_description' => 'required',
                'contactus_url' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
        }


        if ($request->has('enable_cookie')) {
            $post = $request->all();
            unset($post['_token'], $post['_method']);

            $post['cookie_logging'] = isset($request->cookie_logging) ? $request->cookie_logging : 'off';
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
        } else {
            // Define the data to be updated or inserted
            $data = [
                'key' => 'enable_cookie',
                'workspace' => getActiveWorkspace(),
                'created_by' => creatorId(),
            ];

            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert($data, ['value' => 'off']);
        }
        // Settings Cache forget
        adminSettingCacheForget();
        companySettingCacheForget();
        return redirect()->back()->with('success', 'Cookie setting save successfully.');
    }

    public function CookieConsent(Request $request)
    {
        if (adminSetting('enable_cookie') == "on" &&  adminSetting('cookie_logging') == "on") {
            try {

                $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                // Generate new CSV line
                $browser_name = $whichbrowser->browser->name ?? null;
                $os_name = $whichbrowser->os->name ?? null;
                $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                $device_type = getDeviceType($_SERVER['HTTP_USER_AGENT']);

                $ip = $_SERVER['REMOTE_ADDR'];

                $response = Http::timeout(5)->get('https://ip-api.com/json/' . $ip);
                $query = $response->successful() ? $response->json() : null;

                if (is_array($query) && (($query['status'] ?? null) === 'success')) {
                    $date = (new \DateTime())->format('Y-m-d');
                    $time = (new \DateTime())->format('H:i:s') . ' UTC';


                    $new_line = implode(',', [$ip, $date, $time, implode('-', $request['cookie']), $device_type, $browser_language, $browser_name, $os_name, isset($query) ? $query['country'] : '', isset($query) ? $query['region'] : '', isset($query) ? $query['regionName'] : '', isset($query) ? $query['city'] : '', isset($query) ? $query['zip'] : '', isset($query) ? $query['lat'] : '', isset($query) ? $query['lon'] : '']);
                    if (!checkFile('/uploads/sample/cookie_data.csv')) {
                        $first_line = 'IP,Date,Time,Accepted-cookies,Device type,Browser anguage,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                        file_put_contents(base_path() . '/uploads/sample/cookie_data.csv', $first_line . PHP_EOL, FILE_APPEND | LOCK_EX);
                    }
                    file_put_contents(base_path() . '/uploads/sample/cookie_data.csv', $new_line . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            } catch (\Throwable $th) {
                return response()->json('error');
            }
            return response()->json('success');
        }
        return response()->json('error');
    }

    public function seoSetting(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'meta_title' => 'required|string',
                'meta_keywords' => 'required|string',
                'meta_description' => 'required|string',
                'meta_image' => 'mimes:jpeg,jpg,png,gif',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('meta_image')) {
            $filenameWithExt = $request->file('meta_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('meta_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = uploadFile($request, 'meta_image', $fileNameToStore, 'meta');

            if ($uplaod['flag'] == 1) {
                // old img delete
                $settings = getAdminAllSetting();
                if ((!empty($settings['meta_image'])) && strpos($settings['meta_image'], 'meta_image.png') == false && checkFile($settings['meta_image'])) {
                    deleteFile($settings['meta_image']);
                }
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }

        try {
            $post = $request->all();
            unset($post['_token'], $post['_method']);
            if ((isset($uplaod)) && ($uplaod['flag'] == 1) && (!empty($uplaod['url']))) {
                $post['meta_image'] = $uplaod['url'];
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
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        // Settings Cache forget
        adminSettingCacheForget();
        companySettingCacheForget();
        return redirect()->back()->with('success', __('SEO setting successfully updated.'));
    }

}
