<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->latestFirst();

        if ($request->get('action')) {
            $query->where('action', $request->get('action'));
        }
        if ($request->get('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->get('q')) {
            $keyword = trim($request->get('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'like', "%{$keyword}%")
                    ->orWhere('user_name', 'like', "%{$keyword}%")
                    ->orWhere('model_type', 'like', "%{$keyword}%");
            });
        }

        $logs = $query->with('user')->paginate(25)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $actions = ['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus'];

        return view('admin.activity_logs.index', compact('logs', 'users', 'actions'));
    }
}