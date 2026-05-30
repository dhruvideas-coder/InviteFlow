<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\InvitationLink;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $user   = Auth::user();
        $docIds = $this->scopedDocumentIds($user);

        $today   = now()->startOfDay();
        $weekAgo = now()->subDays(7)->startOfDay();

        $docCount       = Document::whereIn('id', $docIds)->count();
        $recipientCount = Recipient::count();
        $linksCount     = InvitationLink::whereIn('document_id', $docIds)->count();
        $waCount        = InvitationLink::whereIn('document_id', $docIds)->where('via', 'WhatsApp')->count();

        $docsThisWeek = Document::whereIn('id', $docIds)->where('created_at', '>=', $weekAgo)->count();
        $linksToday   = InvitationLink::whereIn('document_id', $docIds)->where('created_at', '>=', $today)->count();

        $recentDocs = Document::whereIn('id', $docIds)
            ->withCount('invitationLinks as recipients_count')
            ->latest()
            ->limit(4)
            ->get(['id', 'name', 'status', 'created_at'])
            ->map(fn($d) => [
                'id'               => $d->id,
                'name'             => $d->name,
                'status'           => ucfirst($d->status ?? 'active'),
                'recipients_count' => $d->recipients_count,
                'created_at'       => $d->created_at,
            ]);

        return response()->json([
            'stats' => [
                'documents'       => $docCount,
                'recipients'      => $recipientCount,
                'links_generated' => $linksCount,
                'whatsapp_sent'   => $waCount,
            ],
            'trends' => [
                'docs_this_week' => $docsThisWeek,
                'links_today'    => $linksToday,
                'wa_pct'         => $linksCount > 0 ? round($waCount / $linksCount * 100) : 0,
            ],
            'recent_docs' => $recentDocs,
        ]);
    }

    public function analytics(Request $request)
    {
        $period = max(7, min(90, (int) $request->get('period', 30)));
        $from   = now()->subDays($period)->startOfDay();
        $prev   = now()->subDays($period * 2)->startOfDay();

        $user   = Auth::user();
        $docIds = $this->scopedDocumentIds($user);

        // KPIs — current period
        $docs  = Document::whereIn('id', $docIds)->where('created_at', '>=', $from)->count();
        $links = InvitationLink::whereIn('document_id', $docIds)->where('created_at', '>=', $from)->count();
        $wa    = InvitationLink::whereIn('document_id', $docIds)->where('via', 'WhatsApp')->where('created_at', '>=', $from)->count();
        $email = InvitationLink::whereIn('document_id', $docIds)->where('via', 'Email')->where('created_at', '>=', $from)->count();

        // KPIs — previous period for % change
        $prevDocs  = Document::whereIn('id', $docIds)->whereBetween('created_at', [$prev, $from])->count();
        $prevLinks = InvitationLink::whereIn('document_id', $docIds)->whereBetween('created_at', [$prev, $from])->count();
        $prevWa    = InvitationLink::whereIn('document_id', $docIds)->where('via', 'WhatsApp')->whereBetween('created_at', [$prev, $from])->count();
        $prevEmail = InvitationLink::whereIn('document_id', $docIds)->where('via', 'Email')->whereBetween('created_at', [$prev, $from])->count();

        // Chart data — fill full date range with zeros for missing days
        $chartRaw = InvitationLink::whereIn('document_id', $docIds)
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as links'),
                DB::raw('SUM(CASE WHEN via = "WhatsApp" THEN 1 ELSE 0 END) as wa')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = 0; $i < $period; $i++) {
            $dateStr     = now()->subDays($period - 1 - $i)->format('Y-m-d');
            $entry       = $chartRaw->get($dateStr);
            $chartData[] = [
                'date'  => $dateStr,
                'links' => $entry ? (int) $entry->links : 0,
                'wa'    => $entry ? (int) $entry->wa : 0,
            ];
        }

        // Top documents by link count in period
        $topDocsRaw = Document::whereIn('id', $docIds)
            ->withCount(['invitationLinks as link_count' => fn($q) => $q->where('created_at', '>=', $from)])
            ->orderByDesc('link_count')
            ->limit(5)
            ->get(['id', 'name']);

        $maxLinks = $topDocsRaw->max('link_count') ?: 1;
        $topDocs  = $topDocsRaw->map(fn($d) => [
            'name'  => $d->name,
            'count' => $d->link_count,
            'pct'   => (int) round($d->link_count / $maxLinks * 100),
        ]);

        // Channel breakdown — normalize via capitalization variants
        $channelsRaw = InvitationLink::whereIn('document_id', $docIds)
            ->where('created_at', '>=', $from)
            ->select('via', DB::raw('COUNT(*) as count'))
            ->groupBy('via')
            ->get();

        $normalized = [];
        foreach ($channelsRaw as $c) {
            $key = match (strtolower($c->via ?? '')) {
                'whatsapp' => 'WhatsApp',
                'email'    => 'Email',
                default    => 'Direct Link',
            };
            $normalized[$key] = ($normalized[$key] ?? 0) + $c->count;
        }

        $totalCh  = array_sum($normalized) ?: 1;
        $channels = collect($normalized)->map(fn($count, $name) => [
            'name'  => $name,
            'count' => $count,
            'pct'   => (int) round($count / $totalCh * 100),
        ])->values();

        // Village breakdown
        $villagesRaw = Recipient::whereHas('invitationLinks', fn($q) =>
            $q->whereIn('document_id', $docIds)->where('created_at', '>=', $from)
        )
        ->select('village_en', DB::raw('COUNT(*) as count'))
        ->groupBy('village_en')
        ->orderByDesc('count')
        ->limit(6)
        ->get();

        $maxVillage = $villagesRaw->max('count') ?: 1;
        $villages   = $villagesRaw->map(fn($v) => [
            'name'  => $v->village_en ?: 'Unknown',
            'count' => $v->count,
            'pct'   => (int) round($v->count / $maxVillage * 100),
        ]);

        return response()->json([
            'kpis' => [
                'documents' => ['value' => $docs, 'prev' => $prevDocs],
                'links'     => ['value' => $links, 'prev' => $prevLinks],
                'whatsapp'  => ['value' => $wa, 'prev' => $prevWa],
                'email'     => ['value' => $email, 'prev' => $prevEmail],
            ],
            'chart'    => $chartData,
            'top_docs' => $topDocs,
            'channels' => $channels,
            'villages' => $villages,
        ]);
    }

    private function scopedDocumentIds(User $user): array
    {
        $query = Document::query();

        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'admin') {
            $memberIds = User::where('parent_id', $user->id)->pluck('id')->push($user->id)->toArray();
            $query->whereIn('user_id', $memberIds);
        }
        // super_admin sees all — no filter

        return $query->pluck('id')->toArray();
    }
}
