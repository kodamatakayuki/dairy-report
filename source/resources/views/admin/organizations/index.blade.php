@extends('layouts.app')

@section('title', '組織マスタ')
@section('eyebrow', 'ADMINISTRATION · ORGANIZATION')
@section('heading', '組織マスタ')
@section('subtitle', '取締役会・本部・部・グループの階層と、各組織のリーダーを管理します。')
@section('heading-action')
    <a class="button button-primary" href="{{ route('admin.organizations.create') }}"><span>＋</span> 組織を追加</a>
@endsection

@section('content')
    <div class="hierarchy-banner"><span class="summary-icon icon-mint">⌘</span><div><small>ORGANIZATION HIERARCHY</small><strong>取締役会 <span>→</span> 本部 <span>→</span> 部 <span>→</span> グループ</strong></div><p>各階層にひとつ上の組織を設定してください。</p></div>
    <section class="panel">
        <div class="table-wrap">
            <table class="data-table master-table">
                <thead><tr><th>組織</th><th>階層</th><th>上位組織</th><th>リーダー</th><th>社員</th><th>状態</th><th></th></tr></thead>
                <tbody>
                @foreach($organizations as $organization)
                    <tr>
                        <td><strong>{{ $organization->name }}</strong></td>
                        <td><span class="role-pill">{{ \App\Models\Organization::LEVELS[$organization->level] }}</span></td>
                        <td>{{ $organization->parent?->name ?? '—' }}</td>
                        <td>{{ $organization->leaders->pluck('name')->join('、') ?: '—' }}</td>
                        <td>{{ $organization->users_count }}名</td>
                        <td><span class="role-indicator {{ $organization->is_active ? '' : 'indicator-offline' }}"><i></i> {{ $organization->is_active ? '利用中' : '無効' }}</span></td>
                        <td class="action-cell"><a class="text-link" href="{{ route('admin.organizations.edit', $organization) }}">編集</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($organizations->hasPages())<div class="pagination-wrap">{{ $organizations->links() }}</div>@endif
    </section>
@endsection
