<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = User::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->orderBy('id')->paginate(15)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('employees.form', ['employee' => new User(['role' => 'cashier', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('employees.index')->with('success', 'เพิ่มพนักงานเรียบร้อย');
    }

    public function edit(User $employee): View
    {
        return view('employees.form', compact('employee'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $data = $this->validated($request, $employee);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if ($employee->is(auth()->user())) {
            // ป้องกันผู้ใช้ปิดสิทธิ์/ระงับบัญชีตัวเอง
            $data['role'] = 'admin';
            $data['is_active'] = true;
        }
        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'แก้ไขข้อมูลพนักงานเรียบร้อย');
    }

    public function destroy(User $employee): RedirectResponse
    {
        if ($employee->is(auth()->user())) {
            return back()->with('error', 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่');
        }
        if ($employee->sales()->exists()) {
            $employee->update(['is_active' => false]);

            return back()->with('success', 'พนักงานมีประวัติการขาย จึงระงับบัญชีแทนการลบ');
        }
        $employee->delete();

        return back()->with('success', 'ลบพนักงานเรียบร้อย');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $employee = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee)],
            'password' => [$employee ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:60'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
