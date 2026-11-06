@extends('layouts.app')

@section('title', '社員を追加')
@section('eyebrow', 'PEOPLE · NEW MEMBER')
@section('heading', '社員を登録する')
@section('subtitle', '社員IDと初期パスワードを設定してください。')

@section('content')
    @include('admin.employees._form')
@endsection
