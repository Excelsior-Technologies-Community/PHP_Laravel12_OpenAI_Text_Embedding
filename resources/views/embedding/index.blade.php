@extends('layouts.app')

@section('title', 'Home - OpenAI Text Embeddings')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            OpenAI Text Embeddings
        </h1>
        <p class="text-gray-600 text-lg">
            Convert text into vector embeddings and compare semantic similarity
        </p>
        @if($isMockMode ?? false)
        <div class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full mt-4">
            <i class="fas fa-vial mr-2"></i> Running in Demo Mode
        </div>
        @endif
    </div>

    <!-- Features Grid -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="text-blue-500 text-3xl mb-4">
                <i class="fas fa-bolt"></i>
            </div>
            <h3 class="text-xl font-bold mb-3">Generate Embeddings</h3>
            <p class="text-gray-600 mb-4">
                Convert any text into a 1536-dimensional vector representation.
            </p>
            <a href="#generate" class="text-blue-500 font-medium hover:text-blue-600">
                Try it now →
            </a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="text-green-500 text-3xl mb-4">
                <i class="fas fa-balance-scale"></i>
            </div>
            <h3 class="text-xl font-bold mb-3">Compare Texts</h3>
            <p class="text-gray-600 mb-4">
                Measure semantic similarity between two pieces of text using cosine similarity.
            </p>
            <a href="#compare" class="text-green-500 font-medium hover:text-green-600">
                Compare texts →
            </a>
        </div>
    </div>

    <!-- Generate Embedding Form -->
    <div id="generate" class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-blue-600">
            <i class="fas fa-bolt mr-2"></i>Generate Text Embedding
        </h2>
        
        <form action="{{ route('embedding.generate') }}" method="POST" id="embedding-form">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2" for="text">
                    Enter Text to Embed
                </label>
                <textarea 
                    name="text" 
                    id="text" 
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter any text here (e.g., 'The quick brown fox jumps over the lazy dog')..."
                    required
                ></textarea>
                <p class="text-gray-500 text-sm mt-2">Text will be converted into a 1536-dimensional vector</p>
            </div>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                <i class="fas fa-cogs mr-2"></i>Generate Embedding
            </button>
        </form>
    </div>

    <!-- Compare Texts Form -->
    <div id="compare" class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6 text-green-600">
            <i class="fas fa-balance-scale mr-2"></i>Compare Text Similarity
        </h2>
        
        <form action="{{ route('embedding.compare') }}" method="POST" id="compare-form">
            @csrf
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="text1">
                        Text 1
                    </label>
                    <textarea 
                        name="text1" 
                        id="text1" 
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="First text..."
                        required
                    >The quick brown fox jumps over the lazy dog</textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="text2">
                        Text 2
                    </label>
                    <textarea 
                        name="text2" 
                        id="text2" 
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Second text..."
                        required
                    >A fast brown fox leaps over a sleepy dog</textarea>
                </div>
            </div>
            
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                <i class="fas fa-chart-bar mr-2"></i>Compare Similarity
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading indicators to forms
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                button.disabled = true;
                
                // Re-enable button after 10 seconds if still disabled
                setTimeout(() => {
                    if (button.disabled) {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                }, 10000);
            }
        });
    });
    
    // Auto-clear messages after 8 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.bg-red-100, .bg-green-100, .bg-blue-100');
        messages.forEach(msg => {
            msg.style.transition = 'opacity 1s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 1000);
        });
    }, 8000);
});
</script>
@endpush
@endsection