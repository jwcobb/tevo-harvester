<?php namespace TevoHarvester\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use TevoHarvester\Jobs\UpdatePerformerPopularityJob;
use TevoHarvester\Jobs\UpdateResourceJob;
use TevoHarvester\Tevo\Category;
use TevoHarvester\Tevo\Harvest;

/**
 * Class ResourceController
 *
 * @package TevoHarvester\Http\Controllers
 */
class ResourceController extends Controller
{

    /**
     * @var array
     */
    public $settings;


    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Responds to requests to GET /{resource}
     *
     * @param $resource
     *
     * @return \Illuminate\View\View
     */
    public function index($resource)
    {
        try {
            $harvests = Harvest::where('resource', $resource)->orderBy('action', 'asc')->get();
        } catch (\Exception $e) {
            abort(404, 'Error looking up Harvests defined for ' . ucwords($resource) . '.');
        }

        if ($harvests->isEmpty()) {
            abort(404, 'There are no Harvests defined for ' . ucwords($resource) . '.');
        }

        return view('resource-index', [
            'pageTitle' => ucwords($resource) . ' | TEvo Harvester',
            'harvests'  => $harvests,
        ]);
    }


    /**
     * Responds to requests to GET /{resource}
     *
     *
     * @param $resource
     * @param $action
     *
     * @return \Illuminate\View\View
     */
    public function show($resource, $action)
    {
        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'Error looking up Harvests defined for ' . ucwords($action . ' ' . $resource) . '.');
        }

        return view('resource-show', [
            'pageTitle' => ucwords($action . ' ' . $resource) . ' | TEvo Harvester',
            'harvest'  => $harvest,
        ]);
    }


    /**
     * Make changes such as the scheduled frequency to the Harvest entry.
     *
     * @param $resource
     * @param $action
     *
     * @return \Illuminate\View\View
     */
    public function edit($resource, $action)
    {
        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'There is no existing action for updating ' . ucwords($action) . ' ' . ucwords($resource) . '.');
        }

        return view('resource-edit', [
            'pageTitle' => 'Edit ' . ucwords($resource) . ' ' . ucwords($action) . ' Settings | TEvo Harvester',
            'harvest'   => $harvest,
        ]);
    }


    /**
     * Save changes to the Harvest entry.
     *
     * @param Request $request
     * @param         $resource
     * @param         $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $resource, $action)
    {
        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'There is no existing action for updating ' . ucwords($action) . ' ' . ucwords($resource) . '.');
        }

        $harvest->scheduler_frequency_method = $request->input('scheduler_frequency_method', 'daily');
        $harvest->ping_before_url = $request->input('ping_before_url', null);
        $harvest->then_ping_url = $request->input('then_ping_url', null);
        $harvest->save();

        return redirect()->route('dashboard')->with('status', 'Your changes to ' . ucwords($action) . ' ' . ucwords($resource) . ' were successful.');
    }


    /**
     * Update data for a specific $resource and $action with any
     * changes since the last time this was run.
     *
     * @param $resource
     * @param $action
     *
     * @return \Illuminate\View\View
     */
    public function harvest($resource, $action)
    {
        ini_set('max_execution_time', 2400);
        set_time_limit(2400);

        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'There is no existing action for updating ' . ucwords($action . ' ' . $resource) . '.');
        }

        $this->settings = [
            'startPage' => (int)Input::get('startPage', 1),
            'perPage'   => (int)Input::get('perPage', 100),
            'lastRun'   => $harvest->last_run_at,
        ];

        // If a lastRun was given use that
        // OR if this has never been run before use 2001-01-01
        if ((boolean)Input::get('lastRun', false) !== false) {
            $this->settings['lastRun'] = new Carbon(Input::get('lastRun'));
        } elseif (is_null($this->settings['lastRun'])) {
            $this->settings['lastRun'] = new Carbon('2001-01-01');
        }

        /**
         * Convert $lastRun to UTC because the API currently ignores the time if it is
         * not specified as UTC. This is not expected behavior and should be fixed soon.
         */
        $this->settings['lastRun']->setTimezone(new \DateTimeZone('UTC'));

        $job = new UpdateResourceJob($harvest, $this->settings);

        $this->dispatch($job);

        return view('resource-harvest', [
            'pageTitle' => ucwords($resource) . ' | ' . ucwords($action) . ' | TEvo Harvester',
            'job'       => $job,
        ]);
    }


    /**
     * Refresh ALL current data by using a really old date as your $lastRun
     * the API calls. This works the same as if you had never run
     * Harvester before for this $resource and $action.
     *
     * @param Request $request
     * @param         $resource
     * @param         $action
     *
     * @return \Illuminate\View\View
     */
    public function refresh(Request $request, $resource, $action)
    {
        // Use 2001-01-01 as a default date for a full refresh
        // by pretending it was sent as the lastRun parameter.
        $request->merge(['lastRun' => '2001-01-01']);

        return $this->harvest($resource, $action);
    }


    /**
     * @return \Illuminate\View\View
     */
    public function performersPopularity()
    {
        try {
            $harvest = Harvest::where('resource', 'performers')->where('action', 'popularity')->firstOrFail();
        } catch (\Exception $e) {
            abort(404, 'There is no existing action for updating Performers Popularity.');
        }

        /**
         * Get all the categories and then loop through them creating an
         * UpdatePerformerPopularityJob for each one.
         */
        try {
            $categories = Category::active()->orderBy('id')->get();
        } catch (\Exception $e) {
            abort(404, 'There are no categories yet. Please ensure you have run the Active Categories job.');
        }

        // Get the last category->id so we can later detect that the Job for that
        // category->id has completed in order to know to fire the ResourceUpdateWasCompleted Event
        $last_category_id = $categories->last()->id;

        foreach ($categories as $category) {
            $job = new UpdatePerformerPopularityJob($harvest, $category->id, $last_category_id);

            $this->dispatch($job);
        }

        return view('resource', [
            'pageTitle' => 'Performers | Popularity | TEvo Harvester',
            'job'       => $job,
        ]);
    }
}
