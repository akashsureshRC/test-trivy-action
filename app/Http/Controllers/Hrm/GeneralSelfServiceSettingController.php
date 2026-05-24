<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\GeneralSelfServiceSetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GeneralSelfServiceSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $setting = GeneralSelfServiceSetting::first();
        return view('hrm.general-self-service-settings', compact('setting'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.general-self-service-settings.create');
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            // You can add validation rules here if needed
        ]);

        $data = $request->only([
            'auto_enable',
            'attach_payslips',
            'enable_password_protection',
            'allow_tax_certificates',
            'attach_certificates',
            'disable_leave_requests',
            'disable_info_requests',
        ]);

        foreach ($data as $key => $value) {
            $data[$key] = $request->has($key);
        }

        GeneralSelfServiceSetting::create($data);

        return redirect()->route('general-self-service-settings.create')->with('success', 'Settings created successfully.');
    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $setting = GeneralSelfServiceSetting::findOrFail($id);
        return view('hrm.general-self-service-settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'auto_enable',
            'attach_payslips',
            'enable_password_protection',
            'allow_tax_certificates',
            'attach_certificates',
            'disable_leave_requests',
            'disable_info_requests',
        ]);

        foreach ($data as $key => $value) {
            $data[$key] = $request->has($key);
        }

        $setting = GeneralSelfServiceSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            GeneralSelfServiceSetting::create($data);
        }

        return redirect()->route('self-service-settings')->with('success', 'Settings saved successfully.');
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $setting = GeneralSelfServiceSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('general-self-service-settings.destroy')->with('success', 'Settings deleted.');
    }
}
