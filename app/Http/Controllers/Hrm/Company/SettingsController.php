<?php
// This file use for handle company setting page

namespace App\Http\Controllers\Hrm\Company;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\ExperienceCertificate;
use App\Models\Hrm\NOC;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        if (\Auth::check() && moduleIsActive('Hrm')) {
            $currentParams = \Route::current()->parameters('noclangs');
            $active_module = explode(',', \Auth::user()->active_module);
            $dependency = explode(',', 'Hrm');
            if (!empty(array_intersect($dependency, $active_module))) {
                if (request()->get('explangs')) {
                    $explang = request()->get('explangs');
                } else {
                    $explang = "en";
                }
                if (request()->get('noclangs')) {
                    $noclang = request()->get('noclangs');
                } else {
                    $noclang = "en";
                }
                if (moduleIsActive('Recruitment')) {
                    if (request()->get('offerlangs')) {
                        $offerlang = request()->get('offerlangs');
                    } else {
                        $offerlang = "en";
                    }
                    //offer letter
                    $Offerletter = \Modules\Recruitment\Entities\OfferLetter::all();
                    $currOfferletterLang = \Modules\Recruitment\Entities\OfferLetter::where('created_by', \Auth::user()->id)->where('lang', $offerlang)->where('workspace', getActiveWorkspace())->first();
                } else {
                    $offerlang = "en";
                    $Offerletter = '';
                    $currOfferletterLang = '';
                }
                //Experience Certificate
                $experience_certificate = ExperienceCertificate::all();
                $curr_exp_cetificate_Lang = ExperienceCertificate::where('created_by',  \Auth::user()->id)->where('lang', $explang)->where('workspace', getActiveWorkspace())->first();
                //NOC
                $noc_certificate = NOC::all();
                $currnocLang = NOC::where('created_by',  \Auth::user()->id)->where('lang', $noclang)->where('workspace', getActiveWorkspace())->first();
            }
            return view('hrm.company.settings.index', compact('settings', 'explang', 'noclang', 'experience_certificate', 'curr_exp_cetificate_Lang', 'noc_certificate', 'currnocLang', 'offerlang', 'Offerletter', 'currOfferletterLang'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
}
