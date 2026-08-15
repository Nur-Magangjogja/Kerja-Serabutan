@extends('layouts.admin')

@section('page-title', 'Pengaturan')

@section('content')
<div class="max-w-xl mx-auto py-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-200 flex justify-center">
        <x-theme-switcher />
    </div>
</div>
@endsection
