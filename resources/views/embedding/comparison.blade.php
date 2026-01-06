@extends('layouts.app')

@section('title', 'Text Comparison Result')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">
                        <i class="fas fa-balance-scale mr-2"></i>Text Similarity Comparison
                    </h1>
                    <p class="text-gray-200 mt-2">
                        Cosine similarity score calculated from text embeddings
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
            <!-- Similarity Score Display -->
            <div class="text-center mb-10">
                <div class="inline-block relative mb-4">
                    <div class="text-6xl font-bold {{ $similarity_percentage > 70 ? 'text-green-600' : ($similarity_percentage > 30 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $similarity_percentage }}%
                    </div>
                    <div class="text-gray-600 text-sm mt-2">
                        Cosine Similarity: {{ number_format($similarity, 4) }}
                    </div>
                </div>
                
                <!-- Similarity Bar -->
                <div class="mt-6 max-w-2xl mx-auto">
                    <div class="h-4 bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 rounded-full mb-2 relative">
                        <div class="absolute h-6 w-1 bg-black -top-1 transform -translate-x-1/2" style="left: {{ $similarity_percentage }}%;"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>0% (No Similarity)</span>
                        <span>100% (Identical)</span>
                    </div>
                </div>
            </div>
            
            <!-- Texts Compared -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="font-bold text-gray-800 mb-3 text-lg">Text 1</h3>
                    <div class="bg-gray-50 p-4 rounded h-full">
                        <p class="text-gray-700">{{ $text1 }}</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-5">
                    <h3 class="font-bold text-gray-800 mb-3 text-lg">Text 2</h3>
                    <div class="bg-gray-50 p-4 rounded h-full">
                        <p class="text-gray-700">{{ $text2 }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Interpretation -->
            <div class="mb-8">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">
                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>Interpretation
                </h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="text-center p-4 rounded {{ $similarity_percentage > 70 ? 'bg-green-100 border-2 border-green-400' : 'bg-gray-100' }}">
                        <div class="text-2xl font-bold text-green-600 mb-2">70-100%</div>
                        <p class="text-sm text-gray-700">Highly Similar<br>Same meaning, different words</p>
                    </div>
                    <div class="text-center p-4 rounded {{ $similarity_percentage >= 30 && $similarity_percentage <= 70 ? 'bg-yellow-100 border-2 border-yellow-400' : 'bg-gray-100' }}">
                        <div class="text-2xl font-bold text-yellow-600 mb-2">30-70%</div>
                        <p class="text-sm text-gray-700">Somewhat Related<br>Shared concepts or context</p>
                    </div>
                    <div class="text-center p-4 rounded {{ $similarity_percentage < 30 ? 'bg-red-100 border-2 border-red-400' : 'bg-gray-100' }}">
                        <div class="text-2xl font-bold text-red-600 mb-2">0-30%</div>
                        <p class="text-sm text-gray-700">Not Similar<br>Different topics or meanings</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-blue-800 mb-2">Technical Details</h3>
                        <p class="text-blue-700">
                            <strong>Tokens Used:</strong> {{ $tokens_used }} |
                            <strong>Similarity Score:</strong> {{ number_format($similarity, 6) }} |
                            <strong>Vector Dimensions:</strong> 1536
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <a href="{{ route('home') }}#compare" class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
            <i class="fas fa-redo mr-2"></i>Compare Another Pair
        </a>
    </div>
</div>
@endsection