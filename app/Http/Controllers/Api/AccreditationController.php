<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use Illuminate\Http\Request;

class AccreditationController extends Controller
{
    public function dataTree($parent_idx)
    {
        $items = Accreditation::where('parent_idx', $parent_idx)
            ->orderBy('idx')
            ->where('type','standard')
            ->get()
            ->map(function ($item) {
                return $this->mapTreeNode($item);
            })
            ->values();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Accreditation Tree Success',
            'result' => $items,
        ]);
    }

    public function getParent(Request $request)
    {
        $dataContent = Accreditation::whereNull('parent_idx')->orderBy('idx');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 10);
        $dataContent->getCollection()->transform(function ($item) {
            $parentIdx = $item->idx;
            if (!$parentIdx) {
                $item->total_children = 0;
                $item->completed_children = 0;
                $item->completion_percentage = 0;
                return $item;
            }

            $totalChildren = Accreditation::where('type', 'standard')
                ->where('idx', 'like', $parentIdx . '%')
                ->where('idx', '!=', $parentIdx)
                ->count();
            $completedChildren = Accreditation::where('type', 'standard')
                ->where('idx', 'like', $parentIdx . '%')
                ->where('idx', '!=', $parentIdx)
                ->where('is_complete', 1)
                ->count();

            $item->total_children = $totalChildren;
            $item->completed_children = $completedChildren;
            $item->completion_percentage = $totalChildren > 0
                ? (int) round(($completedChildren / $totalChildren) * 100)
                : 0;

            return $item;
        });

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Accreditation Parents Success',
            'result' => $dataContent,
        ]);
    }

    public function storeEvidence(Request $request)
    {
        $this->validate($request, [
            'parent_id' => 'required|integer',
            'parent_idx' => 'required|string',
            'title' => 'nullable',
            'description' => 'nullable',
            'attachment_urls' => 'nullable',
        ]);

        $payload = $request->attributes->get('jwt_payload');
        $authType = $payload ? data_get($payload, 'log_as_auth_type') : null;
        $authId = $payload ? data_get($payload, 'log_as_auth_id') : null;

        if (!$authType) {
            $authType = $payload ? data_get($payload, 'auth_type') : null;
        }
        if (!$authId) {
            $authId = $payload ? data_get($payload, 'auth_id') : null;
        }

        $attachmentUrls = $request->attachment_urls;
        if (is_array($attachmentUrls)) {
            $attachmentUrls = json_encode($attachmentUrls);
        }

        $accreditation = Accreditation::create([
            'parent_id' => $this->cleanValue($request->parent_id),
            'parent_idx' => $this->cleanValue($request->parent_idx),
            'type' => 'evidence',
            'title' => $this->cleanValue($request->title),
            'description' => $this->cleanValue($request->description),
            'attachment_urls' => $attachmentUrls,
            'auth_type' => $authType,
            'auth_id' => $authId,
            'is_complete' => 1,
        ]);

        return response()->json([
            'success' => true,
            'text' => 'Create Evidence Success',
            'result' => $accreditation,
        ]);
    }

    public function insertInitData()
    {
        $dir = public_path('assets/json');
        $files = glob($dir . '/*.json');

        if (!$files) {
            return response()->json([
                'success' => false,
                'text' => 'No accreditation JSON files found.',
                'result' => [
                    'inserted' => 0,
                    'skipped' => 0,
                    'errors' => ['No JSON files in public/assets/json'],
                ],
            ], 404);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $payload = json_decode($content, true);

            if (!$payload || !is_array($payload)) {
                $errors[] = 'Invalid JSON: ' . basename($file);
                continue;
            }

            $this->insertAccreditationTree($payload, $inserted, $updated, $skipped);
        }

        return response()->json([
            'success' => true,
            'text' => 'Insert accreditation init data success',
            'result' => [
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $dataContent = Accreditation::orderBy('id', 'desc');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Accreditations Success',
            'result' => $dataContent,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $accreditation = Accreditation::create($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Create Accreditation Success',
            'result' => $accreditation,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        $accreditation = Accreditation::find($id);
        if (!$accreditation) {
            return response()->json([
                'success' => false,
                'text' => 'Accreditation not found',
                'result' => null,
            ], 404);
        }

        $accreditation->update($request->all());

        return response()->json([
            'success' => true,
            'text' => 'Update Accreditation Success',
            'result' => $accreditation,
        ]);
    }

    public function show($id)
    {
        $accreditation = Accreditation::find($id);

        if (!$accreditation) {
            return response()->json([
                'success' => false,
                'text' => 'Accreditation not found',
                'result' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'text' => 'Retrieve Accreditation Success',
            'result' => $accreditation,
        ]);
    }

    public function destroy($id)
    {
        $accreditation = Accreditation::find($id);
        if (!$accreditation) {
            return response()->json([
                'success' => false,
                'text' => 'Accreditation not found',
                'result' => null,
            ], 404);
        }

        $accreditation->delete();

        return response()->json([
            'success' => true,
            'text' => 'Delete Accreditation Success',
            'result' => null,
        ]);
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'type' => 'nullable',
            'parent_idx' => 'nullable|integer',
            'idx' => 'nullable|integer',
            'title' => 'nullable',
            'description' => 'nullable',
            'main_element' => 'nullable',
            'main_element_fulfilment' => 'nullable',
            'content' => 'nullable',
            'is_complete' => 'nullable|boolean',
            'attachment_urls' => 'nullable',
            'student_ids' => 'nullable',
            'lecture_ids' => 'nullable',
            'user_ids' => 'nullable',
            'auth_type' => 'nullable',
            'auth_id' => 'nullable|integer',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->keyword != null) {
            $dataContent = $dataContent->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('description', 'LIKE', '%' . $request->keyword . '%');
                $q->orWhere('content', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        return $dataContent;
    }

    private function insertAccreditationTree($item, &$inserted, &$updated, &$skipped)
    {
        $idx = $this->cleanValue(data_get($item, 'idx'));

        if (!$idx) {
            $skipped++;
        } else {
            $accreditation = Accreditation::where('idx', $idx)->first();
            if ($accreditation) {
                $accreditation->update($this->normalizeAccreditationData($item));
                $updated++;
            } else {
                Accreditation::create($this->normalizeAccreditationData($item));
                $inserted++;
            }
        }

        $children = data_get($item, 'children', []);
        if (is_array($children)) {
            foreach ($children as $child) {
                if (is_array($child)) {
                    $this->insertAccreditationTree($child, $inserted, $updated, $skipped);
                }
            }
        }
    }

    private function normalizeAccreditationData($item)
    {
        return [
            'type' => $this->cleanValue(data_get($item, 'type')),
            'parent_idx' => $this->cleanValue(data_get($item, 'parent_idx')),
            'idx' => $this->cleanValue(data_get($item, 'idx')),
            'title' => $this->cleanValue(data_get($item, 'title')),
            'description' => $this->cleanValue(data_get($item, 'description')),
            'main_element' => $this->cleanValue(data_get($item, 'main_element')),
            'main_element_fulfilment' => $this->cleanValue(data_get($item, 'main_element_fulfilment')),
            'content' => $this->cleanValue(data_get($item, 'content')),
            'is_complete' => data_get($item, 'is_complete') ? 1 : 0,
            'attachment_urls' => $this->normalizeJsonField(data_get($item, 'attachment_urls')),
            'student_ids' => $this->normalizeJsonField(data_get($item, 'student_ids')),
            'lecture_ids' => $this->normalizeJsonField(data_get($item, 'lecture_ids')),
            'user_ids' => $this->normalizeJsonField(data_get($item, 'user_ids')),
            'auth_type' => $this->cleanValue(data_get($item, 'auth_type')),
            'auth_id' => $this->cleanValue(data_get($item, 'auth_id')),
        ];
    }

    private function normalizeJsonField($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        return $this->cleanValue($value);
    }

    private function cleanValue($value)
    {
        if (is_string($value)) {
            $value = ltrim($value);
        }

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function mapTreeNode($item)
    {
        $children = Accreditation::where('parent_idx', $item->idx)
            ->orderBy('idx')
            ->where('type', 'standard')
            ->get()
            ->map(function ($child) {
                return $this->mapTreeNode($child);
            })
            ->values();

        $evidences = Accreditation::where('parent_idx', $item->idx)
            ->orderBy('id', 'desc')
            ->where('type', 'evidence')
            ->get()
            ->map(function ($evidence) {
                return $this->mapEvidenceNode($evidence);
            })
            ->values();

        return [
            'id' => $item->id,
            'type' => $item->type,
            'parent_idx' => $item->parent_idx,
            'idx' => $item->idx,
            'title' => $item->title,
            'description' => $item->description,
            'main_element' => $item->main_element,
            'main_element_fulfilment' => $item->main_element_fulfilment,
            'content' => $item->content,
            'is_complete' => $item->is_complete,
            'attachment_urls' => $item->attachment_urls,
            'student_ids' => $item->student_ids,
            'lecture_ids' => $item->lecture_ids,
            'user_ids' => $item->user_ids,
            'auth_type' => $item->auth_type,
            'auth_id' => $item->auth_id,
            'evidences' => $evidences,
            'children' => $children,
        ];
    }

    private function mapEvidenceNode($item)
    {
        return [
            'id' => $item->id,
            'parent_id' => $item->parent_id,
            'parent_idx' => $item->parent_idx,
            'type' => $item->type,
            'title' => $item->title,
            'description' => $item->description,
            'attachment_urls' => $item->attachment_urls,
            'auth_type' => $item->auth_type,
            'auth_id' => $item->auth_id,
            'created_at' => $item->created_at,
        ];
    }
}
