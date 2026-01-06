@extends('layouts.app')

@section('title', 'Demo - OpenAI Text Embeddings')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-play-circle mr-2 text-blue-500"></i>Embedding Demo
        </h1>
        <p class="text-gray-600 text-lg">
            Try pre-configured examples to see how text embeddings work
        </p>
        @if($isMockMode ?? false)
        <div class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full mt-4">
            <i class="fas fa-vial mr-2"></i> Running in Demo Mode - No API Key Required
        </div>
        @endif
    </div>

    <!-- Demo Examples -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Example 1: Similar Sentences -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold text-blue-600 mb-4">
                <i class="fas fa-comments mr-2"></i>Similar Sentences
            </h3>
            <p class="text-gray-600 mb-4">
                These sentences have similar meanings but use different words.
            </p>
            <div class="space-y-3 mb-6">
                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-gray-800">"The quick brown fox jumps over the lazy dog"</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-gray-800">"A fast brown fox leaps over a sleepy dog"</p>
                </div>
            </div>
            <form action="{{ route('embedding.compare') }}" method="POST">
                @csrf
                <input type="hidden" name="text1" value="The quick brown fox jumps over the lazy dog">
                <input type="hidden" name="text2" value="A fast brown fox leaps over a sleepy dog">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-balance-scale mr-2"></i>Compare Similarity
                </button>
            </form>
            <p class="text-gray-500 text-sm mt-2">Expected: High similarity (70-90%)</p>
        </div>

        <!-- Example 2: Different Topics -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold text-red-600 mb-4">
                <i class="fas fa-exchange-alt mr-2"></i>Different Topics
            </h3>
            <p class="text-gray-600 mb-4">
                These texts discuss completely different topics.
            </p>
            <div class="space-y-3 mb-6">
                <div class="bg-red-50 p-3 rounded-lg">
                    <p class="text-gray-800">"Artificial intelligence is transforming the world"</p>
                </div>
                <div class="bg-red-50 p-3 rounded-lg">
                    <p class="text-gray-800">"The weather is beautiful today"</p>
                </div>
            </div>
            <form action="{{ route('embedding.compare') }}" method="POST">
                @csrf
                <input type="hidden" name="text1" value="Artificial intelligence is transforming the world">
                <input type="hidden" name="text2" value="The weather is beautiful today">
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-balance-scale mr-2"></i>Compare Similarity
                </button>
            </form>
            <p class="text-gray-500 text-sm mt-2">Expected: Low similarity (10-30%)</p>
        </div>
    </div>

    <!-- Single Embedding Demo -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-12">
        <h3 class="text-2xl font-bold text-purple-600 mb-6">
            <i class="fas fa-bolt mr-2"></i>Generate Single Embedding
        </h3>
        <div class="mb-6">
            <div class="bg-purple-50 p-4 rounded-lg mb-4">
                <p class="text-gray-800">"Machine learning algorithms can predict patterns in data"</p>
            </div>
            <form action="{{ route('embedding.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="text" value="Machine learning algorithms can predict patterns in data">
                <button type="submit" class="w-full bg-purple-500 hover:bg-purple-600 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-cogs mr-2"></i>Generate Embedding
                </button>
            </form>
        </div>
    </div>

    <!-- How It Works -->
    <div class="bg-gray-50 rounded-xl p-8 mb-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
            <i class="fas fa-cogs mr-2"></i>How Text Embeddings Work
        </h2>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                    <i class="fas fa-keyboard text-blue-500 text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 mb-2">1. Input Text</h4>
                <p class="text-gray-600">Text is tokenized into smaller pieces</p>
            </div>
            
            <div class="text-center">
                <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                    <i class="fas fa-project-diagram text-green-500 text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 mb-2">2. Vector Conversion</h4>
                <p class="text-gray-600">Converted to a 1536-dimensional numerical vector</p>
            </div>
            
            <div class="text-center">
                <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                    <i class="fas fa-chart-line text-purple-500 text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 mb-2">3. Similarity Calculation</h4>
                <p class="text-gray-600">Cosine similarity measures semantic similarity</p>
            </div>
        </div>
    </div>

    <!-- Try Your Own -->
    <div class="text-center">
        <a href="{{ route('home') }}" class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-xl text-lg transition duration-300 shadow-lg">
            <i class="fas fa-rocket mr-2"></i>Try Your Own Examples
        </a>
        <p class="text-gray-600 mt-4">Create custom text comparisons and embeddings</p>
    </div>
</div>
@endsection