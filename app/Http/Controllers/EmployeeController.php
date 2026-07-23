<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user')->withCount('salaries')->latest();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('position', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('entity_scope')) {
            $query->where('entity_scope', $request->entity_scope);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('is_active', true)->count(),
            'pt' => Employee::whereIn('entity_scope', ['pt', 'both'])->count(),
            'apotek' => Employee::whereIn('entity_scope', ['apotek', 'both'])->count(),
        ];

        return view('employees.index', [
            'employees' => $employees,
            'stats' => $stats,
            'entityScopes' => Employee::entityScopes(),
        ]);
    }

    public function create()
    {
        return view('employees.create', $this->formData(null));
    }

    public function store(Request $request)
    {
        $v = $this->validated($request);
        $v = $this->syncJobPosition($v);
        $v['code'] = $v['code'] ?: Employee::nextCode();
        $v['is_active'] = true;
        $v['photo'] = $this->storePhoto($request);

        $employee = Employee::create($v);
        ActivityLogService::created('Karyawan', $employee->name, $employee->toArray());

        return redirect()->route('employees.index')
            ->with('toast_success', "Karyawan {$employee->name} berhasil ditambahkan!");
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'salaries' => fn ($q) => $q->latest('payment_date')->limit(8)]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', array_merge($this->formData($employee), compact('employee')));
    }

    public function update(Request $request, Employee $employee)
    {
        $v = $this->validated($request, $employee);
        $v = $this->syncJobPosition($v);
        $v['is_active'] = $request->boolean('is_active', true);

        if ($request->boolean('remove_photo') && $employee->photo) {
            $this->deletePhotoFile($employee->photo);
            $v['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                $this->deletePhotoFile($employee->photo);
            }
            $v['photo'] = $this->storePhoto($request);
        }

        $old = $employee->toArray();
        $employee->update($v);
        ActivityLogService::updated('Karyawan', $employee->name, $employee->toArray(), $old);

        return redirect()->route('employees.index')
            ->with('toast_success', "Data karyawan {$employee->name} berhasil diperbarui!");
    }

    public function destroy(Employee $employee)
    {
        if ($employee->salaries()->exists()) {
            return back()->with(
                'toast_error',
                "Karyawan {$employee->name} masih punya data gaji. Nonaktifkan saja, atau hapus data gaji terlebih dahulu."
            );
        }

        $old = $employee->toArray();
        $name = $employee->name;
        if ($employee->photo) {
            $this->deletePhotoFile($employee->photo);
        }
        $employee->delete();
        ActivityLogService::deleted('Karyawan', $name, $old);

        return redirect()->route('employees.index')
            ->with('toast_success', "Karyawan {$name} berhasil dihapus!");
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update(['is_active' => ! $employee->is_active]);
        $label = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('toast_success', "Karyawan {$employee->name} berhasil {$label}.");
    }

    private function formData(?Employee $employee = null): array
    {
        $linkedIds = Employee::whereNotNull('user_id')
            ->when($employee, fn ($q) => $q->where('id', '!=', $employee->id))
            ->pluck('user_id');

        $jobPositions = JobPosition::query()
            ->active()
            ->orderBy('name')
            ->get();

        // Pastikan jabatan karyawan saat ini tetap muncul meski nonaktif.
        if ($employee?->job_position_id && ! $jobPositions->contains('id', $employee->job_position_id)) {
            $current = JobPosition::find($employee->job_position_id);
            if ($current) {
                $jobPositions = $jobPositions->prepend($current)->unique('id')->values();
            }
        }

        return [
            'entityScopes' => Employee::entityScopes(),
            'jobPositions' => $jobPositions,
            'users' => User::with('role')
                ->whereHas('role', fn ($q) => $q->whereIn('slug', ['super_admin', 'admin_keuangan', 'kasir', 'staff_operasional']))
                ->orderBy('name')
                ->get(),
            'linkedUserIds' => $linkedIds,
            'nextCode' => $employee?->code ?: Employee::nextCode(),
        ];
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'code' => [
                'nullable', 'string', 'max:30',
                Rule::unique('employees', 'code')->ignore($employee?->id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:150',
            'job_position_id' => 'nullable|exists:job_positions,id',
            'entity_scope' => 'required|in:pt,apotek,both',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'join_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'nik' => 'nullable|string|max:30',
            'bank_name' => 'nullable|string|max:80',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder' => 'nullable|string|max:150',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'remove_photo' => 'nullable|boolean',
        ]);
    }

    /** Sinkronkan nama jabatan dari master data. */
    private function syncJobPosition(array $v): array
    {
        if (! empty($v['job_position_id'])) {
            $job = JobPosition::find($v['job_position_id']);
            $v['position'] = $job?->name;
        } else {
            $v['job_position_id'] = null;
            $v['position'] = null;
        }

        return $v;
    }

    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $dir = public_path('uploads/employees');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $file = $request->file('photo');
        $filename = 'emp_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return 'uploads/employees/'.$filename;
    }

    private function deletePhotoFile(?string $path): void
    {
        if (! $path) {
            return;
        }
        $full = public_path($path);
        if (File::exists($full)) {
            File::delete($full);
        }
    }
}
