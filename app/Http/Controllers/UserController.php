<?php

namespace App\Http\Controllers;

use App\Events\CreateUser;
use App\Events\DefaultData;
use App\Events\DestroyUser;
use App\Events\EditProfileUser;
use App\Events\UpdateUser;
use App\Models\EmailTemplate;
use App\Models\LoginDetail;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Registered;
use Lab404\Impersonate\Impersonate;

use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Master admin and super admin have implicit access
        if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin' || Auth::user()->isAbleTo('user manage'))
        {
            $status = $request->get('status');

            if(Auth::user()->type == 'super admin')
            {
                $roles =[];
                $users = User::where('type','company');
            }
            elseif(Auth::user()->type == 'master_admin')
            {
                // Master Admin - show only assigned companies
                $roles = [];
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')
                    ->toArray();
                $users = User::whereIn('id', $assignedCompanyIds)->where('type', 'company');
            }
            else
            {
                $roles = Role::where('created_by',\Auth::user()->id)->pluck('name','id');
                if(Auth::user()->isAbleTo('workspace manage'))
                {
                    $users = User::where('created_by',creatorId())->where('workspace_id',getActiveWorkspace());
                }
                else
                {
                    $users = User::where('created_by',creatorId());
                }

                if($request->role)
                {
                    $role = Role::find($request->role);
                    $users = $users->where('type',$role->name);
                }
            }

            if($request->filled('name'))
            {
                $users = $users->where('name', 'like', '%' . $request->name . '%');
            }

            if($status === 'active')
            {
                $users = $users->where('is_disable', 1);
            }
            elseif($status === 'suspended')
            {
                $users = $users->where('is_disable', 0);
            }

            // Plan type filter
            if(in_array(Auth::user()->type, ['super admin', 'master_admin']) && $request->filled('plan_type'))
            {
                if($request->plan_type === 'trial')
                {
                    $users = $users->onTrial();
                }
                elseif($request->plan_type === 'paid')
                {
                    $users = $users->paid();
                }
            }

            $users = $users->get();

            return view('users.index',compact('users','roles'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function List(Request $request)
    {
        // Master admin and super admin have implicit access
        if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin' || Auth::user()->isAbleTo('user manage'))
        {
            $status = $request->get('status');
            $perPage = $request->get('per_page', 10);

            if(Auth::user()->type == 'super admin')
            {
                $roles =[];
                $users = User::where('type','company');
            }
            elseif(Auth::user()->type == 'master_admin')
            {
                // Master Admin - show only assigned companies
                $roles = [];
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')
                    ->toArray();
                $users = User::whereIn('id', $assignedCompanyIds)->where('type', 'company');
            }
            else
            {
                $roles = Role::where('created_by',\Auth::user()->id)->pluck('name','id');
                if(Auth::user()->isAbleTo('workspace manage'))
                {
                    $users = User::where('created_by',creatorId())->where('workspace_id',getActiveWorkspace());

                }
                else
                {
                    $users = User::where('created_by',creatorId());
                }

                if($request->role)
                {
                    $role = Role::find($request->role);
                    $users = $users->where('type',$role->name);
                }
            }

            if($request->filled('name'))
            {
                $users = $users->where('name', 'like', '%' . $request->name . '%');
            }

            if($status === 'active')
            {
                $users = $users->where('is_disable', 1);
            }
            elseif($status === 'suspended')
            {
                $users = $users->where('is_disable', 0);
            }

            // Plan type filter (admin/master_admin only)
            if(in_array(Auth::user()->type, ['super admin', 'master_admin']) && $request->filled('plan_type'))
            {
                if($request->plan_type === 'trial')
                {
                    $users = $users->onTrial();
                }
                elseif($request->plan_type === 'paid')
                {
                    $users = $users->paid();
                }
            }

            $users = $users->paginate($perPage)->appends($request->query());

            return view('users.list',compact('users','roles'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->isAbleTo('user create'))
        {
            // Company admins can only create payroll_officer users - no role selection needed
            return view('users.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show full page create customer form (super admin)
     */
    public function createPage()
    {
        if(Auth::user()->isAbleTo('user create'))
        {
            return view('users.create-page');
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('user create'))
        {
            if(Auth::user()->type != 'super admin'){
                $canUse=  planCheck('User',Auth::user()->id);
                if($canUse == false)
                {
                    if($request->ajax()) {
                        return response()->json(['error' => 'You have maxed out the total number of User allowed on your current plan'], 422);
                    }
                    return redirect()->back()->with('error','You have maxed out the total number of User allowed on your current plan');
                }
            }

            if(Auth::user()->type == 'super admin')
            {
                $validatorArray = [
                    //'name' => 'required|max:120',
                    'name' => 'required|max:120|unique:users,name',
                    'email' => ['required',
                                    Rule::unique('users')->where(function ($query) {
                                    return $query->where('created_by', creatorId());
                                })
                    ],
                ];
            }else{
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => ['required',
                                    Rule::unique('users')->where(function ($query) {
                                    return $query->where('created_by', creatorId())->where('workspace_id', getActiveWorkspace());
                                })
                    ],
                ];
            }

            $validator = Validator::make(
                $request->all(), $validatorArray
            );

            if($validator->fails())
            {
                if($request->ajax()) {
                    return response()->json(['error' => $validator->errors()->first()], 422);
                }
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $user['is_enable_login']       = 0;
            if(!empty($request->password_switch) && $request->password_switch == 'on')
            {
                $user['is_enable_login']   = 1;
                $validator = Validator::make(
                    $request->all(), ['password' => 'required|min:6']
                );

                if($validator->fails())
                {
                    if($request->ajax()) {
                        return response()->json(['error' => $validator->errors()->first()], 422);
                    }
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            if($request->input('mobile_no')){
                $validator = Validator::make(
                    $request->all(), ['mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',]
                );
                if($validator->fails())
                {
                    if($request->ajax()) {
                        return response()->json(['error' => $validator->errors()->first()], 422);
                    }
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            if(Auth::user()->type == 'super admin')
            {
                $roles = Role::where('name','company')->first();
            }
            else
            {
                // Company admins can only create payroll_officer users
                $roles = Role::where('name','payroll_officer')->where('created_by', creatorId())->first();
                if(empty($roles)) {
                    // Fallback: create the payroll_officer role if it doesn't exist
                    Auth::user()->MakeRole();
                    $roles = Role::where('name','payroll_officer')->where('created_by', creatorId())->first();
                }
            }
            $company_settings = getCompanyAllSetting();

            $userpassword               = $request->input('password');
            $user['name']               = $request->input('name');
            $user['email']              = $request->input('email');
            $user['mobile_no']          = $request->input('mobile_no');
            $user['password']           = !empty($userpassword) ? \Hash::make($userpassword) : null;
            $user['lang']               = !empty($company_settings['defult_language']) ? $company_settings['defult_language'] : 'en';
            $user['created_by']         = creatorId();
            $user['active_workspace']   = getActiveWorkspace();
            $user = User::create($user);
            $user->forceFill([
                'type' => $roles->name,
                'workspace_id' => getActiveWorkspace(),
            ])->save();
            if(Auth::user()->type == 'super admin')
            {
                $company = User::find($user->id);

                 // create  WorkSpace
                $workspace = new WorkSpace();
                $workspace->name       = !empty($request->workSpace_name) ? $request->workSpace_name : $request->name;
                $workspace->created_by = $company->id;
                $workspace->save();

                $company->active_workspace = $workspace->id;
                $company->workspace_id = $workspace->id;
                $company->save();

                // comapny setting
                User::CompanySetting($company->id);

                //  create role
                $user->MakeRole();

                // Subscription system removed - all users have unlimited access

                $role_r = Role::where('name','company')->first();
            }
            else
            {
                $role_r = Role::find($roles->id);
            }

            $user->addRole($role_r);
            event(new CreateUser($user,$request));

            setConfigEmail(Auth::user()->id);
            if ( adminSetting('email_verification') == 'on')
            {
                try {
                    //code...
                    event(new Registered($user));
                } catch (\Throwable $th) {

                }
            }
            else
            {
                $user_data = User::find($user->id);
                $user_data->email_verified_at = date('Y-m-d h:i:s');
                $user_data->save();
            }


            //Email notification
            // Check both admin_setting (for Super Admin) and company_settings (for Company Admin)
            $emailNotificationEnabled = false;
            if (Auth::user()->type == 'super admin') {
                $createUserSetting = adminSetting('Create User');
                $emailNotificationEnabled = $createUserSetting == 'on' || $createUserSetting == '1' || $createUserSetting === true;
                \Log::info('Super Admin Create User email check', [
                    'createUserSetting' => $createUserSetting,
                    'emailNotificationEnabled' => $emailNotificationEnabled,
                    'is_enable_login' => $user->is_enable_login
                ]);
            } else {
                $emailNotificationEnabled = !empty($company_settings['Create User']) && $company_settings['Create User'] == true;
            }

            if($emailNotificationEnabled && $user->is_enable_login == 1)
            {
                $uArr = [
                    'email'=>$request->input('email'),
                    'password'=> $request->input('password'),
                    'company_name'=>$request->input('name'),
                ];
                
                // Pass Super Admin's user_id (1) to use Super Admin's email configuration
                // For Company Admin, pass their own id and workspace
                if (Auth::user()->type == 'super admin') {
                    $resp = EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr, 1, null);
                } else {
                    $resp = EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr, creatorId(), getActiveWorkspace());
                }
                
                \Log::info('Create User email result', ['resp' => $resp, 'user_email' => $user->email]);
                
                if($request->ajax()) {
                    // Determine redirect route based on referrer
                    $view = $request->get('view', null);
                    if (!$view && $request->headers->get('referer')) {
                        $referrer = $request->headers->get('referer');
                        if (strpos($referrer, 'users/list/view') !== false) {
                            $view = 'list';
                        }
                    }
                    $route = $view === 'list' ? 'users.list.view' : 'users.index';
                    
                    return response()->json([
                        'success' => __('Customer successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''),
                        'redirect' => route($route)
                    ]);
                }
                
                // Determine redirect route based on referrer or view parameter
                $view = $request->get('view', null);
                if (!$view && $request->headers->get('referer')) {
                    $referrer = $request->headers->get('referer');
                    if (strpos($referrer, 'users/list/view') !== false) {
                        $view = 'list';
                    }
                }
                $route = $view === 'list' ? 'users.list.view' : 'users.index';
                
                return redirect()->route($route)->with('success', __('Customer successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            if($request->ajax()) {
                // Determine redirect route based on referrer
                $view = request()->get('view', null);
                if (!$view && request()->headers->get('referer')) {
                    $referrer = request()->headers->get('referer');
                    if (strpos($referrer, 'users/list/view') !== false) {
                        $view = 'list';
                    }
                }
                $route = $view === 'list' ? 'users.list.view' : 'users.index';
                
                return response()->json([
                    'success' => __('Customer successfully created.'),
                    'redirect' => route($route)
                ]);
            }
            
            // Determine redirect route based on referrer or view parameter
            $view = $request->get('view', null);
            if (!$view && $request->headers->get('referer')) {
                $referrer = $request->headers->get('referer');
                if (strpos($referrer, 'users/list/view') !== false) {
                    $view = 'list';
                }
            }
            $route = $view === 'list' ? 'users.list.view' : 'users.index';
            
            return redirect()->route($route)->with('success', __('Customer successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->isAbleTo('user edit'))
        {
            $user = User::find($id);

            // Verify the target user belongs to the current admin's scope
            if (!$user) {
                return response()->json(['error' => __('User not found.')], 404);
            }
            if (Auth::user()->type === 'super admin') {
                // Super admin can edit any user
            } elseif (Auth::user()->type === 'master_admin') {
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')->toArray();
                if ($user->type === 'company' && !in_array($user->id, $assignedCompanyIds)) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                } elseif (!in_array($user->type, ['company']) && $user->created_by !== Auth::user()->id) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            } else {
                // Regular admins can only edit users they created in their workspace
                if ($user->created_by !== creatorId() || $user->workspace_id !== getActiveWorkspace()) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            }

            $roles = Role::where('created_by',\Auth::user()->id)->pluck('name','id');
            return view('users.edit',compact('user','roles'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->isAbleTo('user edit'))
        {
            if(Auth::user()->type == 'super admin')
            {
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => ['required',
                                    Rule::unique('users')->where(function ($query)  use ($id) {
                                        return $query->whereNotIn('id',[$id])->where('created_by', creatorId());
                                    })
                    ],
                ];
            }else{
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => ['required',
                                    Rule::unique('users')->where(function ($query)  use ($id) {
                                        return $query->whereNotIn('id', [$id])->where('created_by', creatorId())->where('workspace_id', getActiveWorkspace());
                                    })
                    ],
                ];
            }

            $validator = Validator::make(
                $request->all(), $validatorArray
            );
            if($validator->fails())
            {
                if($request->ajax()) {
                    return response()->json(['error' => $validator->errors()->first()], 422);
                }
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            if($request->input('mobile_no')){
                $validator = Validator::make(
                    $request->all(), ['mobile_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',]
                );
                if($validator->fails())
                {
                    if($request->ajax()) {
                        return response()->json(['error' => $validator->errors()->first()], 422);
                    }
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            $user = User::find($id);
            if(!empty($user))
            {
                // Verify the target user belongs to the current admin's scope
                if (Auth::user()->type === 'super admin') {
                    // Super admin can update any user
                } elseif (Auth::user()->type === 'master_admin') {
                    $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                        ->pluck('company_id')->toArray();
                    if ($user->type === 'company' && !in_array($user->id, $assignedCompanyIds)) {
                        return $request->ajax()
                            ? response()->json(['error' => __('Permission denied.')], 403)
                            : redirect()->back()->with('error', __('Permission denied.'));
                    }
                } else {
                    if ($user->created_by !== creatorId() || $user->workspace_id !== getActiveWorkspace()) {
                        return $request->ajax()
                            ? response()->json(['error' => __('Permission denied.')], 403)
                            : redirect()->back()->with('error', __('Permission denied.'));
                    }
                }

                if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin')
                {
                    $role = Role::where('name','company')->first();
                }
                else
                {
                    $role = Role::where('name', $user->type)->where('created_by', creatorId())->first();
                }
                $user->name         = $request->name;
                $user->email        = $request->email;
                if(!empty($role))
                {
                    $user->type = $role->name;
                }
                if(Auth::user()->type !== 'master_admin' && $request->has('mobile_no'))
                {
                    $user->mobile_no = $request->mobile_no;
                }
                $user->save();
                if(Auth::user()->type != 'super admin' && Auth::user()->type != 'master_admin' && !empty($role))
                {
                    $user->roles()->sync([$role->id]);
                }
                event(new UpdateUser($user,$request));

                if($request->ajax()) {
                    // Determine redirect route based on referrer
                    $view = $request->get('view', null);
                    if (!$view && $request->headers->get('referer')) {
                        $referrer = $request->headers->get('referer');
                        if (strpos($referrer, 'users/list/view') !== false) {
                            $view = 'list';
                        }
                    }
                    $route = $view === 'list' ? 'users.list.view' : 'users.index';
                    
                    return response()->json([
                        'success' => __('Customer successfully updated.'),
                        'redirect' => route($route)
                    ]);
                }

                // Determine redirect route based on referrer or view parameter
                $view = $request->get('view', null);
                if (!$view && $request->headers->get('referer')) {
                    $referrer = $request->headers->get('referer');
                    if (strpos($referrer, 'users/list/view') !== false) {
                        $view = 'list';
                    }
                }
                $route = $view === 'list' ? 'users.list.view' : 'users.index';

                return redirect()->route($route)->with(
                    'success', 'Customer successfully updated.'
                );
            }
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->isAbleTo('user delete'))
        {
            $user = User::findOrFail($id);

             // first parameter user
             event(new DestroyUser($user));

            try
            {
                // get all table
                $tables_in_db = \DB::select('SHOW TABLES');
                $db = "Tables_in_". config('database.connections.mysql.database');
                foreach($tables_in_db as $table)
                {
                    $tableName = $table->{$db};
                    if (Schema::hasColumn($tableName, 'created_by'))
                    {
                        \DB::table($tableName)->where('created_by', $user->id)->delete();
                    }
                }
                $user->delete();
            }
            catch (\Exception $e)
            {
                \Log::error('User deletion failed for user ID ' . $id . ': ' . $e->getMessage());
                return redirect()->back()->with('error', __('User cannot be deleted.') . ' ' . $e->getMessage());
            }

            // Determine redirect route based on referrer
            $view = request()->get('view', null);
            if (!$view && request()->headers->get('referer')) {
                $referrer = request()->headers->get('referer');
                if (strpos($referrer, 'users/list/view') !== false) {
                    $view = 'list';
                }
            }
            $route = $view === 'list' ? 'users.list.view' : 'users.index';

            return redirect()->route($route)->with('success', __('User successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function profile()
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            $userDetail = \Auth::user();

            return view('users.profile')->with('userDetail', $userDetail);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function editprofile(Request $request)
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            $userDetail = \Auth::user();
            $user = User::findOrFail($userDetail['id']);

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:120',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) use ($user) {
                            return $query->whereNotIn('id', [$user->id])->where('created_by', $user->created_by)->where('workspace_id', $user->workspace_id);
                        })
                    ],
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Handle avatar upload to S3
            if ($request->hasFile('profile')) {
                $upload = uploadToS3($request->file('profile'), S3_FOLDER_AVATARS);
                
                if ($upload['flag'] == 1) {
                    // Delete old avatar from S3 if it exists and is not the default
                    $oldAvatar = $userDetail['avatar'];
                    if (!empty($oldAvatar) && strpos($oldAvatar, 'avatar.png') === false) {
                        deleteFromS3($oldAvatar, S3_FOLDER_AVATARS);
                    }
                    
                    // Store only the filename
                    $user->avatar = $upload['filename'];
                } else {
                    return redirect()->back()->with('error', $upload['msg']);
                }
            }

            $user->name = $request['name'];
            $user->email = $request['email'];
            $user->save();
            
            // Trigger events
            event(new EditProfileUser($request, $user));

            return redirect()->back()->with(
                'success',
                'Profile successfully updated.'
            );
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updatePassword(Request $request)
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            if (\Auth::Check()) {
                $request->validate(
                    [
                        'current_password' => 'required',
                        'new_password' => 'required|min:6',
                        'confirm_password' => 'required|same:new_password',
                    ]
                );
                $objUser          = Auth::user();
                $request_data     = $request->All();
                $current_password = $objUser->password;
                if (Hash::check($request_data['current_password'], $current_password)) {
                    $user_id            = Auth::User()->id;
                    $obj_user           = User::find($user_id);
                    $obj_user->password = Hash::make($request_data['new_password']);;
                    $obj_user->save();

                    return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
                } else {
                    return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
                }
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function ajaxUserList(Request $request){

        if ($request->ajax()) {
            $usersQuery = User::query();

            if(!empty($request->get('name'))){
                $usersQuery->where('id', $request->get('name'));
            }

            $data = $usersQuery->select('*');

            return Datatables::of($data)
                    ->addIndexColumn()

                    ->addColumn('action', function($row){

                           $btn = '<a href="javascript:void(0)" class="edit-icon bg-info"><i class="fas fa-eye"></a>';

                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

        }
    }
    public function UserPassword($id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            $eId        = \Crypt::decrypt($id);
            $user = User::find($eId);
            return view('users.reset',compact('user'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }
    public function UserPasswordReset(Request $request, $id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'password' => 'required|confirmed|same:password_confirmation|min:6',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                
                if($request->ajax()) {
                    return response()->json(['error' => $messages->first()], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }
            $user                 = User::where('id', $id)->first();

            // Verify the target user belongs to the current admin's scope
            if (!$user) {
                return $request->ajax()
                    ? response()->json(['error' => __('User not found.')], 404)
                    : redirect()->back()->with('error', __('User not found.'));
            }
            if (Auth::user()->type === 'super admin') {
                // Super admin can reset any user's password
            } elseif (Auth::user()->type === 'master_admin') {
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')->toArray();
                if ($user->type === 'company' && !in_array($user->id, $assignedCompanyIds)) {
                    return $request->ajax()
                        ? response()->json(['error' => __('Permission denied.')], 403)
                        : redirect()->back()->with('error', __('Permission denied.'));
                }
            } else {
                if ($user->created_by !== creatorId() || $user->workspace_id !== getActiveWorkspace()) {
                    return $request->ajax()
                        ? response()->json(['error' => __('Permission denied.')], 403)
                        : redirect()->back()->with('error', __('Permission denied.'));
                }
            }

            if(isset($request->login_enable))
            {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'is_enable_login' => 1,
                ])->save();
            }
            else
            {
                $user->forceFill([
                                    'password' => Hash::make($request->password),
                                ])->save();
            }

            if($request->ajax()) {
                // Determine redirect route based on referrer
                $view = $request->get('view', null);
                if (!$view && $request->headers->get('referer')) {
                    $referrer = $request->headers->get('referer');
                    if (strpos($referrer, 'users/list/view') !== false) {
                        $view = 'list';
                    }
                }
                $route = $view === 'list' ? 'users.list.view' : 'users.index';
                
                return response()->json([
                    'success' => __('User Password successfully updated.'),
                    'redirect' => route($route)
                ]);
            }

            $view = request()->get('view', 'grid');
            $route = $view === 'list' ? 'users.list.view' : 'users.index';
            
            return redirect()->route($route)->with(
                'success', 'User Password successfully updated.'
            );
        }
        else
        {
            if($request->ajax()) {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function LoginManage($id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            $eId        = \Crypt::decrypt($id);
            $user = User::find($eId);
            $view = request()->get('view', 'grid');
            $route = $view === 'list' ? 'users.list.view' : 'users.index';
            
            if($user->is_enable_login == 1)
            {
                $user->is_enable_login = 0;
                $user->save();
                return redirect()->route($route)->with('success', 'User login disable successfully.');
            }
            else
            {
                $user->is_enable_login = 1;
                $user->save();
                return redirect()->route($route)->with('success', 'User login enable successfully.');
            }

        }
        else
        {
            $view = request()->get('view', 'grid');
            $route = $view === 'list' ? 'users.list.view' : 'users.index';
            return redirect()->route($route)->with('error', 'Permission denied.');
        }
    }
    public function fileImportExport()
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            return view('users.import');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }
    public function fileImport(Request $request)
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            session_start();

            $error = '';

            $html = '';
            if($request->hasFile('file'))
            {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);

                if ($extension == 'csv')
                {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++)
                    {
                        $column_name = strtolower(trim($file_header[$count]));
                        
                        $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                            <option value="">Set Count Data</option>
                                            <option value="name" ' . ($column_name == 'name' ? 'selected' : '') . '>Name</option>
                                            <option value="email" ' . ($column_name == 'email' ? 'selected' : '') . '>Email</option>
                                        </select>
                                </th>
                                ';
                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . htmlspecialchars((string) $row[$count], ENT_QUOTES, 'UTF-8') . '</td>';
                        }
                        $html .= '</tr>';

                        $temp_data[] = $row;

                    }
                    $_SESSION['file_data'] = $temp_data;
                }
                else
                {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            }
            else
            {
                $error = 'Please Select File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        }
        else
        {
            $output = array(
                'error' => 'Permission denied.',
                'output' => '',
            );

            return json_encode($output);
        }

    }

    public function fileImportModal()
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            return view('users.import_modal');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function UserImportdata(Request $request)
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];
            $role_r = Role::where('created_by', creatorId())->where('name', 'payroll_officer')->first();

            if (empty($role_r)) {
                return response()->json([
                    'html' => false,
                    'response' => 'Payroll officer role is not configured. Please contact administrator.',
                ]);
            }

            unset($_SESSION['file_data']);
            foreach ($file_data as $key=>$row) {
                $check_user = user::where('created_by', creatorId())->where('workspace_id',getActiveWorkspace())->Where('email',$row[$request->email])->get();
                if($check_user->isEmpty())
                {
                    try {

                        $user_data = new user();

                        $user_data->name                = $row[$request->name];
                        $user_data->email               = $row[$request->email];
                        $user_data->password            = null;
                        $user_data->lang                = 'en';
                        $user_data->type                = !empty($role_r) ? $role_r->name : 'payroll_officer';
                        $user_data->is_enable_login     = 0;
                        $user_data->created_by          = creatorId();
                        $user_data->workspace_id        = getActiveWorkspace();
                        $user_data->active_workspace    = getActiveWorkspace();
                        $user_data->save();
                        $user_data->addRole($role_r);

                        // Subscription system removed - all users have unlimited access
                    }
                    catch (\Exception $e)
                    {
                        $flag = 1;
                        $html .= '<tr>';
                            $html .= '<td>' . htmlspecialchars((string) $row[$request->name], ENT_QUOTES, 'UTF-8') . '</td>';
                            $html .= '<td>' . htmlspecialchars((string) $row[$request->email], ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '</tr>';
                    }
                }
                else
                {
                    $flag = 1;
                    $html .= '<tr>';
                        $html .= '<td>' . htmlspecialchars((string) $row[$request->name], ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '<td>' . htmlspecialchars((string) $row[$request->email], ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1)
            {
                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            }
            else
            {
                return response()->json([
                    'html' => false,
                    'response' => 'Data Imported Successfully',
                ]);
            }
        }
        else
        {
            return response()->json([
                'html' => false,
                'response' => 'Permission denied.',
            ]);
        }
    }
    public function UserLogHistory(Request $request)
    {
        if(Auth::user()->isAbleTo('user logs history'))
        {
            $perPage = (int) $request->get('per_page', 10);
            $filteruser = User::where('created_by', creatorId())->where('workspace_id', getActiveWorkspace())->get()->pluck('name', 'id');
            $filteruser->prepend('Select User', '');

            if(Auth::user()->type == 'super admin')
            {
                $filteruser = User::where('type', 'company')->get()->pluck('name', 'id');
                $filteruser->prepend('Select User', '');

                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where('login_details.type','company');
            }
            elseif(Auth::user()->type == 'company')
            {
                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => creatorId()]);
            }
            else
            {
                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.user_id' => \Auth::user()->id]);
            }


            if(!empty($request->month))
            {
                $query->whereMonth('date', date('m',strtotime($request->month)));
                $query->whereYear('date', date('Y',strtotime($request->month)));
            }else{
                $query->whereMonth('date', date('m'));
                $query->whereYear('date', date('Y'));
            }

            if(!empty($request->users))
            {
                $query->where('user_id', '=', $request->users);
            }
            $userdetails = $query
                ->orderBy('login_details.date', 'desc')
                ->paginate($perPage)
                ->appends($request->query());

            return view('users.userlog', compact( 'userdetails','filteruser'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function UserLogView($id)
    {
        if(!Auth::user()->isAbleTo('user manage'))
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $users_log = LoginDetail::find($id);

        if (!$users_log) {
            return redirect()->back()->with('error', __('Log not found.'));
        }

        return view('users.userlogview', compact('users_log'));
    }

    public function UserLogDestroy($id)
    {
        if(Auth::user()->isAbleTo('user delete'))
        {
            LoginDetail::where('id', $id)->delete();

            return redirect()->route('users.userlog.history')->with('success', __('User logs successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LoginWithCompany(Request $request, User $user,  $id)
    {
        $user = User::find($id);
        
        // Verify access: super admin can access all, master_admin can only access assigned companies
        if (Auth::user()->type === 'master_admin') {
            $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                ->pluck('company_id')
                ->toArray();
            
            if (!in_array($id, $assignedCompanyIds)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } elseif (Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        if ($user && auth()->check()) {
            Impersonate::take($request->user(), $user);
            return redirect('/home');
        }
    }

    public function ExitCompany(Request $request)
    {
        \Auth::user()->leaveImpersonation($request->user());
        return redirect('/dashboard');
    }

    public function companyInfo($id)
    {
        if(!empty($id)){
            // Verify access: super admin can access all, master_admin can only access assigned companies,
            // company users can only access their own info
            if (Auth::user()->type === 'master_admin') {
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')
                    ->toArray();
                
                if (!in_array($id, $assignedCompanyIds)) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            } elseif (Auth::user()->type === 'company') {
                if ((int) $id !== Auth::user()->id) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            } elseif (Auth::user()->type !== 'super admin') {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
            
            $data = $this->Counter($id);
            if($data['is_success']){
                $users_data = $data['response']['users_data'];
                $workspce_data = $data['response']['workspce_data'];
                return view('users.companyinfo', compact('id','users_data','workspce_data'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function userUnable(Request $request)
    {
        if(!empty($request->id) && !empty($request->company_id))
        {
            // Verify access: super admin can access all, master_admin can only access assigned companies,
            // company users can only manage their own company
            if (Auth::user()->type === 'master_admin') {
                $assignedCompanyIds = \App\Models\MasterAdminCompany::where('master_admin_id', Auth::user()->id)
                    ->pluck('company_id')
                    ->toArray();
                
                if (!in_array($request->company_id, $assignedCompanyIds)) {
                    return response()->json(['error' => __('Permission denied.')]);
                }
            } elseif (Auth::user()->type === 'company') {
                if ((int) $request->company_id !== Auth::user()->id) {
                    return response()->json(['error' => __('Permission denied.')]);
                }
            } elseif (Auth::user()->type !== 'super admin') {
                return response()->json(['error' => __('Permission denied.')]);
            }
            
            if($request->name == 'user')
            {
                User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
                $data = $this->Counter($request->company_id);

            }
            elseif($request->name == 'workspace')
            {
                $company = User::find($request->company_id);
                if($company->active_workspace != $request->id )
                {
                    WorkSpace::where('id',$request->id)->update(['is_disable' => $request->is_disable]);
                }
                else
                {
                    return response()->json(['error' => __('Active Workspace can not disable.')]);
                }

                if($request->is_disable == 0)
                {
                    User::where('workspace_id',$request->id)->where('type','!=','company')->update(['is_disable' => $request->is_disable]);
                }
                $data = $this->Counter($request->company_id);
            }
            if($data['is_success'])
            {
                $users_data = $data['response']['users_data'];
                $workspce_data = $data['response']['workspce_data'];
            }
            if($request->is_disable == 1){

                return response()->json(['success' => __('Successfully Unable.'),'users_data' => $users_data, 'workspce_data' => $workspce_data]);
            }else
            {
                return response()->json(['success' => __('Successfull Disable.'),'users_data' => $users_data, 'workspce_data' => $workspce_data]);
            }
        }
        return response()->json('error');
    }

    public function Counter($id)
    {
        $response = [];
        if(!empty($id))
        {
            $workspces= WorkSpace::where('created_by', $id)
            ->selectRaw('COUNT(*) as total_workspace, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_workspace, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_workspace')
            ->first();
            $workspaces = WorkSpace::where('created_by',$id)->get();
            $users_data = [];
            foreach($workspaces as $workspce)
            {
                $users = User::where('created_by',$id)->where('workspace_id',$workspce->id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();

                $users_data[$workspce->name] = [
                    'workspace_id' => $workspce->id,
                    'total_users' => !empty($users->total_users) ? $users->total_users : 0,
                    'disable_users' => !empty($users->disable_users) ? $users->disable_users : 0,
                    'active_users' => !empty($users->active_users) ? $users->active_users : 0,
                ];
            }
            $workspce_data =[
                'total_workspace' =>  $workspces->total_workspace,
                'disable_workspace' => $workspces->disable_workspace,
                'active_workspace' => $workspces->active_workspace,
            ];
            $response['users_data'] = $users_data;
            $response['workspce_data'] = $workspce_data;

            return [
                'is_success' => true,
                'response' => $response,
            ];
        }
        return [
            'is_success' => false,
            'error' => 'Plan is deleted.',
        ];
    }
}
