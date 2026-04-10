<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\AgentRepositoryInterface;

class AgentController extends Controller
{
    protected $agentRepository;

    public function __construct(AgentRepositoryInterface $agentRepository)
    {
        $this->agentRepository = $agentRepository;
        $this->middleware('auth:sanctum')->except(['index', 'show', 'search', 'related']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $sortBy = $request->input('sort_by', 'agency_name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $perPage = $request->input('per_page', 3);
        $agents = $this->agentRepository->all($filters, [$sortBy => $sortDirection], $perPage);

        return AgentResource::collection($agents);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        $agent->load(['properties', 'user', 'properties.city']);
        return new AgentResource($agent);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        //
    }
}
