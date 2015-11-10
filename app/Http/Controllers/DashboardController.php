<?php namespace TevoHarvester\Http\Controllers;

use TevoHarvester\Tevo\Harvest;

class DashboardController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Welcome Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders the "marketing page" for the application and
    | is configured to only allow guests. Like most of the other sample
    | controllers, you are free to modify or remove it as you desire.
    |
    */

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Show the application welcome screen to the user.
     *
     * Here we grab all the ”harvests” from the `harvests` table
     * and use the data within to run the jobs to update.
     *
     * @return Response
     */
    public function index()
    {
        $harvests = Harvest::orderBy('resource', 'asc')->orderBy('action', 'asc')->get();
        $harvestsGroupedByResource = $harvests->groupBy('resource');

        $resources = $harvests->unique('resource');

        return view('dashboard', ['harvests' => $harvestsGroupedByResource, 'resources' => $resources]);
    }

}
