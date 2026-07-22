@extends('reports.preview_layout')

@section('content')
    @include('reports.templates.' . $type)
@endsection
