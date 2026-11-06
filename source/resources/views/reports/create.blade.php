@extends('layouts.app')

@section('title', '日報を作成')
@section('eyebrow', 'WRITE YOUR REPORT')
@section('heading', '今日の仕事を記録する')
@section('subtitle', '日付・タイトル・日報内容を入力してください。提出前に下書き保存できます。')

@section('content')
    @include('reports._form')
@endsection
