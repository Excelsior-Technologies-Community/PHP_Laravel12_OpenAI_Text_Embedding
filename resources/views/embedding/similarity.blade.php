@extends('layouts.app')

@section('title', 'Similarity Search Results')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
        <div class="gradient-bg text-white p-6">
            <h1 class="text-3xl font-bold">
                <i class="fas fa-search mr-2"></i>Similarity Search Results
            </h1>
            <p class="text-gray-200 mt-2">
                Found the most similar text from your list
            </p>
        </div>
        
        <div class="p-6">
            <!-- Query Display -->
            <div class="mb-10">
                <h2 class="text-xl font-bold text-gray-800 mb-3">Search Query</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                    <p class="text-gray-800 text-lg"><strong>"{{ $query }}"</strong></p>
                </div>
            </div>
            
            <!-- Most Similar Result -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Most Similar Result</h2>
                    <div class="bg-green-100 text-green-800 font-bold py-2 px-4 rounded-full">
                        {{ $most_similar['percentage'] }}% Match
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                    <p class="text-gray-800 text-lg mb-4">{{ $most_similar['text'] }}</p>
                    <div class="flex items-center text-green-700">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Cosine Similarity: {{ number_format($most_similar['similarity'], 4) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- All Results -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">All Results (Ranked)</h2>
                
                <div class="space-y-4">
                    @foreach($all_similarities as $index => $result)
                    <div class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 transition duration-300 {{ $loop->first ? 'border-green-300 bg-green-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 mr-4">
                                <div class="flex items-center mb-2">
                                    @if($loop->first)
                                        <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded mr-3">#1</span>
                                    @else
                                        <span class="bg-gray-200 text-gray-800 text-xs font-bold px-2 py-1 rounded mr-3">#{{ $loop->iteration }}</span>
                                    @endif
                                    <span class="text-gray-600">Score: {{ number_format($result['similarity'], 4) }}</span>
                                </div>
                                <p class="text-gray-800">{{ $result['text'] }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold {{ $result['percentage'] > 70 ? 'text-green-600' : ($result['percentage'] > 30 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $result['percentage'] }}%
                                </div>
                            </div>
                        </div>
                        
                        <!-- Similarity Bar -->
                        <div class="mt-4">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full {{ $result['percentage'] > 70 ? 'bg-green-500' : ($result['percentage'] > 30 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                     style="width: {{ min($result['percentage'], 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Stats -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-blue-800 mb-2">Search Information</h3>
                        <p class="text-blue-700">
                            <strong>Total Texts Searched:</strong> {{ count($all_similarities) }}<br>
                            <strong>Tokens Used:</strong> {{ $tokens_used }}<br>
                            <strong>Most Similar Score:</strong> {{ number_format($most_similar['similarity'], 6) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <a href="{{ route('home') }}#find-similar" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
            <i class="fas fa-redo mr-2"></i>Perform Another Search
        </a>
    </div>
</div>
@endsection