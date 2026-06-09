@extends('adminlte::page')

@section('title', trim(strip_tags($title ?? config('app.name', 'Laravel'))))

@section('content_header')
    @isset($header)
        {{ $header }}
    @endisset
@stop

@section('content')
    {{ $slot }}
@stop
