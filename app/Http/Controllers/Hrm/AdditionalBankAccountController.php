<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\AdditionalBankAccount;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdditionalBankAccountController extends Controller
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
        return view('hrm.additional-bank-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'eft_format' => 'required|string',
            'bank' => 'required|string',
            'account_number' => 'required|string|size:16|unique:additional_bank_accounts,account_number',
            'branch_code' => 'required|string|size:6',
            'account_type' => 'required|string',
        ], [
            'employee_name.required' => 'Employee Name is required.',
            'eft_format.required' => 'EFT Format is required.',
            'bank.required' => 'Bank is required.',
            'account_number.required' => 'Account Number is required.',
            'account_number.size' => 'Account Number must be exactly 16 digits.',
            'account_number.unique' => 'This Account Number already exists.',
            'branch_code.required' => 'Branch Code is required.',
            'branch_code.size' => 'Branch Code must be exactly 6 digits.',
            'account_type.required' => 'Account Type is required.',
        ]);

        AdditionalBankAccount::create($request->all());

        return redirect()->route('additional-bank-accounts.create')->with('success', 'Bank Account added successfully.');
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
        $bankaccount = AdditionalBankAccount::findOrFail($id);
        return view('hrm.additional-bank-accounts.edit', compact('bankaccount'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $bankaccount = AdditionalBankAccount::findOrFail($id);

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'eft_format' => 'required|string',
            'bank' => 'required|string',
            'account_number' => 'required|string|size:16|unique:additional_bank_accounts,account_number,' . $id,
            'branch_code' => 'required|string|size:6',
            'account_type' => 'required|string',
        ]);

        $bankaccount->update($request->all());

        return redirect()->route('additional-bank-accounts.edit', $id)->with('success', 'Bank Account updated successfully.');
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
