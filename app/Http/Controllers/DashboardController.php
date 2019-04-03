<?php namespace App\Http\Controllers;

use App\Tevo\Harvest;
use Illuminate\Support\Facades\App;

class DashboardController extends Controller
{

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

        $laravelMinorVersion = preg_replace('/(\d\.\d+).+/', '$1', App::VERSION());

        return view('dashboard',
            ['harvests' => $harvestsGroupedByResource, 'resources' => $resources, 'laravelMinorVersion' => $laravelMinorVersion]);
    }
}
