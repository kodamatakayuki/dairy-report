@php
    $statusLabels = [
        'DRAFT' => '下書き',
        'SUBMITTED' => '提出済み',
        'RETURNED' => '差し戻し',
        'PUBLISHED' => '公開済み',
    ];
@endphp
<span class="status-badge status-{{ strtolower($status) }}">
    <span class="badge-dot"></span>{{ $statusLabels[$status] ?? $status }}
</span>
