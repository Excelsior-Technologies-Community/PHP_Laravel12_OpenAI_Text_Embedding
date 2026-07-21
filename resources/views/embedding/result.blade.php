@extends('layouts.app')

@section('title', 'Embedding Result')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Back Button -->
    <div class="mb-8">
        <a href="{{ route('home') }}" class="text-blue-500 hover:text-blue-600 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">

                <div>
                    <h1 class="text-3xl font-bold">
                        <i class="fas fa-bolt mr-2"></i>Embedding Generated
                    </h1>

                    <p class="text-gray-200 mt-2">
                        Text converted into a
                        <strong>{{ number_format($embedding_length) }}</strong>
                        dimensional vector
                    </p>
                </div>

                <!-- Current Mode -->
                @if($is_mock ?? false)
                    <div class="bg-yellow-500 text-white px-5 py-3 rounded-full shadow-lg text-center">
                        <div class="font-bold">
                            <i class="fas fa-vial mr-2"></i>🟡 Demo Mode
                        </div>
                        <div class="text-xs text-yellow-100 mt-1">
                            Mock Embeddings
                        </div>
                    </div>
                @else
                    <div class="bg-green-500 text-white px-5 py-3 rounded-full shadow-lg text-center">
                        <div class="font-bold">
                            <i class="fas fa-robot mr-2"></i>🟢 OpenAI Mode
                        </div>
                        <div class="text-xs text-green-100 mt-1">
                            Real API Embeddings
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <div class="p-6">

            <!-- Status Card -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-8">
                <div class="grid md:grid-cols-2 gap-6">

                    <div>
                        <h3 class="font-bold text-gray-800 mb-2">
                            Embedding Status
                        </h3>

                        @if($is_mock ?? false)
                            <p class="text-yellow-600 font-semibold">
                                🟡 Running in Demo Mode
                            </p>

                            <p class="text-sm text-gray-600 mt-2">
                                The OpenAI API is unavailable, so a deterministic mock embedding
                                has been generated for demonstration purposes.
                            </p>
                        @else
                            <p class="text-green-600 font-semibold">
                                🟢 Running in OpenAI Mode
                            </p>

                            <p class="text-sm text-gray-600 mt-2">
                                Embedding successfully generated using the OpenAI API.
                            </p>
                        @endif
                    </div>

                    <div class="text-left md:text-right">
                        <div class="text-sm text-gray-500">
                            Model
                        </div>

                        <div class="font-bold text-blue-700 text-lg">
                            {{ $model }}
                        </div>

                        <div class="text-sm text-gray-500 mt-3">
                            Embedding Length
                        </div>

                        <div class="font-bold text-gray-800">
                            {{ number_format($embedding_length) }}
                        </div>
                    </div>

                </div>
            </div>

            <!-- Original Text -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-3">
                    Original Text
                </h2>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                    <p class="text-gray-700 leading-relaxed">
                        {{ $text }}
                    </p>
                </div>
            </div>

            <!-- Embedding Vector -->
            <div class="mb-8">

                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-xl font-bold text-gray-800">
                        Embedding Vector
                    </h2>

                    <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                        {{ $model }}
                    </span>
                </div>

                <div class="bg-gray-900 text-green-300 rounded-lg p-5 overflow-auto max-h-96">

                    <pre class="text-xs whitespace-pre-wrap font-mono">[
{{ implode(', ', array_slice($embedding, 0, 10)) }},
...
]</pre>

                    <div class="border-t border-gray-700 mt-4 pt-3 text-gray-400 text-sm">
                        Showing only the first <strong>10</strong> values of a
                        <strong>{{ number_format($embedding_length) }}</strong>
                        dimensional vector.
                    </div>

                </div>

            </div>

            <!-- Statistics -->
            <div class="grid md:grid-cols-4 gap-5 mb-8">

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">

                    <div class="text-blue-500 text-3xl mb-3">
                        <i class="fas fa-layer-group"></i>
                    </div>

                    <h3 class="font-semibold text-blue-800">
                        Dimensions
                    </h3>

                    <p class="text-2xl font-bold text-blue-700 mt-2">
                        {{ number_format($embedding_length) }}
                    </p>

                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-5">

                    <div class="text-green-500 text-3xl mb-3">
                        <i class="fas fa-coins"></i>
                    </div>

                    <h3 class="font-semibold text-green-800">
                        Tokens Used
                    </h3>

                    <p class="text-2xl font-bold text-green-700 mt-2">
                        {{ $tokens_used }}
                    </p>

                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-5">

                    <div class="text-purple-500 text-3xl mb-3">
                        <i class="fas fa-database"></i>
                    </div>

                    <h3 class="font-semibold text-purple-800">
                        Vector Size
                    </h3>

                    <p class="text-2xl font-bold text-purple-700 mt-2">
                        {{ number_format($embedding_length * 32 / 8 / 1024, 2) }} KB
                    </p>

                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-5">

                    <div class="text-orange-500 text-3xl mb-3">
                        <i class="fas fa-microchip"></i>
                    </div>

                    <h3 class="font-semibold text-orange-800">
                        Mode
                    </h3>

                    <p class="text-lg font-bold mt-2">
                        @if($is_mock ?? false)
                            <span class="text-yellow-600">
                                Demo
                            </span>
                        @else
                            <span class="text-green-600">
                                OpenAI
                            </span>
                        @endif
                    </p>

                </div>

            </div>

            <!-- Buttons -->
            <div class="text-center">

                <a href="{{ route('home') }}#generate"
                    class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg transition mr-3">
                    <i class="fas fa-redo mr-2"></i>
                    Generate Another
                </a>

                <a href="{{ route('home') }}#compare"
                    class="inline-block bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg transition">
                    <i class="fas fa-balance-scale mr-2"></i>
                    Compare Texts
                </a>

            </div>

        </div>
    </div>
</div>
@endsection