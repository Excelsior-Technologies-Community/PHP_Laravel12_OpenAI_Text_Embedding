@extends('layouts.app')

@section('title', 'Embedding Result')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">
                        <i class="fas fa-bolt mr-2"></i>Embedding Generated
                    </h1>
                    <p class="text-gray-200 mt-2">
                        Text converted into a {{ $embedding_length }}-dimensional vector
                    </p>
                </div>
                @if($is_mock ?? false)
                <div class="bg-yellow-500 text-white px-4 py-2 rounded-full mock-badge">
                    <i class="fas fa-vial mr-2"></i> Mock Mode
                </div>
                @endif
            </div>
        </div>
        
        <div class="p-6">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-3">Original Text</h2>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-gray-700">{{ $text }}</p>
                </div>
            </div>
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-xl font-bold text-gray-800">Embedding Vector</h2>
                    <div class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                        {{ $model }}
                    </div>
                </div>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg max-h-96 overflow-y-auto">
                    <pre class="whitespace-pre-wrap text-xs font-mono">[{{ implode(', ', array_slice($embedding, 0, 10)) }}, ...]</pre>
                    <p class="text-gray-400 text-sm mt-2">
                        Showing first 10 of {{ $embedding_length }} dimensions (full vector has {{ number_format($embedding_length) }} values)
                    </p>
                </div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-4 mb-8">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-blue-500 text-2xl mb-2">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="font-bold text-blue-800">Dimensions</h3>
                    <p class="text-blue-700 text-2xl font-bold">{{ number_format($embedding_length) }}</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-green-500 text-2xl mb-2">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3 class="font-bold text-green-800">Tokens Used</h3>
                    <p class="text-green-700 text-2xl font-bold">{{ $tokens_used }}</p>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="text-purple-500 text-2xl mb-2">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="font-bold text-purple-800">Vector Size</h3>
                    <p class="text-purple-700 text-2xl font-bold">{{ number_format($embedding_length * 32 / 8 / 1024, 2) }} KB</p>
                </div>
            </div>
            
            <div class="text-center">
                <a href="{{ route('home') }}#generate" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300 inline-block mr-4">
                    <i class="fas fa-redo mr-2"></i>Generate Another
                </a>
                <a href="{{ route('home') }}#compare" class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300 inline-block">
                    <i class="fas fa-balance-scale mr-2"></i>Compare Texts
                </a>
            </div>
        </div>
    </div>
</div>
@endsection