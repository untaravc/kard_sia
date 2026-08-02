<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected $ownerOptions = [
        'departemen_kardiologi_dan_kedokteran_vaskular',
        'program_studi_jantung_dan_pembuluh_darah',
        'rumah_sakit_sardjito',
        'ppds_jantung_dan_pembuluh_darah',
        'pribadi',
    ];

    protected $categoryOptions = [
        'elektronik',
        'furnitur',
        'alat_tulis_kantor',
        'kendaraan',
        'bangunan',
        'alat_pembelajaran',
        'aset_maya',
        'konsumsi',
    ];

    protected $statusOptions = [
        'baik',
        'rusak_ringan',
        'rusak_berat',
        'dalam_perbaikan',
        'hilang',
        'dijual',
        'dipinjam',
        'dipindahkan',
    ];

    public function properties()
    {
        return response()->json([
            'success' => true,
            'text' => 'Retrieve Asset Properties Success',
            'result' => [
                'owner' => $this->buildOptions($this->ownerOptions),
                'category' => $this->buildOptions($this->categoryOptions),
                'status' => $this->buildOptions($this->statusOptions),
            ],
        ]);
    }

    protected function buildOptions(array $values)
    {
        return collect($values)->map(function ($value) {
            return [
                'value' => $value,
                'label' => ucwords(str_replace('_', ' ', $value)),
            ];
        })->values();
    }

    public function index(Request $request)
    {
        $dataContent = Asset::query()->latest();
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Assets Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $asset = Asset::create($data);
        $asset->number = $this->generateNumber($asset);
        $asset->save();

        return response()->json([
            'success' => true,
            'text' => 'Create Asset Success',
            'result' => $asset,
        ]);
    }

    protected function generateNumber(Asset $asset)
    {
        $initial = collect(explode('_', (string) $asset->category))
            ->filter()
            ->map(function ($word) {
                return strtoupper(substr($word, 0, 1));
            })
            ->implode('');

        return $initial . str_pad($asset->id, 4, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json([
                'success' => false,
                'text' => 'Asset not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Asset Success',
            'result' => $asset,
        ]);
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json([
                'success' => false,
                'text' => 'Asset not found',
                'result' => null,
            ], 404);
        }

        $data = $this->validateData($request);
        unset($data['number']);
        $asset->update($data);
        $asset->number = $this->generateNumber($asset);
        $asset->save();

        return response()->json([
            'success' => true,
            'text' => 'Update Asset Success',
            'result' => $asset,
        ]);
    }

    public function destroy($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json([
                'success' => false,
                'text' => 'Asset not found',
                'result' => null,
            ], 404);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Asset Success',
            'result' => null,
        ]);
    }

    protected function validateData(Request $request)
    {
        return $this->validate($request, [
            'number' => 'nullable|string',
            'owner' => 'nullable|string',
            'name' => 'nullable|string',
            'price' => 'nullable|numeric',
            'estimate_month' => 'nullable|integer',
            'supplier' => 'nullable|string',
            'photo_url' => 'nullable|string',
            'photo_urls' => 'nullable',
            'brand' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
            'description' => 'nullable|string',
            'warranty_until' => 'nullable|date',
            'location' => 'nullable|string',
            'study_program_code' => 'nullable|string|max:50',
        ]);
    }

    protected function withFilter($dataContent, Request $request)
    {
        if ($request->filled('keyword')) {
            $dataContent = $dataContent->where(function ($query) use ($request) {
                $query->where('number', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('owner', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('brand', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('category')) {
            $dataContent = $dataContent->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $dataContent = $dataContent->where('status', $request->status);
        }

        if ($request->filled('owner')) {
            $dataContent = $dataContent->where('owner', 'LIKE', '%' . $request->owner . '%');
        }

        if ($request->filled('location')) {
            $dataContent = $dataContent->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('purchase_date_gte')) {
            $dataContent = $dataContent->whereDate('purchase_date', '>=', $request->purchase_date_gte);
        }

        if ($request->filled('purchase_date_lte')) {
            $dataContent = $dataContent->whereDate('purchase_date', '<=', $request->purchase_date_lte);
        }

        return $dataContent;
    }
}
