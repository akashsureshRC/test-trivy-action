<?php

namespace App\Http\Controllers;

use App\Events\DefaultData;
use App\Events\DestroyWorkSpace;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WorkSpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->isAbleTo('workspace create'))
        {
            return view('workspace.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('workspace create'))
        {
            if(Auth::user()->type != 'super admin'){
                $canUse=  planCheck('Workspace',Auth::user()->id);
                if($canUse == false)
                {
                    return redirect()->back()->with('error','You have maxed out the total number of Workspace allowed on your current plan');
                }
            }
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            try {
                $workspace = new WorkSpace();

                $workspace->name = $request->name;
                $workspace->created_by = \Auth::user()->id;
                $workspace->save();

                $msg = __('Workspace create successfully!');

                $user = \Auth::user();
                $user->active_workspace =$workspace->id;
                $user->save();


                User::CompanySetting(\Auth::user()->id,$workspace->id);
                if(!empty(\Auth::user()->active_module))
                {
                    event(new DefaultData(\Auth::user()->id,$workspace->id,\Auth::user()->active_module));
                }


                // return redirect()->route('dashboard')->with('success',$msg);
                return redirect()->back()->with('success',$msg);



            }catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkSpace  $workSpace
     * @return \Illuminate\Http\Response
     */
    public function show(WorkSpace $workSpace)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkSpace  $workSpace
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->isAbleTo('workspace edit'))
        {
            $workSpace = WorkSpace::find($id);

            return view('workspace.edit',compact('workSpace'));
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
     * @param  \App\Models\WorkSpace  $workSpace
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if(Auth::user()->isAbleTo('workspace edit'))
        {

            $workSpace = WorkSpace::find($id);

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
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

            $workSpace->name = $request->name;
            $workSpace->slug = $request->slug;
            $workSpace->save();

            if($request->ajax()) {
                return response()->json(['success' => __('Workspace updated successfully!')]);
            }
            return redirect()->back()->with('success', __('Workspace updated successfully!'));

        }
        else
        {
            if($request->ajax()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkSpace  $workSpace
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkSpace $workSpace,$workspace_id)
    {
        if(Auth::user()->isAbleTo('workspace delete'))
        {
            $objUser   = \Auth::user();
            $workspace = Workspace::find($workspace_id);

            if($workspace && $workspace->created_by == $objUser->id)
            {
                $other_workspac = Workspace::where('created_by',$objUser->id)->where('is_disable',1)->where('id','!=',$workspace->id)->first();
                if($other_workspac)
                {
                    if(!empty($other_workspac))
                    {
                        $objUser->active_workspace = $other_workspac->id;
                        $objUser->save();
                    }
                     // first parameter workspace
                    event(new DestroyWorkSpace($workspace));

                    $workspace->delete();

                    return redirect()->route('dashboard')->with('success', __('Workspace Deleted Successfully!'));
                }
                return redirect()->route('dashboard')->with('errors', __("You can't delete Workspace! because your other workspaces are disabled "));
            }
            else
            {
                return redirect()->route('dashboard')->with('errors', __("You can't delete Workspace!"));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function change($workspace_id)
    {
        $check = WorkSpace::find($workspace_id);
        if (empty($check)) {
            return redirect()->route('dashboard')->with('error', "Workspace not found.");
        }

        if ((int) $check->created_by !== (int) creatorId()) {
            return redirect()->route('dashboard')->with('error', __('Permission denied.'));
        }

        $user = Auth::user();
        $user->active_workspace = $workspace_id;
        $user->save();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'User Workspace change successfully.');
    }

    public function workspaceCheck(Request $request)
    {
        if(isset($request->slug))
        {
             $workSpace = WorkSpace::where('slug',$request->slug)->where('id','!=',$request->workspace)->exists();
             if(!$workSpace)
             {
                return response()->json(['success' => __('This Slug is Available.')]);
             }
        }
        return response()->json(['error' => __('This Slug Not Available.')]);
    }
}
