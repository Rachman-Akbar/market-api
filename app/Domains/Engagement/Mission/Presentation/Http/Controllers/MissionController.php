<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Presentation\Http\Controllers;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Engagement\Mission\Presentation\Http\Requests\MissionRequest;
use App\Domains\Engagement\Mission\Presentation\Http\Resources\MissionResource;
use App\Domains\Shared\Presentation\Http\Concerns\ResolvesActiveRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MissionController extends Controller
{
    use ResolvesActiveRole;
    public function __construct(private MissionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $this->hasActiveRole($request, 'admin');
        $rows = $this->service->paginate(
            $request->only(['event_type', 'is_active', 'search']),
            min(100, max(1, (int) $request->query('per_page', 20))),
            $isAdmin
        );

        return MissionResource::collection($rows)->additional(['success' => true])->response();
    }

    public function userMissions(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->userMissions((string) $request->user()->id)]);
    }

    public function store(MissionRequest $request): JsonResponse
    {
        $row = $this->service->save($request->validated(), null);

        return (new MissionResource($row))->additional(['success' => true, 'message' => 'Misi berhasil dibuat.'])->response()->setStatusCode(201);
    }

    public function update(MissionRequest $request, int $id): JsonResponse
    {
        $row = $this->service->save($request->validated(), $id);

        return (new MissionResource($row))->additional(['success' => true, 'message' => 'Misi berhasil diperbarui.'])->response();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        abort_unless($this->hasActiveRole($request, 'admin'), 403, 'Hanya admin yang dapat menghapus misi.');
        $this->service->delete($id);

        return response()->json(['success' => true, 'message' => 'Misi berhasil dihapus.']);
    }

    public function reportEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => ['required', 'string', 'max:100'],
            'value' => ['required', 'integer', 'min:1'],
        ]);

        $userId = (string) $request->user()->id;
        $eventType = $validated['event_type'];
        $value = $validated['value'];

        $this->service->recordEvent($userId, $eventType, $value);

        $missions = $this->service->userMissions($userId);
        $updatedCount = 0;
        $rewards = [];

        foreach ($missions as $mission) {
            if (in_array($mission['status'], ['completed', 'rewarded'], true)) {
                $updatedCount++;
            }
            if (isset($mission['voucher']) && $mission['voucher'] && $mission['status'] === 'rewarded') {
                $rewards[] = $mission['voucher']['name'];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'missions_updated' => $updatedCount,
                'rewards_earned' => $rewards,
            ],
        ]);
    }
}
