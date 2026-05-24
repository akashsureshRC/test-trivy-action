<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\PrimaryBankAccount;
class PrimaryBankAccountController extends Controller
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
        return view('hrm.primary-bank-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'eft_format' => 'required|string',
            'bank' => 'required|string',
            'account_number' => 'required|numeric|digits_between:8,16|unique:primary_bank_accounts,account_number',
            'branch_code' => 'required|numeric|digits:6',
            'account_type' => 'required|string',
        ], [
            'eft_format.required' => 'EFT Format is required.',
            'bank.required' => 'Bank selection is required.',
            'account_number.required' => 'Account number is required.',
            'account_number.numeric' => 'Account number must be a number.',
            'account_number.digits_between' => 'Account number must be between 8 to 16 digits.',
            'account_number.unique' => 'This account number is already registered.',
            'branch_code.required' => 'Branch code is required.',
            'branch_code.numeric' => 'Branch code must be a number.',
            'branch_code.digits' => 'Branch code must be exactly 6 digits.',
            'account_type.required' => 'Account type is required.',
        ]);

        PrimaryBankAccount::create($request->all());

        return redirect()->route('primary-bank-accounts.create')->with('success', 'Primary Bank Account saved successfully.');
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
        $bankAccount = PrimaryBankAccount::findOrFail($id);
        return view('hrm.primary-bank-accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $bankAccount = PrimaryBankAccount::findOrFail($id);

        $request->validate([
            'eft_format' => 'required|string',
            'bank' => 'required|string',
            'account_number' => 'required|numeric|digits_between:8,16|unique:primary_bank_accounts,account_number,' . $id,
            'branch_code' => 'required|numeric|digits:6',
            'account_type' => 'required|string',
        ], [
            'eft_format.required' => 'EFT Format is required.',
            'bank.required' => 'Bank selection is required.',
            'account_number.required' => 'Account number is required.',
            'account_number.numeric' => 'Account number must be a number.',
            'account_number.digits_between' => 'Account number must be between 8 to 16 digits.',
            'account_number.unique' => 'This account number is already registered.',
            'branch_code.required' => 'Branch code is required.',
            'branch_code.numeric' => 'Branch code must be a number.',
            'branch_code.digits' => 'Branch code must be exactly 6 digits.',
            'account_type.required' => 'Account type is required.',
        ]);

        $bankAccount->update($request->all());

        return redirect()->route('primary-bank-accounts.edit',$id)->with('success', 'Primary Bank Account updated successfully.');
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
