<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = User::query()
            ->with('organization.parent')
            ->orderBy('login_id')
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $organizations = Organization::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('admin.employees.create', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['email'] = mb_strtolower($data['login_id']).'@employees.local';

        User::query()->create($data);

        return redirect()->route('admin.employees.index')->with('success', '社員を登録しました。');
    }

    public function edit(User $employee): View
    {
        $organizations = Organization::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('admin.employees.edit', compact('employee', 'organizations'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $data = $this->validatedData($request, $employee);

        if (filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['email'] = mb_strtolower($data['login_id']).'@employees.local';
        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', '社員情報を更新しました。');
    }

    public function destroy(Request $request, User $employee): RedirectResponse
    {
        if ($employee->is($request->user())) {
            return back()->with('error', 'ログイン中の社員を停止することはできません。');
        }

        if (
            $employee->isAdmin()
            && $employee->is_active
            && User::query()->where('system_role', User::ROLE_ADMIN)->where('is_active', true)->count() === 1
        ) {
            return back()->with('error', '最後の管理者は利用停止にできません。');
        }

        $employee->update(['is_active' => false]);

        return redirect()->route('admin.employees.index')->with('success', '社員を利用停止にしました。');
    }

    /**
     * @return array{name: string, login_id: string, system_role: string, is_leader: bool, organization_id: int, is_active: bool, password: string|null}
     */
    private function validatedData(Request $request, ?User $employee = null): array
    {
        $request->merge([
            'login_id' => mb_strtoupper(trim((string) $request->input('login_id'))),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'login_id' => [
                'required',
                'string',
                'max:100',
                'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                Rule::unique('users', 'login_id')->ignore($employee?->id),
            ],
            'system_role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_GENERAL])],
            'is_leader' => ['nullable', 'boolean'],
            'organization_id' => ['required', 'integer', Rule::exists('organizations', 'id')->where('is_active', true)],
            'is_active' => ['required', 'boolean'],
            'password' => [$employee === null ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
        ], [], [
            'name' => '氏名',
            'login_id' => '社員ID',
            'system_role' => '権限',
            'organization_id' => '所属組織',
            'password' => 'パスワード',
        ]);
    }
}
