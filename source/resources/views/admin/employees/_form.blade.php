@php($editing = isset($employee))
<form class="panel form-panel" method="POST" action="{{ $editing ? route('admin.employees.update', $employee) : route('admin.employees.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="form-grid">
        <div class="field-group"><label for="name">氏名 <span class="required-mark">必須</span></label><input id="name" class="form-control" name="name" value="{{ old('name', $employee->name ?? '') }}" maxlength="100" required></div>
        <div class="field-group"><label for="login_id">社員ID <span class="required-mark">必須</span></label><input id="login_id" class="form-control" name="login_id" value="{{ old('login_id', $employee->login_id ?? '') }}" maxlength="100" pattern="[A-Za-z0-9][A-Za-z0-9._-]*" placeholder="例：E1024" required><span class="field-hint">半角英数字・ピリオド・ハイフンを使用できます。</span></div>
        <div class="field-group"><label for="organization_id">所属組織 <span class="required-mark">必須</span></label><select id="organization_id" class="form-control" name="organization_id" required><option value="">組織を選択</option>@foreach($organizations as $organization)<option value="{{ $organization->id }}" @selected((string)old('organization_id', $employee->organization_id ?? '') === (string)$organization->id)>{{ \App\Models\Organization::LEVELS[$organization->level] }} ／ {{ $organization->name }}</option>@endforeach</select></div>
        <div class="field-group"><label for="system_role">システム権限 <span class="required-mark">必須</span></label><select id="system_role" class="form-control" name="system_role" required><option value="GENERAL" @selected(old('system_role', $employee->system_role ?? 'GENERAL') === 'GENERAL')>一般</option><option value="ADMIN" @selected(old('system_role', $employee->system_role ?? '') === 'ADMIN')>管理者</option></select></div>
        <div class="field-group">
            <label for="password">ログインパスワード @if(!$editing)<span class="required-mark">必須</span>@else<span class="field-hint">変更時のみ入力</span>@endif</label>
            <input id="password" class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
        </div>
        <div class="field-group field-toggle">
            <span class="field-label">社員設定</span>
            <label class="check-row"><input type="hidden" name="is_leader" value="0"><input type="checkbox" name="is_leader" value="1" @checked(old('is_leader', $employee->is_leader ?? false))> グループリーダー</label>
            <label class="check-row"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee->is_active ?? true))> アカウントを利用可能にする</label>
        </div>
    </div>
    <div class="form-footer"><a href="{{ route('admin.employees.index') }}" class="button button-ghost">キャンセル</a><button class="button button-primary" type="submit">{{ $editing ? '変更を保存' : '社員を登録' }} <span>→</span></button></div>
</form>
