<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /** Supported template languages. */
    private const LANGUAGES = ['en', 'gu'];

    /** Built-in fallback templates used until an admin customizes them. */
    private const DEFAULTS = [
        'en' => "Hello {name},\n\nYou are cordially invited to *{document}*.\n\nView your personalized invitation here:\n{link}\n\nRegards",
        'gu' => "નમસ્તે {name},\n\nઆપને *{document}* માટે સાદર આમંત્રણ છે.\n\nઆપનું વ્યક્તિગત આમંત્રણ અહીં જુઓ:\n{link}\n\nઆભાર",
    ];

    /**
     * The tenant (admin) that owns the templates for the given user.
     * Members inherit their parent admin's templates.
     */
    private function tenantId(User $user): int
    {
        return $user->isMember() && $user->parent_id ? $user->parent_id : $user->id;
    }

    /**
     * Return both language templates for the current user's tenant,
     * falling back to the built-in defaults for any not yet customized.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request->user());

        $stored = MessageTemplate::where('user_id', $tenantId)
            ->pluck('body', 'language');

        $templates = [];
        foreach (self::LANGUAGES as $lang) {
            $templates[$lang] = $stored[$lang] ?? self::DEFAULTS[$lang];
        }

        return response()->json([
            'templates'   => $templates,
            'can_edit'    => $request->user()->isAdmin() || $request->user()->isSuperAdmin(),
        ]);
    }

    /**
     * Upsert the EN/GU templates for the current admin's tenant.
     * Only admins and super admins may edit.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isSuperAdmin())) {
            return response()->json(['message' => 'You are not allowed to edit message templates.'], 403);
        }

        $validated = $request->validate([
            'templates'    => 'required|array',
            'templates.en' => ['required', 'string', 'max:2000', 'regex:/\{link\}/'],
            'templates.gu' => ['required', 'string', 'max:2000', 'regex:/\{link\}/'],
        ], [
            'templates.*.regex' => 'The message must include the {link} placeholder.',
        ]);

        foreach (self::LANGUAGES as $lang) {
            MessageTemplate::updateOrCreate(
                ['user_id' => $user->id, 'language' => $lang],
                ['body' => $validated['templates'][$lang]],
            );
        }

        return $this->index($request);
    }
}
