<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CompanySetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CompanySettingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $settings = CompanySetting::all();
        return view('hrm.company-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.company-settings.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
       
        $request->validate([
            'minimum_wage' => 'required|string',
            'minimum_wage_monthly' => 'nullable|numeric',
            'minimum_wage_normal_rate' => 'nullable|numeric',
            'economic_zone' => 'nullable|string',
            'effective_from' => 'required|date',
        ]);
    
        // Storing Data
        CompanySetting::create([
            'minimum_wage' => $request->minimum_wage,
            'minimum_wage_monthly' => $request->minimum_wage_monthly,
            'minimum_wage_normal_rate' => $request->minimum_wage_normal_rate,
            'special_economic_zone' => $request->has('special_economic_zone') ? 'yes' : 'no',
            'economic_zone' => $request->economic_zone,
            'effective_from' => $request->effective_from,
        ]);
    
        return redirect()->back()->with('success', 'Company Settings Saved Successfully!');
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
        $companySetting = CompanySetting::findOrFail($id);
        return view('hrm.company-settings.edit', compact('companySetting'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $companySetting = CompanySetting::findOrFail($id);
        $request->validate([
            'minimum_wage' => 'required',
            'minimum_wage_monthly' => 'required|numeric',
            'minimum_wage_normal_rate' => 'required|numeric',
            'economic_zone' => 'required',
            'effective_from' => 'required|date',
        ]);

        $companySetting->update([
            'minimum_wage' => $request->minimum_wage,
            'minimum_wage_monthly' => $request->minimum_wage_monthly,
            'minimum_wage_normal_rate' => $request->minimum_wage_normal_rate,
            'special_economic_zone' => $request->has('special_economic_zone'),
            'economic_zone' => $request->economic_zone,
            'effective_from' => $request->effective_from,
        ]);

        return redirect()->route('company-settings.edit',$id)->with('success', 'Company setting updated successfully.');
    }

   

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
