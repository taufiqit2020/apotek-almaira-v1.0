<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobPositionController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPosition::withCount('employees')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $jobPositions = $query->paginate(20)->withQueryString();

        return view('job_positions.index', compact('jobPositions'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:job_positions,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $v['is_active'] = true;
        $job = JobPosition::create($v);
        ActivityLogService::created('Jabatan', $job->name);

        return back()->with('toast_success', "Jabatan {$job->name} berhasil ditambahkan!");
    }

    public function update(Request $request, JobPosition $jobPosition)
    {
        $v = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('job_positions', 'name')->ignore($jobPosition->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $jobPosition->update($v);
        ActivityLogService::updated('Jabatan', $jobPosition->name);

        return back()->with('toast_success', 'Jabatan berhasil diperbarui!');
    }

    public function destroy(JobPosition $jobPosition)
    {
        $count = $jobPosition->employees()->count();
        if ($count > 0) {
            return back()->with(
                'toast_error',
                "Jabatan tidak bisa dihapus karena masih dipakai {$count} karyawan!"
            );
        }

        $name = $jobPosition->name;
        $jobPosition->delete();
        ActivityLogService::deleted('Jabatan', $name);

        return back()->with('toast_success', "Jabatan {$name} berhasil dihapus!");
    }

    public function toggleStatus(JobPosition $jobPosition)
    {
        $jobPosition->update(['is_active' => ! $jobPosition->is_active]);

        return back()->with('toast_success', 'Status jabatan diperbarui!');
    }
}
