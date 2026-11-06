@php($editing = isset($organization))
<form class="panel form-panel" method="POST" action="{{ $editing ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="form-grid">
        <div class="field-group"><label for="name">組織名 <span class="required-mark">必須</span></label><input id="name" class="form-control" name="name" maxlength="100" value="{{ old('name', $organization->name ?? '') }}" placeholder="例：営業1G" required></div>
        <div class="field-group"><label for="level">階層 <span class="required-mark">必須</span></label><select id="level" class="form-control" name="level" required>@foreach(\App\Models\Organization::LEVELS as $value => $label)<option value="{{ $value }}" @selected(old('level', $organization->level ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field-group"><label for="parent_id">上位組織 <span class="field-hint">取締役会には不要</span></label><select id="parent_id" class="form-control" name="parent_id"><option value="">上位組織を選択</option>@foreach($organizations as $parent)<option value="{{ $parent->id }}" @selected((string)old('parent_id', $organization->parent_id ?? '') === (string)$parent->id)>{{ \App\Models\Organization::LEVELS[$parent->level] }} ／ {{ $parent->name }}</option>@endforeach</select></div>
        <div class="field-group field-toggle"><span class="field-label">組織設定</span><label class="check-row"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $organization->is_active ?? true))> 新しい社員の配属先として利用する</label></div>
    </div>
    <div class="form-footer"><a href="{{ route('admin.organizations.index') }}" class="button button-ghost">キャンセル</a><button class="button button-primary" type="submit">{{ $editing ? '変更を保存' : '組織を登録' }} <span>→</span></button></div>
</form>
