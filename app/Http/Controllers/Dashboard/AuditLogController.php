<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('type'), fn ($q) => $q->where('auditable_type', $request->type))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereIlike('description', '%' . $request->search . '%')
                  ->orWhereHas('user', fn ($u) =>
                      $u->whereIlike('first_name', '%' . $request->search . '%')
                        ->orWhereIlike('last_name',  '%' . $request->search . '%')
                        ->orWhereIlike('email',       '%' . $request->search . '%')
                  );
            }))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('dashboard.audit-logs.index', compact('logs'));
    }
}
