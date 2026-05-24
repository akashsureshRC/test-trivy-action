<?php

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\Language;
use App\Models\Permission;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\userActiveModule;
use Illuminate\Support\Collection;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

if (!function_exists('getMenu')) {
    function getMenu()
    {
        $user = auth()->user();
        
        // Generate menu fresh each time (no caching for now)
        $role = $user->roles->first();
        $menu = new \App\Classes\Menu($user);
        if ($role && $role->name == 'super admin') {
            event(new \App\Events\SuperAdminMenuEvent($menu));
        } elseif ($user->type == 'master_admin') {
            event(new \App\Events\MasterAdminMenuEvent($menu));
        } else {
            event(new \App\Events\CompanyMenuEvent($menu));
        }
        $collection = collect($menu->menu);
        $grouped = $collection->groupBy('category')->toArray();

        $categoryIcon = categoryIcon();
        uksort($grouped, function ($a, $b) use ($categoryIcon) {
            $indexA = array_search($a, array_keys($categoryIcon));
            $indexB = array_search($b, array_keys($categoryIcon));
            return $indexA <=> $indexB;
        });
        
        return generateMenu($grouped, null);
    }
}


// if (!function_exists('generateMenu')) {
// function generateMenu($menuItems, $parent = null)
// {
//     $html = '';

//     $html .= '<ul class="dash-navbar">';
//     $filteredItems = array_filter($menuItems, function ($item) use ($parent) {
//         return $item['parent'] == $parent;
//     });
//     if($parent == 'subscription')
//     {
//         // dd($filteredItems);
//     }
//     usort($filteredItems, function ($a, $b) {
//         return $a['order'] - $b['order'];
//     });

//     foreach ($filteredItems as $item) {
//         $html .= '<li class="dash-item dash-hasmenu">';
//         $html .= '<a href="' . (!empty($item['route']) ? route($item['route']) : '#!') . '" class="dash-link">
//         <span class="dash-micon"><i class="ti ti-' . $item['icon'] . '"></i></span>
//         <span class="dash-mtext">' . $item['title'] . '</span>';

//         $html .= '<span class="dash-arrow"> <i data-feather="chevron-right"></i> </span> </a> <ul class="dash-submenu">';
// 		$html .= generateMenu($menuItems, $item['name']);

//         $html .= '</ul> </li>';
//     }
//     $html .= '</ul>';

//     return $html;
// }
// }

if (!function_exists('generateMenu')) {
    function generateMenu($grouped, $parent = null)
    {
         $html = '';

        foreach ($grouped as $category => $menuItems)
        {
            $company_settings = getCompanyAllSetting();
            if(!empty($company_settings['category_wise_sidemenu']) && $company_settings['category_wise_sidemenu'] == 'on'){
                $icon = isset(categoryIcon()[$category]) ? categoryIcon()[$category] : 'home';
                $categoryLabel = htmlspecialchars((string) $category, ENT_QUOTES, 'UTF-8');
                $safeIcon = preg_replace('/[^a-z0-9\-]/i', '', (string) $icon);
                $html .= '<li class="dash-item dash-caption">
                    <label for="helooo">'.$categoryLabel.'</label>
                    <i class="ti ti-'.$safeIcon.'"></i>
                      </li>';
            }

            $html .= generateSubMenu($menuItems,$parent);

        }

        return $html;
    }
}

if (!function_exists('generateSubMenu')) {
    function generateSubMenu($menuItems, $parent = null)
    {
        $html = '';

        // dd($parent);

        $filteredItems = array_filter($menuItems, function ($item) use ($parent) {
            return $item['parent'] == $parent;
        });

        usort($filteredItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        foreach ($filteredItems as $item) {
            $hasChildren = hasChildren($menuItems, $item['name']);
            $rawIcon = trim((string) ($item['icon'] ?? ''));
            $normalizedIcon = preg_replace('/^ti\s+ti\-/i', '', $rawIcon);
            $normalizedIcon = preg_replace('/^ti\-/i', '', (string) $normalizedIcon);
            $safeIcon = preg_replace('/[^a-z0-9\-]/i', '', (string) $normalizedIcon);
            $safeTitle = htmlspecialchars((string) __($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            if ($item['parent'] == null) {
                $html .= '<li class="dash-item dash-hasmenu 1">';
            } else {
                $html .= '<li class="dash-item">';
            }
            $target = isset($item['target']) ? ' target="' . htmlspecialchars((string) $item['target'], ENT_QUOTES, 'UTF-8') . '"' : '';
            
            $href = '#!';
            if (!empty($item['route'])) {
                try {
                    if (\Illuminate\Support\Facades\Route::has($item['route'])) {
                        $href = route($item['route']);
                    }
                } catch (\Exception $e) {
                    // Route doesn't exist, use #! as fallback
                }
            }
            
            $html .= '<a href="' . htmlspecialchars((string) $href, ENT_QUOTES, 'UTF-8') . '" class="dash-link"' . $target . '>';

            if ($item['parent'] == null) {
                $html .= ' <span class="dash-micon"><i class="ti ti-' . $safeIcon . '"></i></span>
                <span class="dash-mtext 11">';
            }
            $html .= $safeTitle . '</span>';

            if ($hasChildren) {
                $html .= '<span class="dash-arrow"> <i data-feather="chevron-right"></i> </span> </a>';
                $html .= '<ul class="dash-submenu">';
                $html .= generateSubMenu($menuItems, $item['name']);
                $html .= '</ul>';
            } else {
                $html .= '</a>';
            }

            $html .= '</li>';
        }
        return $html;
    }
}

if (!function_exists('categoryIcon')) {
    function categoryIcon()
    {
        $categoryIcon = [
        'General' => 'indent-increase',
        'Addon Manager' => 'apps',
        'Finance' => 'chart-dots',
        'HR' => 'users',
        'Sales' => 'businessplan',
        'eCommerce' => 'shopping-cart',
        'Education' => 'school',
        'Operations' => 'stack-2',
        'Productivity' => 'list-check',
        'Communication' => 'messages',
        'Medical' => 'ambulance',
        'Vehicle' => 'bike',
        'AI' => 'brand-gitlab',
        'Settings' => 'adjustments-horizontal',
       ];

       return $categoryIcon;
    }
}

if (!function_exists('hasChildren')) {
    function hasChildren($menuItems, $name)
    {
        foreach ($menuItems as $item) {
            if ($item['parent'] === $name) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists('getSettingMenu')) {
    function getSettingMenu()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        $menu = new \App\Classes\Menu($user);
        if ($role && $role->name == 'super admin') {
            event(new \App\Events\SuperAdminSettingMenuEvent($menu));
        } else {
            event(new \App\Events\CompanySettingMenuEvent($menu));
        }
        return generateSettingMenu($menu->menu);
    }
}


if (!function_exists('generateSettingMenu')) {
    function generateSettingMenu($menuItems)
    {
        usort($menuItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($menuItems as $menu) {
            $method = isset($menu['method']) ? $menu['method'] : null;
            $navigation = htmlspecialchars((string) ($menu['navigation'] ?? ''), ENT_QUOTES, 'UTF-8');
            $module = htmlspecialchars((string) ($menu['module'] ?? ''), ENT_QUOTES, 'UTF-8');
            $method = htmlspecialchars((string) ($method ?? ''), ENT_QUOTES, 'UTF-8');
            $title = htmlspecialchars((string) ($menu['title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $html .= '<a href="#' . $navigation . '" data-module="' . $module . '" data-method="' . $method . '"  class="list-group-item list-group-item-action setting-menu-nav">' . $title . '<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>';
        }
        return $html;
    }
}
if (!function_exists('getSettings')) {
    function getSettings()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        if ($role && $role->name == 'super admin') {
            $settings = getAdminAllSetting();
            $html = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\SuperAdminSettingEvent($html));
        } else {
            $settings = getCompanyAllSetting();
            $html = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\CompanySettingEvent($html));
        }
        return generateSettings($html->html);
    }
}
if (!function_exists('generateSettings')) {
    function generateSettings($settingItems)
    {
        usort($settingItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($settingItems as $setting) {
            $html .= $setting['html'];
        }
        return $html;
    }
}

if (!function_exists('getAdminAllSetting')) {
    function getAdminAllSetting()
    {
        // Laravel cache
        return Cache::rememberForever('admin_settings', function () {
            $super_admin = User::where('type', 'super admin')->first();

            $settings = [];
            if ($super_admin) {
                $settings = Setting::where('created_by', $super_admin->id)->where('workspace', 0)->pluck('value', 'key')->toArray();
            }

            return $settings;
        });
    }
}

if (!function_exists('getCompanyAllSetting')) {
    function getCompanyAllSetting($user_id = null, $workspace = null)
    {
        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user =  auth()->user();
        }

        $workspace = $workspace ?? $user->active_workspace;

        // // Check if the user is not 'company' or 'super admin' and find the creator
        if (!in_array($user->type, ['company', 'super admin'])) {
            $user = User::find($user->created_by);
        }

        if (!empty($user)) {
            $key = 'company_settings_' . $workspace . '_' . $user->id;
            return Cache::rememberForever($key, function () use ($user, $workspace) {
                $settings = [];
                $settings = Setting::where('created_by', $user->id)->where('workspace', $workspace)->pluck('value', 'key')->toArray();
                return $settings;
            });
        }

        return [];
    }
}

if (!function_exists('adminSetting')) {
    function adminSetting($key)
    {
        if ($key) {
            $admin_settings = getAdminAllSetting();
            $setting = (array_key_exists($key, $admin_settings)) ? $admin_settings[$key] : null;
            return $setting;
        }
    }
}

if (!function_exists('companySetting')) {
    function companySetting($key, $user_id = null, $workspace = null)
    {
        if ($key) {
            $company_settings = getCompanyAllSetting($user_id, $workspace);
            $setting = null;
            if (!empty($company_settings)) {
                $setting = (array_key_exists($key, $company_settings)) ? $company_settings[$key] : null;
            }
            return $setting;
        }
    }
}

if (!function_exists('adminSettingCacheForget')) {
    function adminSettingCacheForget()
    {
        try {
            Cache::forget('admin_settings');
        } catch (\Exception $e) {
            \Log::error('adminSettingCacheForget :' . $e->getMessage());
        }
    }
}

if (!function_exists('companySettingCacheForget')) {
    function companySettingCacheForget($user_id = null, $workspace = null)
    {
        try {
            if (empty($user_id)) {
                $user_id = creatorId();
            }
            if (empty($workspace)) {
                $workspace = getActiveWorkspace();
            }
            $key = 'company_settings_' . $workspace . '_' . $user_id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('companySettingCacheForget :' . $e->getMessage());
        }
    }
}

if (!function_exists('sideMenuCacheForget')) {
    function sideMenuCacheForget($type = null, $user_id = null)
    {
        if ($type == 'all') {
            Cache::flush();
        }

        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user =  auth()->user();
        }

        if ($user->type == 'company') {
            $users = User::select('id')->where('created_by', $user->id)->pluck('id');
            foreach ($users as $id) {
                try {
                    $key = 'sidebar_menu_' . $id;
                    Cache::forget($key);
                } catch (\Exception $e) {
                    \Log::error('comapnySettingCacheForget :' . $e->getMessage());
                }
            }
            try {
                $key = 'sidebar_menu_' . $user->id;
                Cache::forget($key);
            } catch (\Exception $e) {
                \Log::error('comapnySettingCacheForget :' . $e->getMessage());
            }
            return true;
        }

        try {
            $key = 'sidebar_menu_' . $user->id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }

        return true;
    }
}

if (!function_exists('getActiveWorkspace')) {
    function getActiveWorkspace($user_id = null)
    {
        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user =  auth()->user();
        }

        if ($user) {
            if (!empty($user->active_workspace)) {
                return $user->active_workspace;
            } else {
                if ($user->type == 'super admin') {
                    return 0;
                } else {
                    static $WorkSpace = null;
                    if ($WorkSpace == null) {
                        $ownerId = ($user->type == 'company') ? $user->id : $user->created_by;
                        $WorkSpace = WorkSpace::where('created_by', $ownerId)->first();
                    }
                    return $WorkSpace ? $WorkSpace->id : 0;
                }
            }
        }
    }
}

if (!function_exists('getWorkspace')) {
    function getWorkspace()
    {
        $data = [];
        if (Auth::check()) {
            static $WorkSpace = null;
            if ($WorkSpace == null) {
                $ownerId = creatorId();
                $WorkSpace = WorkSpace::where('created_by', $ownerId)
                    ->where('is_disable', 1)
                    ->get();
            }
            return $WorkSpace;
        } else {
            return $data;
        }
    }
}


if (!function_exists('creatorId')) {
    function creatorId()
    {
        if (Auth::user()->type == 'super admin' || Auth::user()->type == 'company') {
            return Auth::user()->id;
        } else {
            return Auth::user()->created_by;
        }
    }
}


if (!function_exists('getModuleList')) {
    function getModuleList()
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            array_push($list, $module->getName());
        }
        return $list;
    }
}

if (!function_exists('getShowModuleList')) {
    function getShowModuleList()
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);
            if (!isset($json['display']) || $json['display'] == true) {
                array_push($list, $module->getName());
            }
        }
        return $list;
    }
}

if (!function_exists('moduleIsActive')) {
    function moduleIsActive($module, $user_id = null)
    {
        // RC ClearPay: HRM is now merged into core, always active
        if ($module === 'Hrm') {
            return true;
        }
        
        if (Module::has($module)) {
            $module = Module::find($module);
            if ($module->isEnabled()) {
                if (Auth::check()) {
                    $user = Auth::user();
                } elseif ($user_id != null) {
                    $user = User::find($user_id);
                }
                if (!empty($user)) {
                    if ($user->type == 'super admin') {
                        return true;
                    } else {
                        $active_module = activatedModule($user->id);
                        if ((count($active_module) > 0 && in_array($module->getName(), $active_module))) {
                            return true;
                        }
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('activatedModule')) {
    function activatedModule($user_id = null)
    {
        // Module system removed - ClearPay is now a single core module
        // Always return Base and Hrm as all functionality is part of core
        return ['Base', 'Hrm'];
    }
}

// module alias name
if (!function_exists('moduleAliasName')) {
    function moduleAliasName($module_name)
    {
        $module = Module::find($module_name);
        if (!empty($module)) {
            return $module->get('alias') ?? $module_name;
        }
        return $module_name;
    }
}

if (!function_exists('getPermissionByModule')) {
    function getPermissionByModule($mudule)
    {
        $user = Auth::user();

        if ($user->type == 'super admin') {
            $permissions = Permission::where('module', $mudule)->orderBy('name')->get();
        } else {
            $permissions = new Collection();
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
            $permissions = $permissions->where('module', $mudule);
        }
        // $permissions = Spatie\Permission\Models\Permission::where('module',$mudule)->orderBy('name')->get();
        return $permissions;
    }
}

if (!function_exists('getActiveLanguage')) {
    function getActiveLanguage()
    {
        if (Auth::check()) {
            // 1. User's own language preference (set via header dropdown)
            if (!empty(Auth::user()->lang)) {
                return Auth::user()->lang;
            }
            // 2. Company-level default for non-super-admin users
            if (Auth::user()->type != 'super admin') {
                $company_settings = getCompanyAllSetting();
                if (!empty($company_settings['defult_language'])) {
                    return $company_settings['defult_language'];
                }
            }
        }
        // 3. Admin/global default
        $admin_settings = getAdminAllSetting();
        return !empty($admin_settings['defult_language']) ? $admin_settings['defult_language'] : 'en';
    }
}

if (!function_exists('languages')) {
    function languages()
    {

        try {
            $arrLang = Language::where('status', 1)->get()->pluck('name', 'code')->toArray();
        } catch (\Throwable $th) {
            $arrLang = [
                "ar" => "Arabic",
                "da" => "Danish",
                "de" => "German",
                "en" => "English",
                "es" => "Spanish",
                "fr" => "French",
                "it" => "Italian",
                "ja" => "Japanese",
                "nl" => "Dutch",
                "pl" => "Polish",
                "pt" => "Portuguese",
                "ru" => "Russian",
                "tr" => "Turkish"
            ];
        }
        return $arrLang;
    }
}

if (!function_exists('deleteDirectory')) {
    function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}

// setConfigEmail ( SMTP )
if (!function_exists('setConfigEmail')) {
    function setConfigEmail($user_id = null, $workspace_id = null)
    {
        try {

            if (!empty($user_id) && !empty($workspace_id)) {
                $company_settings = getCompanyAllSetting($user_id, $workspace_id);
            } elseif (!empty($user_id)) {
                $company_settings = getCompanyAllSetting($user_id);
            } else if (Auth::check()) {
                $company_settings = getCompanyAllSetting();
            } else {
                $user_id = User::where('type', 'super admin')->first()->id;
                $company_settings = getCompanyAllSetting($user_id);
            }

            // Check if company has mail settings configured, if not fall back to .env defaults
            if (empty($company_settings['mail_host']) || empty($company_settings['mail_from_address'])) {
                // No custom SMTP configured — use .env defaults (no config override needed)
                return true;
            }

            config(
                [
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => $company_settings['mail_driver'] ?? 'smtp',
                    'mail.mailers.smtp.host' => $company_settings['mail_host'],
                    'mail.mailers.smtp.port' => $company_settings['mail_port'],
                    'mail.mailers.smtp.encryption' => $company_settings['mail_encryption'],
                    'mail.mailers.smtp.username' => $company_settings['mail_username'],
                    'mail.mailers.smtp.password' => $company_settings['mail_password'],
                    'mail.from.address' => $company_settings['mail_from_address'],
                    'mail.from.name' => $company_settings['mail_from_name'],
                ]
            );
            return true;
        } catch (\Exception $e) {
            \Log::error('setConfigEmail failed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Configure SMTP using the Super Admin's settings.
 * Used for admin-to-customer communication
 * Falls back to .env SMTP configuration if super admin has no custom settings.
 */
if (!function_exists('setAdminConfigEmail')) {
    function setAdminConfigEmail()
    {
        try {
            $adminSettings = getAdminAllSetting();

            if (empty($adminSettings['mail_host']) || empty($adminSettings['mail_from_address'])) {
                // No admin SMTP configured — use .env defaults
                return true;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => $adminSettings['mail_driver'] ?? 'smtp',
                'mail.mailers.smtp.host' => $adminSettings['mail_host'],
                'mail.mailers.smtp.port' => $adminSettings['mail_port'] ?? 587,
                'mail.mailers.smtp.encryption' => strtolower($adminSettings['mail_encryption'] ?? 'tls'),
                'mail.mailers.smtp.username' => $adminSettings['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $adminSettings['mail_password'] ?? null,
                'mail.from.address' => $adminSettings['mail_from_address'],
                'mail.from.name' => $adminSettings['mail_from_name'] ?? config('app.name'),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('setAdminConfigEmail failed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Configure SMTP using the Company's settings for employee communication.
 * Resolves the company owner from the employee's workspace.
 * Used for customer-to-employee/resource communication:
 * Falls back to .env SMTP configuration if the company has no custom settings.
 */
if (!function_exists('setCompanyConfigEmailForEmployee')) {
    function setCompanyConfigEmailForEmployee($employee)
    {
        try {
            $workspaceId = $employee->workspace_id ?? null;
            if (empty($workspaceId)) {
                \Log::warning('setCompanyConfigEmailForEmployee: Employee has no workspace_id, using .env defaults');
                return true;
            }

            // Find the company user who owns this workspace
            $workspace = \App\Models\WorkSpace::find($workspaceId);
            if (!$workspace || empty($workspace->created_by)) {
                \Log::warning('setCompanyConfigEmailForEmployee: Workspace not found or no owner, using .env defaults');
                return true;
            }

            return setConfigEmail($workspace->created_by, $workspaceId);
        } catch (\Exception $e) {
            \Log::error('setCompanyConfigEmailForEmployee failed: ' . $e->getMessage());
            return false;
        }
    }
}

// S3 file upload helpers

if (!function_exists('uploadToS3')) {
    /**
     * Upload a file to S3 and return just the filename
     * 
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @param string $folder The S3 folder (e.g., 'avatars', 'logos', 'invoices')
     * @return array ['flag' => 1, 'filename' => 'hash.ext'] or ['flag' => 0, 'msg' => 'error']
     */
    function uploadToS3($file, string $folder): array
    {
        try {
            $s3Service = app(\App\Services\S3StorageService::class);
            $filename = $s3Service->uploadFile($file, $folder);
            
            return [
                'flag' => 1,
                'msg' => 'success',
                'filename' => $filename,
            ];
        } catch (\Exception $e) {
            Log::error('S3 upload error: ' . $e->getMessage());
            return [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

if (!function_exists('uploadContentToS3')) {
    /**
     * Upload raw content (like PDF) to S3
     * 
     * @param string $content The file content
     * @param string $folder The S3 folder
     * @param string $filename The filename to use
     * @param string $contentType The MIME type
     * @return array
     */
    function uploadContentToS3(string $content, string $folder, string $filename, string $contentType = 'application/octet-stream'): array
    {
        try {
            $s3Service = app(\App\Services\S3StorageService::class);
            $s3Service->uploadContent($content, $folder, $filename, $contentType);
            
            return [
                'flag' => 1,
                'msg' => 'success',
                'filename' => $filename,
            ];
        } catch (\Exception $e) {
            Log::error('S3 upload content error: ' . $e->getMessage());
            return [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

if (!function_exists('getS3SignedUrl')) {
    /**
     * Get a signed URL for an S3 file
     * 
     * @param string $filename The filename stored in database
     * @param string $folder The S3 folder
     * @param int $expiresIn Expiration time in seconds (default 5 minutes)
     * @return string|null The signed URL or null on error
     */
    function getS3SignedUrl(string $filename, string $folder, int $expiresIn = 300): ?string
    {
        try {
            if (empty($filename)) {
                return null;
            }
            
            $s3Service = app(\App\Services\S3StorageService::class);
            return $s3Service->getSignedUrl($filename, $folder, $expiresIn);
        } catch (\Exception $e) {
            Log::error('S3 signed URL error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getS3PublicUrl')) {
    /**
     * Get public URL for an S3 file (for folders with public bucket policy)
     * 
     * @param string $filename The filename stored in database
     * @param string $folder The S3 folder
     * @return string|null The public URL or null on error
     */
    function getS3PublicUrl(string $filename, string $folder): ?string
    {
        try {
            if (empty($filename)) {
                return null;
            }
            
            $s3Service = app(\App\Services\S3StorageService::class);
            return $s3Service->getPublicUrl($filename, $folder);
        } catch (\Exception $e) {
            Log::error('S3 public URL error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('deleteFromS3')) {
    /**
     * Delete a file from S3
     * 
     * @param string $filename The filename
     * @param string $folder The S3 folder
     * @return bool
     */
    function deleteFromS3(string $filename, string $folder): bool
    {
        try {
            if (empty($filename)) {
                return true;
            }
            
            $s3Service = app(\App\Services\S3StorageService::class);
            $s3Service->deleteFile($filename, $folder);
            return true;
        } catch (\Exception $e) {
            Log::error('S3 delete error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('s3FileExists')) {
    /**
     * Check if a file exists in S3
     * 
     * @param string $filename The filename
     * @param string $folder The S3 folder
     * @return bool
     */
    function s3FileExists(string $filename, string $folder): bool
    {
        try {
            if (empty($filename)) {
                return false;
            }
            
            $s3Service = app(\App\Services\S3StorageService::class);
            return $s3Service->fileExists($filename, $folder);
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('generateS3Filename')) {
    /**
     * Generate a unique hash-based filename for S3
     * 
     * @param string $originalName Original filename to extract extension
     * @return string Unique filename with extension
     */
    function generateS3Filename(string $originalName): string
    {
        $s3Service = app(\App\Services\S3StorageService::class);
        return $s3Service->generateFilename($originalName);
    }
}

if (!function_exists('getAvatarUrl')) {
    /**
     * Get avatar URL from S3 using signed URL for security
     * 
     * @param string|null $avatar The avatar filename (S3 hash filename)
     * @param int $expiresIn Expiration time in seconds (default 30 minutes for avatars)
     * @return string|null The signed URL or null if not uploaded
     */
    function getAvatarUrl(?string $avatar, int $expiresIn = 1800): ?string
    {
        if (empty($avatar)) {
            return null;
        }
        
        return getS3SignedUrl($avatar, S3_FOLDER_AVATARS, $expiresIn);
    }
}

if (!function_exists('getLogoUrl')) {
    /**
     * Get logo URL from S3 using public URL (logos bucket is public)
     * 
     * @param string|null $logo The logo filename (S3 hash filename)
     * @param string $type 'dark', 'light', or 'favicon' (used for fallback selection)
     * @return string|null The public URL or null if not uploaded
     */
    function getLogoUrl(?string $logo, string $type = 'dark'): ?string
    {
        if (empty($logo)) {
            return null;
        }
        
        return getS3PublicUrl($logo, S3_FOLDER_LOGOS);
    }
}

if (!function_exists('getLogoFallback')) {
    /**
     * Get local fallback URL for a logo type
     * 
     * @param string $type 'dark', 'light', or 'favicon'
     * @return string The local asset URL
     */
    function getLogoFallback(string $type = 'dark'): string
    {
        $map = [
            'dark'    => 'uploads/logo/rc-clearpay-logo-dark.png',
            'light'   => 'uploads/logo/rc-clearpay-logo.png',
            'favicon' => 'uploads/logo/favicon.png',
        ];

        return asset($map[$type] ?? $map['dark']);
    }
}

if (!function_exists('getFaviconUrl')) {
    /**
     * Get favicon URL from S3 (public) with local fallback
     * Checks company settings first, then admin settings
     * 
     * @return string The favicon URL (S3 public or local fallback)
     */
    function getFaviconUrl(): string
    {
        $admin_settings = getAdminAllSetting();
        $favicon = null;

        if (\Auth::check() && (\Auth::user()->type != 'super admin')) {
            $company_settings = getCompanyAllSetting();
            $favicon = $company_settings['favicon'] ?? $admin_settings['favicon'] ?? null;
        } else {
            $favicon = $admin_settings['favicon'] ?? null;
        }

        return getLogoUrl($favicon, 'favicon') ?? getLogoFallback('favicon');
    }
}

if (!function_exists('getEmailBranding')) {
    /**
     * Get branding data for email templates.
     *
     * @param string $context 'admin' for Admin→Customer emails, 'company' for Customer→Employee emails
     * @param int|null $companyUserId  Company user ID (for company context when no auth)
     * @param int|null $workspace      Workspace ID (for company context when no auth)
     * @return array{logo_url: string, company_name: string, accent_color: string, footer_text: string|null, support_email: string|null, address: string|null}
     */
    function getEmailBranding(string $context = 'admin', ?int $companyUserId = null, ?int $workspace = null): array
    {
        $themeColorMap = [
            'theme-1'  => '#3956ca',
            'theme-2'  => '#75C251',
            'theme-3'  => '#584ED2',
            'theme-4'  => '#145388',
            'theme-5'  => '#B9406B',
            'theme-6'  => '#008ECC',
            'theme-7'  => '#922C88',
            'theme-8'  => '#C0A145',
            'theme-9'  => '#48494B',
            'theme-10' => '#0C7785',
        ];

        $adminSettings = getAdminAllSetting();

        if ($context === 'company') {
            $settings = !empty($companyUserId)
                ? getCompanyAllSetting($companyUserId, $workspace)
                : ((\Auth::check() && \Auth::user()->type !== 'super admin') ? getCompanyAllSetting() : $adminSettings);

            // Determine accent color from company theme
            $accentColor = '#3956ca'; // default green
            if (!empty($settings['color_flag']) && $settings['color_flag'] === 'true' && !empty($settings['custom_color'])) {
                $accentColor = $settings['custom_color'];
            } elseif (!empty($settings['color'])) {
                $accentColor = $themeColorMap[$settings['color']] ?? '#3956ca';
            }

            // Logo: use light-mode/white-bg logo for emails
            $logoFile = $settings['logo_light'] ?? $adminSettings['logo_light'] ?? null;
            $logoUrl = getLogoUrl($logoFile, 'light') ?? getLogoFallback('light');

            return [
                'logo_url'      => $logoUrl,
                'company_name'  => $settings['company_name'] ?? config('app.name', 'RC ClearPay'),
                'accent_color'  => $accentColor,
                'footer_text'   => $settings['footer_text'] ?? null,
                'support_email' => $settings['company_email'] ?? null,
                'address'       => $settings['company_address'] ?? null,
            ];
        }

        // Admin context
        $accentColor = '#3956ca';
        if (!empty($adminSettings['color_flag']) && $adminSettings['color_flag'] === 'true' && !empty($adminSettings['custom_color'])) {
            $accentColor = $adminSettings['custom_color'];
        } elseif (!empty($adminSettings['color'])) {
            $accentColor = $themeColorMap[$adminSettings['color']] ?? '#3956ca';
        }

        $logoFile = $adminSettings['logo_light'] ?? null;
        $logoUrl = getLogoUrl($logoFile, 'light') ?? getLogoFallback('light');

        return [
            'logo_url'      => $logoUrl,
            'company_name'  => $adminSettings['company_name'] ?? config('app.name', 'RC ClearPay'),
            'accent_color'  => $accentColor,
            'footer_text'   => $adminSettings['footer_text'] ?? null,
            'support_email' => $adminSettings['company_email'] ?? null,
            'address'       => $adminSettings['company_address'] ?? null,
        ];
    }
}

if (!function_exists('getHelpdeskAttachmentUrl')) {
    /**
     * Get helpdesk attachment URL from S3 using signed URL for security
     * 
     * @param string|null $filename The attachment filename (S3 hash filename)
     * @param int $expiresIn Expiration time in seconds (default 5 minutes)
     * @return string|null The signed URL or null if not uploaded
     */
    function getHelpdeskAttachmentUrl(?string $filename, int $expiresIn = 300): ?string
    {
        if (empty($filename)) {
            return null;
        }
        
        return getS3SignedUrl($filename, S3_FOLDER_HELPDESK, $expiresIn);
    }
}

// S3 folder constants
if (!defined('S3_FOLDER_AVATARS')) {
    define('S3_FOLDER_AVATARS', 'avatars');
}
if (!defined('S3_FOLDER_LOGOS')) {
    define('S3_FOLDER_LOGOS', 'logos');
}
if (!defined('S3_FOLDER_INVOICES')) {
    define('S3_FOLDER_INVOICES', 'invoices');
}
if (!defined('S3_FOLDER_HELPDESK')) {
    define('S3_FOLDER_HELPDESK', 'helpdesk_attachments');
}

// file upload

if (!function_exists('uploadFile')) {
    /**
     * Upload a file to S3
     * @deprecated Use upload_to_s3() instead for new code
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $key_name Form field name
     * @param string $name Filename to save as
     * @param string $path S3 folder path
     * @param array $custom_validation Custom validation rules
     * @return array
     */
    function uploadFile($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();
            
            $max_size = !empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
            $mimes = !empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx,webp';
            
            $file = $request->$key_name;
            if (count($custom_validation) > 0) {
                $validation = $custom_validation;
            } else {
                $validation = [
                    'mimes:' . $mimes,
                    'max:' . $max_size,
                ];
            }
            $validator = Validator::make($request->all(), [
                $key_name => $validation
            ]);
            if ($validator->fails()) {
                $res = [
                    'flag' => 0,
                    'msg' => $validator->messages()->first(),
                ];
                Log::error('File upload validation error: ' . $validator->messages()->first());
                return $res;
            } else {
                $save = Storage::disk('s3')->putFileAs(
                    $path,
                    $file,
                    $name
                );
                $res = [
                    'flag' => 1,
                    'msg'  => 'success',
                    'url'  => $save
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (!function_exists('multiUploadFile')) {
    /**
     * Upload multiple files to S3
     * @deprecated Use upload_to_s3() instead for new code
     * 
     * @param mixed $request File object
     * @param string $key_name Form field name
     * @param string $name Filename to save as
     * @param string $path S3 folder path
     * @param array $custom_validation Custom validation rules
     * @return array
     */
    function multiUploadFile($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();
            
            $max_size = !empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
            $mimes = !empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';

            $file = $request;
            $key_validation = $key_name . '*';
            if (count($custom_validation) > 0) {
                $validation = $custom_validation;
            } else {
                $validation = [
                    'mimes:' . $mimes,
                    'max:' . $max_size,
                ];
            }
            $validator = Validator::make(array($key_name => $request), [
                $key_validation => $validation
            ]);
            if ($validator->fails()) {
                $res = [
                    'flag' => 0,
                    'msg' => $validator->messages()->first(),
                ];
                return $res;
            } else {
                $save = Storage::disk('s3')->putFileAs(
                    $path,
                    $file,
                    $name
                );
                $res = [
                    'flag' => 1,
                    'msg'  => 'success',
                    'url'  => $save
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (!function_exists('checkFile')) {
    /**
     * Check if a file exists in S3
     * @deprecated Use s3_file_exists() instead for new code
     * 
     * @param string $path The S3 path or filename
     * @return bool
     */
    function checkFile($path)
    {
        if (empty($path)) {
            return false;
        }
        
        try {
            return Storage::disk('s3')->exists($path);
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('getFile')) {
    /**
     * Get file URL from S3
     * 
     * @param string $path The S3 path (folder/filename) or just filename
     * @param string|null $folder S3 folder for standalone filenames
     * @return string|null The public URL or null if not found
     */
    function getFile($path, ?string $folder = null)
    {
        if (empty($path)) {
            return null;
        }

        if ($folder !== null && !str_contains($path, '/')) {
            return getS3PublicUrl($path, $folder);
        }

        return Storage::disk('s3')->url($path);
    }
}

if (!function_exists('getBaseFile')) {
    /**
     * Get file URL from S3
     * 
     * @param string $path The S3 path
     * @return string The S3 URL
     */
    function getBaseFile($path)
    {
        return Storage::disk('s3')->url($path);
    }
}

if (!function_exists('deleteFile')) {
    /**
     * Delete a file from S3
     * @deprecated Use delete_from_s3() instead for new code
     * 
     * @param string $path The S3 path
     * @return bool
     */
    function deleteFile($path)
    {
        if (empty($path)) {
            return true;
        }
        
        try {
            return Storage::disk('s3')->delete($path);
        } catch (\Throwable $th) {
            Log::error('S3 delete error: ' . $th->getMessage());
            return false;
        }
    }
}

if (!function_exists('getSize')) {
    function getSize($url)
    {
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }
}

if (!function_exists('deleteFolder')) {
    /**
     * Delete a folder from S3
     * 
     * @param string $path The S3 folder path
     * @return bool
     */
    function deleteFolder($path)
    {
        if (empty($path)) {
            return true;
        }
        
        try {
            return Storage::disk('s3')->deleteDirectory($path);
        } catch (\Throwable $th) {
            Log::error('S3 delete folder error: ' . $th->getMessage());
            return false;
        }
    }
}

// Company Subscription Details
if (!function_exists('subscriptionDetails')) {
    function subscriptionDetails($user_id = null)
    {
        $data = [];
        $data['status'] = false;
        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (\Auth::check()) {
            $user = \Auth::user();
        }

        if (isset($user) && !empty($user)) {
            if ($user->type != 'company' && $user->type != 'super admin') {
                $user = User::find($user->created_by);
            }

            if (!empty($user)) {
                // Subscription system removed - all users have unlimited access
                $data['status'] = true;
                $data['active_plan'] = 1; // Default plan
                $data['active_module'] = activatedModule();
                $data['seeder_run'] = $user->seeder_run ?? 1;
            }
        }
        return $data;
    }
}

if (!function_exists('planCheck')) {
    /**
     * Plan check disabled - subscription system removed
     * Always returns true (unlimited access)
     */
    function planCheck($type = 'User', $id = null)
    {
        // Subscription system removed - all users have unlimited access
        return true;
    }
}

if (!function_exists('checkCoupon')) {
    /**
     * Coupon check disabled - subscription system removed
     * Returns original price
     */
    function checkCoupon($code, $plan_id, $price = 0)
    {
        // Subscription system removed - no coupons
        return $price;
    }
}

if (!function_exists('userCoupon')) {
    /**
     * User coupon disabled - subscription system removed
     */
    function userCoupon($code, $orderID, $user_id = null)
    {
        // Subscription system removed - no coupons
        return null;
    }
}

// if Subscription price is 0 then call this
if (!function_exists('directAssignPlan')) {
    function directAssignPlan($plan_id, $duration, $user_module, $counter, $type, $coupon_code = null, $user_id = null)
    {
        // Subscription system removed - all users have unlimited access
        return ['is_success' => true];
    }
}

if (!function_exists('makeEmailLang')) {
    function makeEmailLang($lang)
    {
        $templates = EmailTemplate::all();
        foreach ($templates as $template) {

            $default_lang  = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();

            $emailTemplateLang              = new EmailTemplateLang();
            $emailTemplateLang->parent_id   = $template->id;
            $emailTemplateLang->lang        = $lang;
            $emailTemplateLang->subject     = $default_lang->subject;
            $emailTemplateLang->content     = $default_lang->content;
            $emailTemplateLang->variables   = $default_lang->variables;
            $emailTemplateLang->save();
        }
    }
}
if (!function_exists('errorRes')) {
    function errorRes($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "error" : $msg;
        $msg_id    = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }
}

if (!function_exists('successRes')) {
    function successRes($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "success" : $msg;
        $json      = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }
}

if (!function_exists('getDeviceType')) {
    function getDeviceType($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {
            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
}

// Get Cache Size
if (!function_exists('cacheSize')) {
    function cacheSize()
    {
        //start for cache clear
        $file_size = 0;
        foreach (\File::allFiles(storage_path('/framework')) as $file) {
            $file_size += $file->getSize();
        }
        $file_size = number_format($file_size / 1000000, 4);

        return $file_size;
    }
}

if (!function_exists('getModuleImg')) {
    function getModuleImg($module)
    {
        $url = url("/Modules/" . $module . '/favicon.png');
        return $url;
    }
}

if (!function_exists('sidebarLogo')) {
    /**
     * Get sidebar logo URL from S3
     * Returns light or dark logo based on user's theme preference
     * 
     * @return string|null The S3 URL or null if not uploaded
     */
    function sidebarLogo()
    {
        $admin_settings = getAdminAllSetting();
        $isDark = (isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on';
        // logo_dark = logo shown in dark mode; logo_light = logo shown in light mode
        $preferredKey = $isDark ? 'logo_dark' : 'logo_light';
        $fallbackKey = $isDark ? 'logo_light' : 'logo_dark';
        
        if (\Auth::check() && (\Auth::user()->type != 'super admin')) {
            $company_settings = getCompanyAllSetting();
            $isDark = (isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on';
            $preferredKey = $isDark ? 'logo_dark' : 'logo_light';
            $fallbackKey = $isDark ? 'logo_light' : 'logo_dark';
            
            // Try company logo first, then fall back to admin logo
            $logo = $company_settings[$preferredKey]
                ?? $admin_settings[$preferredKey]
                ?? $company_settings[$fallbackKey]
                ?? $admin_settings[$fallbackKey]
                ?? null;
        } else {
            $logo = $admin_settings[$preferredKey]
                ?? $admin_settings[$fallbackKey]
                ?? null;
        }
        
        return getLogoUrl($logo, $isDark ? 'dark' : 'light');
    }
}

if (!function_exists('lightLogo')) {
    /**
     * Get light logo URL from S3
     * 
     * @return string|null The S3 URL or null if not uploaded
     */
    function lightLogo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo = $company_settings['logo_light'] ?? null;
        } else {
            $admin_settings = getAdminAllSetting();
            $logo = $admin_settings['logo_light'] ?? null;
        }
        
        return getLogoUrl($logo, 'light');
    }
}

if (!function_exists('darkLogo')) {
    /**
     * Get dark logo URL from S3
     * 
     * @return string|null The S3 URL or null if not uploaded
     */
    function darkLogo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo = $company_settings['logo_dark'] ?? null;
        } else {
            $admin_settings = getAdminAllSetting();
            $logo = $admin_settings['logo_dark'] ?? null;
        }
        
        return getLogoUrl($logo, 'dark');
    }
}

if (!function_exists('currencyFormat')) {
    function currencyFormat($price, $company_id = null, $workspace = null)
    {
        return number_format((float) $price, 2, '.', '');
    }
}

if (!function_exists('currencyFormatWithSym')) {
    function currencyFormatWithSym($price, $company_id = null, $workspace = null)
    {
        return 'R ' . number_format((float) $price, 2, ',', ' ');
    }
}

if (!function_exists('companyDateFormate')) {
    function companyDateFormate($date, $company_id = null, $workspace = null)
    {

        if (!empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $date_formate = !empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-Y';

        return date($date_formate, strtotime($date));
    }
}

if (!function_exists('superCurrencyFormatWithSym')) {
    function superCurrencyFormatWithSym($price)
    {
        return 'R ' . number_format((float) $price, 2, ',', ' ');
    }
}

if (!function_exists('companyDateTimeFormate')) {
    function companyDateTimeFormate($date, $company_id = null, $workspace = null)
    {
        $company_settings = getCompanyAllSetting($company_id, $workspace);
        $date_formate = !empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-Y';
        $time_formate = !empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($date_formate . ' ' . $time_formate, strtotime($date));
    }
}

if (!function_exists('companyTimeFormate')) {
    function companyTimeFormate($time, $company_id = null, $workspace = null)
    {
        if (!empty($company_id) && empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($workspace)) {
            $company_settings = getCompanyAllSetting($company_id, $workspace);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $time_formate = !empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($time_formate, strtotime($time));
    }
}

// ────────────────────────────────────────────────────────────────────────────
// Universal date/time formatting helpers (auto-detect admin vs company context)
// ────────────────────────────────────────────────────────────────────────────

if (!function_exists('getDateFormat')) {
    /**
     * Get the PHP date format string for the current user context.
     * Super-admin → admin settings, everyone else → company settings, guest → admin settings.
     */
    function getDateFormat()
    {
        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                $settings = getAdminAllSetting();
            } else {
                $settings = getCompanyAllSetting();
            }
        } else {
            $settings = getAdminAllSetting();
        }
        return !empty($settings['site_date_format']) ? $settings['site_date_format'] : 'd-m-Y';
    }
}

if (!function_exists('getTimeFormat')) {
    /**
     * Get the PHP time format string for the current user context.
     */
    function getTimeFormat()
    {
        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                $settings = getAdminAllSetting();
            } else {
                $settings = getCompanyAllSetting();
            }
        } else {
            $settings = getAdminAllSetting();
        }
        return !empty($settings['site_time_format']) ? $settings['site_time_format'] : 'H:i';
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a date value according to the current user's date format setting.
     * Accepts Carbon instances, DateTimeInterface, or date strings.
     */
    function formatDate($date)
    {
        if (empty($date)) {
            return '-';
        }
        try {
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
                return $date->format(getDateFormat());
            }
            return \Carbon\Carbon::parse($date)->format(getDateFormat());
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format a datetime value according to the current user's date + time format settings.
     */
    function formatDateTime($date)
    {
        if (empty($date)) {
            return '-';
        }
        try {
            $format = getDateFormat() . ' ' . getTimeFormat();
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
                return $date->format($format);
            }
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format a time value according to the current user's time format setting.
     */
    function formatTime($time)
    {
        if (empty($time)) {
            return '-';
        }
        try {
            if ($time instanceof \Carbon\Carbon || $time instanceof \DateTimeInterface) {
                return $time->format(getTimeFormat());
            }
            return \Carbon\Carbon::parse($time)->format(getTimeFormat());
        } catch (\Exception $e) {
            return $time;
        }
    }
}

if (!function_exists('formatMonthYear')) {
    /**
     * Format a date as "Month Year" (e.g. "February 2026").
     * This is used for salary periods and is not affected by date format setting.
     */
    function formatMonthYear($date)
    {
        if (empty($date)) {
            return '-';
        }
        try {
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
                return $date->format('F Y');
            }
            return \Carbon\Carbon::parse($date)->format('F Y');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatShortMonthYear')) {
    /**
     * Format a date as short "Mon Year" (e.g. "Feb 2026").
     */
    function formatShortMonthYear($date)
    {
        if (empty($date)) {
            return '-';
        }
        try {
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
                return $date->format('M Y');
            }
            return \Carbon\Carbon::parse($date)->format('M Y');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatDayMonth')) {
    /**
     * Format a date as "day month" partial (e.g. "21 Feb").
     * Respects the date format order (day-first vs month-first).
     */
    function formatDayMonth($date)
    {
        if (empty($date)) {
            return '-';
        }
        try {
            $carbon = ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface)
                ? $date
                : \Carbon\Carbon::parse($date);

            $dateFormat = getDateFormat();
            // If the format starts with month (m-d-Y or M d, Y), show "Mon DD"
            if (str_starts_with($dateFormat, 'm') || str_starts_with($dateFormat, 'M')) {
                return $carbon->format('M d');
            }
            // Otherwise show "DD Mon"
            return $carbon->format('d M');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

// module price name
if (!function_exists('modulePriceByName')) {
    function modulePriceByName($module_name)
    {
        $module = Module::find($module_name);
        $data = [];
        $data['monthly_price'] = 0;
        $data['yearly_price'] = 0;

        if (!empty($module)) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);

            $data['monthly_price'] = (isset($json['monthly_price']) && !empty($json['monthly_price'])) ? $json['monthly_price'] : 0;
            $data['yearly_price'] = (isset($json['yearly_price']) && !empty($json['yearly_price'])) ? $json['yearly_price'] : 0;
        }

        return $data;
    }
}

// invoice template Data

if (!function_exists('templateData')) {
    function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $arr['templates'] = [
            "template1" => "Template 1",
            "template2" => "Template 2",
            "template3" => "Template 3",
            "template4" => "Template 4",
            "template5" => "Template 5",
            "template6" => "Template 6",
            "template7" => "Template 7",
            "template8" => "Template 8",
            "template9" => "Template 9",
            "template10" => "Template 10",
        ];
        return $arr;
    }
}
if (!function_exists('annualLeaveCycle')) {
    function annualLeaveCycle()
    {
        $start_date = date('Y-m-d', strtotime(date('Y') . '-01-01 -1 day'));
        $end_date = date('Y-m-d', strtotime(date('Y') . '-12-31 +1 day'));

        $date['start_date'] = $start_date;
        $date['end_date']   = $end_date;

        return $date;
    }
}

// time tracker
if (!function_exists('secondToTime')) {
    function secondToTime($seconds = 0)
    {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        $time = sprintf("%02d:%02d:%02d", $H, $i, $s);
        return $time;
    }
}
