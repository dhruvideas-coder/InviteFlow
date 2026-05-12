<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized');
        }
        return $user;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        if ($user->isMember()) {
            $ownerId = $user->parent_id;
            if (!$ownerId) {
                return response()->json([]);
            }
        } elseif ($user->isAdmin()) {
            $ownerId = $user->id;
        } elseif (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $query = Document::with(['fields'])->withCount('fields');

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $ownerId);
        }

        $documents = $query
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get()
            ->map(fn($doc) => [
                'id' => $doc->id,
                'type' => $doc->type,
                'name' => $doc->name,
                'description' => $doc->description,
                'file_name' => $doc->file_name,
                'file_type' => $doc->file_type,
                'status' => $doc->status,
                'total_signers' => $doc->total_signers,
                'completed_signers' => $doc->completed_signers,
                'language' => $doc->language,
                'fields_count' => $doc->fields_count,
                'fields' => $doc->fields,
                'expires_at' => $doc->expires_at?->toDateString(),
                'file_url' => Storage::disk('public')->url($doc->file_path),
                'created_at' => $doc->created_at->toDateString(),
            ]);

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $user = $this->authorizeAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'description' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date',
            'status' => 'nullable|in:draft,active',
            'language' => 'nullable|in:en,gu,both',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'user_id' => $user->id,
            'type' => $request->type ?? 'document',
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'expires_at' => $request->expires_at,
            'status' => $request->status ?? 'draft',
            'total_signers' => $request->total_signers ?? 0,
            'language' => $request->language ?? 'gu',
        ]);

        return response()->json($document, 201);
    }

    public function show(Request $request, Document $document)
    {
        $user = $this->authorizeAdmin($request);

        if (!$user->isSuperAdmin() && $document->user_id !== $user->id) {
            abort(403);
        }

        return response()->json(array_merge($document->load('fields')->toArray(), [
            'file_url' => Storage::disk('public')->url($document->file_path),
        ]));
    }

    public function update(Request $request, Document $document)
    {
        $user = $this->authorizeAdmin($request);

        if (!$user->isSuperAdmin() && $document->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date',
            'status' => 'sometimes|in:draft,active,expired',
            'language' => 'sometimes|in:en,gu,both',
        ]);

        $document->update($request->only(['name', 'description', 'expires_at', 'status', 'language']));

        return response()->json($document);
    }

    public function destroy(Request $request, Document $document)
    {
        $user = $this->authorizeAdmin($request);

        if (!$user->isSuperAdmin() && $document->user_id !== $user->id) {
            abort(403);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function saveFields(Request $request, Document $document)
    {
        $user = $this->authorizeAdmin($request);

        if (!$user->isSuperAdmin() && $document->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'fields' => 'present|array',
            'fields.*.field_type' => 'required|in:name,village',
            'fields.*.label' => 'required|string|max:100',
            'fields.*.color' => 'nullable|string|max:20',
            'fields.*.x_percent' => 'required|numeric|min:0|max:100',
            'fields.*.y_percent' => 'required|numeric|min:0|max:100',
            'fields.*.width_percent' => 'nullable|numeric|min:1|max:100',
            'fields.*.height_percent' => 'nullable|numeric|min:1|max:100',
            'fields.*.page_number' => 'integer|min:1',
            'fields.*.required' => 'boolean',
            'fields.*.sort_order' => 'integer',
        ]);

        $document->fields()->delete();

        foreach ($request->fields as $i => $data) {
            $document->fields()->create([
                'field_type'     => $data['field_type'],
                'label'          => $data['label'],
                'color'          => $data['color'] ?? '#000000',
                'x_percent'      => $data['x_percent'],
                'y_percent'      => $data['y_percent'],
                'width_percent'  => $data['width_percent']  ?? 22,
                'height_percent' => $data['height_percent'] ?? 4,
                'page_number'    => $data['page_number'] ?? 1,
                'required'       => $data['required'] ?? false,
                'sort_order'     => $data['sort_order'] ?? $i,
            ]);
        }

        return response()->json($document->load('fields'));
    }
    public function publicShow(Document $document)
    {
        return response()->json(array_merge($document->load('fields')->toArray(), [
            'file_url' => Storage::disk('public')->url($document->file_path),
        ]));
    }
}
