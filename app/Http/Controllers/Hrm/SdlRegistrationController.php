<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\SdlRegistration;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SdlRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
       // $sdlregistrations = \App\Models\Hrm\SdlRegistration::all();
       // return view('hrm.sdl-registrations.index', compact('sdlregistrations'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.sdl-registrations.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'sdl_registration' => 'required',
            'effective_from' => 'required|date',
        ], [
            'sdl_registration.required' => 'DL Registration is required.',
            'effective_from.required' => 'Effective From date is required.',
        ]);

        SdlRegistration::create($request->all());
        return redirect()->route('sdl-registrations.create')->with('success', 'SDL Registration added successfully.');
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
        $sdlRegistration = SdlRegistration::findOrFail($id);
        return view('hrm.sdl-registrations.edit', compact('sdlRegistration'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $sdlRegistration = SdlRegistration::findOrFail($id);
        $request->validate([
            'sdl_registration' => 'required',
            'effective_from' => 'required|date',
        ]);

        $sdlRegistration->update($request->all());
        return redirect()->route('sdl-registrations.edit',$id)->with('success', 'SDL Registration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //$sdlRegistration->delete();
       // return redirect()->route('sdl.index')->with('success', 'SDL Registration deleted successfully.');
    }
}
