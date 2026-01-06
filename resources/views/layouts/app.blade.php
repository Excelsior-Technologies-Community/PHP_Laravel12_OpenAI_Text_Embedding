<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OpenAI Text Embeddings')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .mock-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-purple-700 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('home') }}" class="text-2xl font-bold">
                    <i class="fas fa-robot mr-2"></i>OpenAI Embeddings
                </a>
                <div class="space-x-4">
                    <a href="{{ route('home') }}" class="hover:text-gray-200">
                        <i class="fas fa-home mr-1"></i>Home
                    </a>
                    <a href="{{ route('demo') }}" class="hover:text-gray-200">
                        <i class="fas fa-play mr-1"></i>Demo
                    </a>
                    @if(app()->environment('local'))
                    <a href="{{ route('clear.rate.limit') }}" class="text-yellow-300 hover:text-yellow-200">
                        <i class="fas fa-redo mr-1"></i>Clear Rate Limit
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Mock Mode Warning -->
    @if(isset($isMockMode) && $isMockMode)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-3 text-yellow-600"></i>
                <div class="flex-1">
                    <strong>Demo Mode Active:</strong> Using mock embeddings. 
                    To use real OpenAI API, add your API key to the .env file:
                    <code class="bg-yellow-200 px-2 py-1 rounded ml-2">OPENAI_API_KEY=your-key-here</code>
                </div>
                <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:text-blue-800 ml-4">
                    <i class="fas fa-external-link-alt mr-1"></i>Get API Key
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- API Status Bar -->
    <div class="bg-blue-50 border-b border-blue-200 py-2">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt text-blue-500 mr-2"></i>
                    <span class="text-blue-700 font-medium mr-4">API Status:</span>
                    @php
                        $status = $apiStatus ?? app(App\Services\OpenAIService::class)->getApiStatus();
                        $percentage = $status['usage_percentage'];
                    @endphp
                    <div class="w-48 bg-gray-200 rounded-full h-2.5 mr-3">
                        <div class="h-2.5 rounded-full {{ $percentage < 70 ? 'bg-green-500' : ($percentage < 90 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                             style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                    <span class="text-sm text-gray-600">
                        {{ $status['requests_last_minute'] }}/{{ $status['limit_per_minute'] }} requests per minute
                    </span>
                </div>
                @if($status['is_mock_mode'] ?? false)
                <div class="text-sm bg-yellow-500 text-white px-3 py-1 rounded-full mock-badge mt-2 md:mt-0">
                    <i class="fas fa-vial mr-1"></i> Mock Mode
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif
        
        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            </div>
        @endif
        
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>Laravel 12 + OpenAI Text Embedding Demo</p>
            <p class="text-gray-400 text-sm mt-2">Using text-embedding-ada-002 model with automatic mock mode fallback</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>