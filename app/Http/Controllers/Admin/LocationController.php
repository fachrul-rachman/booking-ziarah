<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadLocationRequest;
use App\Jobs\ImportLocationsJob;
use App\Models\Location;
use App\Models\LocationImport;
use App\Services\LocationImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::query()
            ->with(['zones.lots'])
            ->orderBy('name')
            ->get();

        $latestImport = null;
        $latestImportId = session()->get('location_import_id');
        if ($latestImportId) {
            $latestImport = LocationImport::query()->find($latestImportId);
        }
        if (!$latestImport) {
            $latestImport = LocationImport::query()
                ->orderByDesc('id')
                ->first();
        }

        return view('admin.locations.index', [
            'locations' => $locations,
            'importPreview' => session()->get('location_import_preview'),
            'latestImport' => $latestImport,
        ]);
    }

    public function upload(UploadLocationRequest $request, LocationImportService $importService): RedirectResponse
    {
        try {
            if ($request->boolean('confirm')) {
                $preview = session()->get('location_import_preview');
                if (!is_array($preview) || empty($preview['path'])) {
                    return back()->with('toast', [
                        'type' => 'error',
                        'message' => 'File import tidak ditemukan, upload ulang.',
                    ]);
                }

                session()->forget('location_import_preview');

                $import = LocationImport::query()->create([
                    'user_id' => $request->user()?->id,
                    'path' => $preview['path'],
                    'status' => 'queued',
                    'total_rows' => 0,
                    'processed_rows' => 0,
                ]);

                session()->put('location_import_id', $import->id);

                ImportLocationsJob::dispatch($import->id)->onQueue('location-import');

                return redirect()
                    ->route('admin.locations.index')
                    ->with('toast', [
                        'type' => 'success',
                        'message' => 'Import sedang diproses di background (queue).',
                    ]);
            }

            if (!$request->hasFile('file')) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => 'File tidak terbaca (cek ukuran file dan coba upload ulang).',
                ]);
            }

            $path = $request->file('file')->storeAs(
                'tmp/location-import',
                now()->format('YmdHis').'-'.uniqid('', true).'.'.$request->file('file')->getClientOriginalExtension(),
            );

            $preview = $importService->previewFromPath($path);
            $preview['path'] = $path;

            session()->put('location_import_preview', $preview);

            return back();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Preview import gagal, cek format file Excel.',
            ]);
        }
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Lokasi berhasil dihapus.',
        ]);
    }

    public function importStatus(LocationImport $import): JsonResponse
    {
        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'total_rows' => (int) $import->total_rows,
            'processed_rows' => (int) $import->processed_rows,
            'created_lots' => (int) $import->created_lots,
            'errors_count' => (int) $import->errors_count,
            'errors' => $import->errors ?? [],
            'started_at' => optional($import->started_at)->toISOString(),
            'finished_at' => optional($import->finished_at)->toISOString(),
        ]);
    }
}
