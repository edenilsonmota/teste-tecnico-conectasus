<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $patients = Patient::query()
            ->with('healthUnit:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return response()->json($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->validated());

        return response()->json(
            $patient->load('healthUnit:id,name'),
            Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): JsonResponse
    {
        return response()->json($patient->load('healthUnit:id,name'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());

        return response()->json($patient->load('healthUnit:id,name'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient): Response
    {
        $patient->delete();

        return response()->noContent();
    }
}
