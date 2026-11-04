<?php
namespace App\Http\Controllers;

use App\DTOs\RiderVerificationDTO;
use App\Http\Requests\RiderRequest;
use App\Models\Rider;
use App\Services\RiderService;
use Illuminate\Support\Facades\Request;

class RiderController extends Controller
{
    public function __construct(
        protected RiderService $riderService,
        protected RiderVerificationDTO $payloadDto
    ) {}

    public function index()
    {
        $riders = Rider::query()->latest()->paginate(10);

        return response()->json([
            'message' => 'Riders retrieved successfully',
            'data'    => $riders,
        ], 200);
    }

    public function show($id)
    {
        $rider = Rider::find($id);

        if (! $rider) {
            return response()->json(['message' => 'Rider not found'], 404);
        }

        return response()->json([
            'message' => 'Rider retrieved successfully',
            'data'    => $rider,
        ], 200);
    }

    public function store(RiderRequest $request)
    {

        $user = auth()->user();

        if ($user->rider()->exists()) {
            return response()->json([
                'message' => 'You already have a rider profile.',
            ], 409);
        }

        $validatedData = $request->validated();
        $documentPath  = $request->hasFile('documents')
            ? $request->file('documents')->store('rider/verification', 'public')
            : null;

        $rider = $this->riderService->create(
            $user,
            $this->riderService->buildPayload($validatedData, $documentPath),
            $this->payloadDto
        );

        return response()->json([
            'message' => 'Rider created successfully',
            'data'    => $rider,
        ], 201);
    }

    public function online(Request $request)
    {
        $validatedData = $request->validate([
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $user  = auth()->user();
        $rider = $user->rider;

        if (! $user || $user->role !== 'rider') {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }
        if (! $rider ) {
            return response()->json([
                'message' => 'Rider profile not found for the authenticated user.',
            ], 404);
        }

        if ($rider->approval_status !== 'approved') {
            return response()->json([
                'message' => 'Your rider account is not approved yet.',
            ], 403);
        }

        // Toggle the current state
        $rider->is_online = ! $rider->is_online;

        if (!$rider->is_online ) {
            $rider->latitude            = $validatedData['latitude'];
            $rider->longitude           = $validatedData['longitude'];
            $rider->is_available        = true;
            $rider->location_updated_at = now();
            $rider->is_online = true;
        } else {
            $rider->is_available = false;
            $rider->is_online = false;
        }

        $rider->save();

        return response()->json([
            'message' => $rider->is_online
                ? 'Rider is now online'
                : 'Rider is now offline',

            'data'    => $rider,
        ], 200);
    }

    public function locationUpdate(Request $request)
    {
                $validatedData = $request->validate([
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $user  = auth()->user();
        $rider = $user->rider;

        if (! $user || $user->role !== 'rider' || $user->id !== $rider->user_id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        if ($rider->approval_status !== 'approved') {
            return response()->json([
                'message' => 'Your rider account is not approved yet.',
            ], 403);
        }

        if (! $rider->is_online) {
            return response()->json([
                'message' => 'You must be online to update your location.',
            ], 403);
        }


        $rider->latitude            = $validatedData['latitude'];
        $rider->longitude           = $validatedData['longitude'];
        $rider->location_updated_at = now();
        $rider->save();

        return response()->json([
            'message' => 'Rider location updated successfully',
            'data'    => $rider,
        ], 200);

    }


}
