@extends('adminlte::auth.auth-page', ['authType' => $authType ?? 'login'])

@section('title', trim(strip_tags($title ?? config('app.name', 'Laravel'))))

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@isset($authHeader)
    @section('auth_header')
        {{ $authHeader }}
    @stop
@endisset

@section('auth_body')
    {{ $slot }}
@stop
