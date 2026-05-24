<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\AddGarnishee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AddGarnisheeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.add-garnishees.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_name' => 'required|string|max:255',
            'bank' => 'required|string',
            'account_number' => 'required|string|size:16|unique:add_garnishees,account_number',
            'branch_code' => 'required|string|size:6',
            'account_type' => 'required|string',
            'eft_payment_type' => 'nullable|string',
            'your_reference' => 'nullable|string|max:255',
            'beneficiary_reference' => 'nullable|string|max:255',
        ]);
    
        $validatedData['include_eftexport'] = $request->has('include_eftexport') ? 1 : 0;
    
        AddGarnishee::create($validatedData);
    
        return redirect()->route('add-garnishees.create')->with('success', 'Bank Account added successfully.');
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
        $addGarnishee =  AddGarnishee::findOrFail($id);
        return view('hrm.add-garnishees.edit', compact('addGarnishee'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $addGarnishee = AddGarnishee::findOrFail($id);

    $validatedData = $request->validate([
        'employee_name' => 'required|string|max:255',
        'bank' => 'required|string',
        'account_number' => 'required|string|size:16|unique:add_garnishees,account_number,' . $id,
        'branch_code' => 'required|string|size:6',
        'account_type' => 'required|string',
       
        'eft_payment_type' => 'nullable|string',
        'your_reference' => 'nullable|string|max:255',
        'beneficiary_reference' => 'nullable|string|max:255',
    ]);

    $validatedData['include_eftexport'] = $request->has('include_eftexport') ? 1 : 0;

    $addGarnishee->update($validatedData);

    return redirect()->route('add-garnishees.edit', $id)->with('success', 'Bank Account updated successfully.');
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

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_name'          => 'required|string|max:255',
            'bank'                   => 'required|string',
            'account_number'         => 'required|string|size:16|unique:add_garnishees,account_number',
            'branch_code'            => 'required|string|size:6',
            'account_type'           => 'required|string',
            'eft_payment_type'       => 'nullable|string',
            'your_reference'         => 'nullable|string|max:255',
            'beneficiary_reference'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_name'          => 'required|string|max:255',
            'bank'                   => 'required|string',
            'account_number'         => 'required|string|size:16|unique:add_garnishees,account_number,' . $id,
            'branch_code'            => 'required|string|size:6',
            'account_type'           => 'required|string',
            'eft_payment_type'       => 'nullable|string',
            'your_reference'         => 'nullable|string|max:255',
            'beneficiary_reference'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
