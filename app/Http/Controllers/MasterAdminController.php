<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\MasterAdminCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MasterAdminController extends Controller
{
    /**
     * Allowed admin types managed by this controller.
     */
    private const ADMIN_TYPES = ['super admin', 'master_admin'];

    /**
     * Role display names for the UI.
     */
    private const ROLE_OPTIONS = [
        'super admin'  => 'Global Administrator',
        'master_admin' => 'Master Administrator',
    ];

    /**
     * Display a listing of Global & Master Administrators.
     * Only accessible by Global Administrator (super admin).
     */
    public function index(Request $request)
    {
        if (Auth::user()->type !== 'super admin') {
            return redirect()->route('dashboard')->with('error', __('Permission denied.'));
        }

        $perPage = $request->get('per_page', 10);

        $query = User::whereIn('type', self::ADMIN_TYPES)
            ->where('id', '!=', Auth::id())
            ->with('assignedCompanies');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('type', $request->role);
        }

        $admins = $query->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        $roleOptions = self::ROLE_OPTIONS;

        return view('master-admin.index', compact('admins', 'roleOptions'));
    }

    /**
     * Show the form for creating a new administrator.
     */
    public function create()
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $companies = User::where('type', 'company')
            ->orderBy('name')
            ->pluck('name', 'id');

        $roleOptions = self::ROLE_OPTIONS;

        return view('master-admin.create', compact('companies', 'roleOptions'));
    }

    /**
     * Store a newly created administrator.
     */
    public function store(Request $request)
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:super admin,master_admin',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $selectedType = $request->role;

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'lang' => 'en',
            'is_enable_login' => 1,
            'created_by' => Auth::user()->id,
        ]);
        $admin->forceFill([
            'type' => $selectedType,
            'email_verified_at' => now(),
        ])->save();

        // Assign the matching role
        $roleName = $selectedType === 'super admin' ? 'super admin' : 'master_admin';
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $admin->addRole($role);
        }

        // Assign companies only for master_admin
        if ($selectedType === 'master_admin' && $request->has('companies') && !empty($request->companies)) {
            foreach ($request->companies as $companyId) {
                MasterAdminCompany::create([
                    'master_admin_id' => $admin->id,
                    'company_id' => $companyId,
                ]);
            }
        }

        // Send email notification
        try {
            $createUserSetting = adminSetting('Create User');
            $emailNotificationEnabled = $createUserSetting == 'on' || $createUserSetting == '1' || $createUserSetting === true;
            
            if ($emailNotificationEnabled) {
                $uArr = [
                    'email' => $admin->email,
                    'password' => $request->password,
                    'company_name' => $admin->name,
                ];
                $resp = \App\Models\EmailTemplate::sendEmailTemplate('New User', [$admin->email], $uArr, 1, null);
                \Log::info('Admin creation email sent', [
                    'admin_id' => $admin->id,
                    'email' => $admin->email,
                    'resp' => $resp
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Admin creation email error: ' . $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Administrator created successfully.'),
            ]);
        }

        return redirect()->route('master-admin.index')
            ->with('success', __('Administrator created successfully.'));
    }

    /**
     * Show the form for editing an administrator.
     */
    public function edit($id)
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $admin = User::where('id', $id)
            ->whereIn('type', self::ADMIN_TYPES)
            ->where('id', '!=', Auth::id())
            ->firstOrFail();

        $companies = User::where('type', 'company')
            ->orderBy('name')
            ->pluck('name', 'id');

        $assignedCompanyIds = $admin->assignedCompanies->pluck('id')->toArray();
        $roleOptions = self::ROLE_OPTIONS;

        return view('master-admin.edit', compact('admin', 'companies', 'assignedCompanyIds', 'roleOptions'));
    }

    /**
     * Update the specified administrator.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $admin = User::where('id', $id)
            ->whereIn('type', self::ADMIN_TYPES)
            ->where('id', '!=', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($admin->id)],
            'password' => 'nullable|min:6',
            'role' => 'required|in:super admin,master_admin',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $newType = $request->role;
        $oldType = $admin->type;

        $admin->name = $request->name;
        $admin->email = $request->email;
        
        if (!empty($request->password)) {
            $admin->password = Hash::make($request->password);
        }
        
        $admin->save();

        // Update type if changed
        if ($oldType !== $newType) {
            $admin->forceFill(['type' => $newType])->save();

            // Swap role
            $admin->roles()->detach();
            $roleName = $newType === 'super admin' ? 'super admin' : 'master_admin';
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $admin->addRole($role);
            }
        }

        // Sync company assignments (only relevant for master_admin)
        MasterAdminCompany::where('master_admin_id', $admin->id)->delete();
        
        if ($newType === 'master_admin' && $request->has('companies') && !empty($request->companies)) {
            foreach ($request->companies as $companyId) {
                MasterAdminCompany::create([
                    'master_admin_id' => $admin->id,
                    'company_id' => $companyId,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Administrator updated successfully.'),
            ]);
        }

        return redirect()->route('master-admin.index')
            ->with('success', __('Administrator updated successfully.'));
    }

    /**
     * Remove the specified administrator.
     */
    public function destroy($id)
    {
        if (Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $admin = User::where('id', $id)
            ->whereIn('type', self::ADMIN_TYPES)
            ->where('id', '!=', Auth::id())
            ->firstOrFail();

        // Remove company assignments
        MasterAdminCompany::where('master_admin_id', $admin->id)->delete();

        $admin->delete();

        return redirect()->route('master-admin.index')
            ->with('success', __('Administrator deleted successfully.'));
    }

    /**
     * Manage company assignments for a Master Administrator.
     */
    public function manageCompanies($id)
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $masterAdmin = User::where('id', $id)
            ->whereIn('type', self::ADMIN_TYPES)
            ->firstOrFail();

        $companies = User::where('type', 'company')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $id)
            ->pluck('company_id')
            ->toArray();

        return view('master-admin.manage-companies', compact('masterAdmin', 'companies', 'assignedCompanyIds'));
    }

    /**
     * Update company assignments for an administrator.
     */
    public function updateCompanies(Request $request, $id)
    {
        if (Auth::user()->type !== 'super admin') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $masterAdmin = User::where('id', $id)
            ->whereIn('type', self::ADMIN_TYPES)
            ->firstOrFail();

        // Sync company assignments
        MasterAdminCompany::where('master_admin_id', $masterAdmin->id)->delete();
        
        if ($request->has('companies') && !empty($request->companies)) {
            foreach ($request->companies as $companyId) {
                MasterAdminCompany::create([
                    'master_admin_id' => $masterAdmin->id,
                    'company_id' => $companyId,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Company assignments updated successfully.'),
            ]);
        }

        return redirect()->route('master-admin.index')
            ->with('success', __('Company assignments updated successfully.'));
    }
}
