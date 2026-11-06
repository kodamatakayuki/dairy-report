@extends('layouts.app')

@section('title', '日報を編集')
@section('eyebrow', 'EDIT YOUR REPORT')
@section('heading', '日報を編集する')
@section('subtitle', '提出前の内容を編集して保存・提出できます。')

@section('content')
    @include('reports._form')
@endsection
