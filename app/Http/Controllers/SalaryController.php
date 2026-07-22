<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Salary::with(['employee', 'user', 'creator'])->latest('payment_date');

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->entity) {
            $query->where('entity', $request->entity);
        }

        if ($request->month) {
            $query->where('period_month', $request->month);
        }

        if ($request->year) {
            $query->where('period_year', $request->year);
        }

        $salaries = $query->paginate(15)->withQueryString();
        $employees = Employee::orderBy('name')->get();
        $entities = Salary::entities();

        return view('salaries.index', compact('salaries', 'employees', 'entities'));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        $entities = Salary::entities();

        return view('salaries.create', compact('employees', 'entities'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'entities' => 'required|array|min:1',
            'entities.*' => 'in:pt,apotek',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'basic_salary' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'allowance' => 'required|numeric|min:0',
            'bpjs_kesehatan' => 'required|numeric|min:0',
            'bpjs_ketenagakerjaan' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ], [
            'entities.required' => 'Pilih minimal satu entitas perusahaan (PT dan/atau Apotek).',
            'entities.min' => 'Pilih minimal satu entitas perusahaan (PT dan/atau Apotek).',
        ]);

        $entities = array_values(array_unique($v['entities']));
        $employee = Employee::findOrFail($v['employee_id']);

        foreach ($entities as $entity) {
            if (! $employee->canReceiveForEntity($entity)) {
                $label = Salary::entities()[$entity] ?? $entity;

                return back()->withInput()->with(
                    'toast_error',
                    "Karyawan {$employee->name} tidak terdaftar untuk {$label}. Sesuaikan lingkup entitas di Master Karyawan."
                );
            }
        }

        $blocked = [];
        foreach ($entities as $entity) {
            $exists = Salary::where('employee_id', $v['employee_id'])
                ->where('entity', $entity)
                ->where('period_month', $v['period_month'])
                ->where('period_year', $v['period_year'])
                ->exists();
            if ($exists) {
                $blocked[] = Salary::entities()[$entity] ?? $entity;
            }
        }
        if ($blocked) {
            return back()->withInput()->with(
                'toast_error',
                'Gaji '.$employee->name.' untuk '.implode(' & ', $blocked).' pada periode ini sudah tercatat!'
            );
        }

        $net = $v['basic_salary'] + $v['overtime'] + $v['allowance']
            - ($v['bpjs_kesehatan'] + $v['bpjs_ketenagakerjaan'] + $v['deduction']);

        $createdLabels = [];
        foreach ($entities as $entity) {
            $salary = Salary::create([
                'employee_id' => $v['employee_id'],
                'user_id' => $employee->user_id,
                'entity' => $entity,
                'period_month' => $v['period_month'],
                'period_year' => $v['period_year'],
                'basic_salary' => $v['basic_salary'],
                'overtime' => $v['overtime'],
                'allowance' => $v['allowance'],
                'bpjs_kesehatan' => $v['bpjs_kesehatan'],
                'bpjs_ketenagakerjaan' => $v['bpjs_ketenagakerjaan'],
                'deduction' => $v['deduction'],
                'net_salary' => $net,
                'payment_date' => $v['payment_date'],
                'notes' => $v['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $createdLabels[] = $salary->entity_label;
            ActivityLogService::created(
                'Gaji Karyawan',
                $employee->name.' · '.$salary->entity_label.' · Periode '.$v['period_month'].'/'.$v['period_year'],
                $salary->toArray()
            );
        }

        $count = count($createdLabels);
        $labelText = implode(' & ', $createdLabels);

        return redirect()->route('salaries.index')->with(
            'toast_success',
            $count > 1
                ? "{$count} slip gaji {$employee->name} berhasil dicatat ({$labelText})."
                : "Data gaji {$employee->name} ({$labelText}) berhasil dicatat!"
        );
    }

    public function edit(Salary $salary)
    {
        $employees = Employee::orderBy('name')->get();
        $entities = Salary::entities();

        return view('salaries.edit', compact('salary', 'employees', 'entities'));
    }

    public function update(Request $request, Salary $salary)
    {
        $v = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'entity' => 'required|in:pt,apotek',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'basic_salary' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'allowance' => 'required|numeric|min:0',
            'bpjs_kesehatan' => 'required|numeric|min:0',
            'bpjs_ketenagakerjaan' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($v['employee_id']);
        if (! $employee->canReceiveForEntity($v['entity'])) {
            return back()->withInput()->with(
                'toast_error',
                "Karyawan {$employee->name} tidak terdaftar untuk entitas tersebut."
            );
        }

        $exists = Salary::where('employee_id', $v['employee_id'])
            ->where('entity', $v['entity'])
            ->where('period_month', $v['period_month'])
            ->where('period_year', $v['period_year'])
            ->where('id', '!=', $salary->id)
            ->exists();

        if ($exists) {
            $label = Salary::entities()[$v['entity']] ?? $v['entity'];

            return back()->withInput()->with(
                'toast_error',
                "Gaji {$employee->name} untuk {$label} pada periode ini sudah tercatat!"
            );
        }

        $oldData = $salary->toArray();
        $v['user_id'] = $employee->user_id;
        $v['net_salary'] = $v['basic_salary'] + $v['overtime'] + $v['allowance']
            - ($v['bpjs_kesehatan'] + $v['bpjs_ketenagakerjaan'] + $v['deduction']);
        $salary->update($v);
        $entityLabel = $salary->fresh()->entity_label;

        ActivityLogService::updated(
            'Gaji Karyawan',
            $employee->name.' · '.$entityLabel.' · Periode '.$v['period_month'].'/'.$v['period_year'],
            $salary->toArray(),
            $oldData
        );

        return redirect()->route('salaries.index')->with('toast_success', 'Data gaji berhasil diperbarui!');
    }

    public function destroy(Salary $salary)
    {
        $oldData = $salary->toArray();
        $name = $salary->employee_name;
        $entityLabel = $salary->entity_label;
        $period = $salary->period_month.'/'.$salary->period_year;
        $salary->delete();

        ActivityLogService::deleted(
            'Gaji Karyawan',
            $name.' · '.$entityLabel.' · Periode '.$period,
            $oldData
        );

        return redirect()->route('salaries.index')->with('toast_success', 'Data gaji berhasil dihapus!');
    }

    public function printSlip(Salary $salary)
    {
        $salary->loadMissing(['employee', 'user.role']);

        ActivityLogService::log(
            'PRINT_REPORT',
            'Gaji Karyawan',
            'Mencetak Slip Gaji: '.$salary->employee_name
                .' ('.$salary->entity_label.') Periode: '.$salary->period_month.'/'.$salary->period_year
        );

        $branding = $salary->branding();

        return view('salaries.slip', compact('salary', 'branding'));
    }
}
