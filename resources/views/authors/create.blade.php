@extends('layouts.app')

@section('title', 'Создать автора')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">
                <i class="fas fa-plus me-2"></i>Создать нового автора
            </h2>
        </div>
        <div class="card-body">
            <form action="{{ route('authors.store') }}" method="POST">
                @csrf
                @include('authors.form')
            </form>
        </div>
    </div>
@endsection
