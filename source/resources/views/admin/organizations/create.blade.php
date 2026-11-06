@extends('layouts.app')

@section('title', '組織を追加')
@section('eyebrow', 'ORGANIZATION · NEW')
@section('heading', '組織を登録する')
@section('subtitle', '組織の階層と、ひとつ上の親組織を設定してください。')

@section('content')
    @include('admin.organizations._form')
@endsection
