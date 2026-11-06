<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::query()
            ->with(['parent', 'leaders'])
            ->withCount(['children', 'users', 'dailyReports'])
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        $organizations = Organization::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('admin.organizations.create', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        Organization::query()->create($this->validatedData($request));

        return redirect()->route('admin.organizations.index')->with('success', '組織を登録しました。');
    }

    public function edit(Organization $organization): View
    {
        $organization->loadCount(['children', 'users', 'dailyReports']);

        $organizations = Organization::query()
            ->where('is_active', true)
            ->whereNotIn('id', $organization->subtreeIds())
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('admin.organizations.edit', compact('organization', 'organizations'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $organization->update($this->validatedData($request, $organization));

        return redirect()->route('admin.organizations.index')->with('success', '組織情報を更新しました。');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        if (
            $organization->children()->exists()
            || $organization->users()->exists()
            || $organization->dailyReports()->exists()
        ) {
            return back()->with('error', '社員・日報・下位組織が登録されている組織は削除できません。');
        }

        $organization->delete();

        return redirect()->route('admin.organizations.index')->with('success', '組織を削除しました。');
    }

    /**
     * @return array{name: string, level: string, parent_id: int|null, is_active: bool}
     */
    private function validatedData(Request $request, ?Organization $organization = null): array
    {
        $level = $request->string('level')->toString();
        $excludedIds = $organization?->subtreeIds() ?? [];

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organizations', 'name')
                    ->where(function ($query) use ($request): void {
                        $parentId = $request->input('parent_id');

                        if ($parentId === null || $parentId === '') {
                            $query->whereNull('parent_id');
                        } else {
                            $query->where('parent_id', $parentId);
                        }
                    })
                    ->ignore($organization?->id),
            ],
            'level' => ['required', Rule::in(array_keys(Organization::LEVELS))],
            'parent_id' => [
                Rule::requiredIf($level !== Organization::LEVEL_BOARD),
                'nullable',
                'integer',
                'exists:organizations,id',
                Rule::notIn($excludedIds),
            ],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'name' => '組織名',
            'level' => '階層',
            'parent_id' => '上位組織',
        ]);

        $parent = isset($data['parent_id']) ? Organization::query()->find($data['parent_id']) : null;

        if ($data['level'] === Organization::LEVEL_BOARD && $parent !== null) {
            throw ValidationException::withMessages(['parent_id' => '取締役会には上位組織を指定できません。']);
        }

        $requiredParentLevel = Organization::PARENT_LEVELS[$data['level']] ?? null;

        if ($requiredParentLevel !== null && $parent?->level !== $requiredParentLevel) {
            throw ValidationException::withMessages(['parent_id' => '選択した階層のひとつ上にあたる組織を指定してください。']);
        }

        if (
            $organization !== null
            && $organization->level !== $data['level']
            && $organization->children()->exists()
        ) {
            throw ValidationException::withMessages(['level' => '下位組織があるため階層を変更できません。']);
        }

        if ($parent !== null && ! $parent->is_active) {
            throw ValidationException::withMessages(['parent_id' => '無効な組織は上位組織に指定できません。']);
        }

        return $data;
    }
}
