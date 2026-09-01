<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Presentation\Http\Controllers;

use App\Domains\Seller\Planner\Application\Services\ScheduleService;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = (string) $user->id;
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');
        $storeId = $isAdmin && $request->filled('store_id') ? (int) $request->query('store_id') : null;

        $schedules = $this->service->getAll(
            $userId,
            $request->only(['type', 'priority', 'is_completed', 'from_date', 'to_date']),
            min(100, max(1, (int) $request->query('per_page', 20))),
            $storeId,
            $isAdmin
        );

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:task,meeting,reminder,shipment,restock'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'color' => ['nullable', 'string', 'max:20'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'is_all_day' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['store_id'] = $request->user()->store->id ?? null;
        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;
        $validated['is_completed'] = false;

        $schedule = $this->service->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dibuat.',
            'data' => [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'date' => $schedule->date,
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        try {
            $schedule = $this->service->requireEditable($id, (string) $user->id, $user->hasRole('admin') || $user->hasRole('super_admin'));
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => $schedule->description,
                'type' => $schedule->type,
                'priority' => $schedule->priority,
                'color' => $schedule->color,
                'date' => $schedule->date,
                'start_time' => $schedule->startTime,
                'end_time' => $schedule->endTime,
                'is_all_day' => $schedule->isAllDay,
                'is_completed' => $schedule->isCompleted,
                'completed_at' => $schedule->completedAt,
                'duration_minutes' => $schedule->getDurationInMinutes(),
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['sometimes', 'string', 'in:task,meeting,reminder,shipment,restock'],
            'priority' => ['sometimes', 'string', 'in:low,normal,high,urgent'],
            'color' => ['nullable', 'string', 'max:20'],
            'date' => ['sometimes', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'is_all_day' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $validated['updated_by'] = $request->user()->id;

        $user = $request->user();
        try {
            $this->service->requireEditable($id, (string) $user->id, $user->hasRole('admin') || $user->hasRole('super_admin'));
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $schedule = $this->service->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => [
                'id' => $schedule->id,
                'title' => $schedule->title,
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        try {
            $this->service->requireEditable($id, (string) $user->id, $user->hasRole('admin') || $user->hasRole('super_admin'));
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        try {
            $this->service->requireEditable($id, (string) $user->id, $user->hasRole('admin') || $user->hasRole('super_admin'));
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $schedule = $this->service->markComplete($id);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal ditandai selesai.',
            'data' => [
                'id' => $schedule->id,
                'is_completed' => $schedule->isCompleted,
                'completed_at' => $schedule->completedAt,
            ],
        ]);
    }

    public function grid(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = (string) $user->id;
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $storeId = $isAdmin && $request->filled('store_id') ? (int) $request->query('store_id') : null;

        $grid = $this->service->getGrid($userId, $year, $month, $storeId, $isAdmin);

        return response()->json([
            'success' => true,
            'data' => $grid,
        ]);
    }

    public function export(Request $request): Response
    {
        $userId = (string) $request->user()->id;
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $csv = $this->service->exportToCsv($userId, $fromDate, $toDate);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="jadwal-export.csv"',
        ]);
    }
}
