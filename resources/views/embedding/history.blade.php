@extends('layouts.app')

@section('title', 'Embedding History')

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- Back Button -->
    <div class="mb-6">

        <a href="{{ route('home') }}"
            class="text-blue-500 hover:text-blue-700">

            <i class="fas fa-arrow-left mr-2"></i>

            Back to Home

        </a>

    </div>

    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-xl shadow-lg text-white p-6 mb-8">

        <div class="flex justify-between items-center">

            <div>

                <h1 class="text-3xl font-bold">

                    <i class="fas fa-history mr-2"></i>

                    Embedding History Dashboard

                </h1>

                <p class="mt-2 text-gray-200">

                    View all generated embedding records.

                </p>

            </div>

            <div>

                <a href="{{ route('home') }}"
                    class="bg-white text-indigo-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100">

                    <i class="fas fa-plus mr-2"></i>

                    Generate New

                </a>

            </div>

        </div>

    </div>

    <!-- Statistics -->

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <!-- Total -->

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">

            <div class="flex justify-between">

                <div>

                    <p class="text-gray-500 text-sm">

                        Total Embeddings

                    </p>

                    <h2 class="text-3xl font-bold text-blue-600 mt-2">

                        {{ $stats['total'] }}

                    </h2>

                </div>

                <div class="text-blue-500 text-4xl">

                    <i class="fas fa-database"></i>

                </div>

            </div>

        </div>

        <!-- Today -->

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">

            <div class="flex justify-between">

                <div>

                    <p class="text-gray-500 text-sm">

                        Today's Embeddings

                    </p>

                    <h2 class="text-3xl font-bold text-green-600 mt-2">

                        {{ $stats['today'] }}

                    </h2>

                </div>

                <div class="text-green-500 text-4xl">

                    <i class="fas fa-calendar-day"></i>

                </div>

            </div>

        </div>

        <!-- Tokens -->

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">

            <div class="flex justify-between">

                <div>

                    <p class="text-gray-500 text-sm">

                        Tokens Used

                    </p>

                    <h2 class="text-3xl font-bold text-purple-600 mt-2">

                        {{ number_format($stats['tokens']) }}

                    </h2>

                </div>

                <div class="text-purple-500 text-4xl">

                    <i class="fas fa-coins"></i>

                </div>

            </div>

        </div>

        <!-- Mock -->

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-500">

            <div class="flex justify-between">

                <div>

                    <p class="text-gray-500 text-sm">

                        Mock Records

                    </p>

                    <h2 class="text-3xl font-bold text-yellow-600 mt-2">

                        {{ $stats['mock'] }}

                    </h2>

                </div>

                <div class="text-yellow-500 text-4xl">

                    <i class="fas fa-vial"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- Search -->

    <div class="bg-white rounded-xl shadow p-6 mb-8">

        <form method="GET"
            action="{{ route('embedding.history') }}">

            <div class="flex flex-col md:flex-row gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search text or model..."
                    class="flex-1 border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">

                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

                    <i class="fas fa-search mr-2"></i>

                    Search

                </button>

                <a href="{{ route('embedding.history') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg text-center">

                    Clear

                </a>

            </div>

        </form>

    </div>

    <!-- Table -->

    <div class="bg-white rounded-xl shadow overflow-hidden">

        <div class="px-6 py-4 border-b">

            <h2 class="text-xl font-bold">

                Embedding Records

            </h2>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-6 py-3 text-left">#</th>

                        <th class="px-6 py-3 text-left">Text</th>

                        <th class="px-6 py-3 text-center">Model</th>

                        <th class="px-6 py-3 text-center">Dimensions</th>

                        <th class="px-6 py-3 text-center">Tokens</th>

                        <th class="px-6 py-3 text-center">Mode</th>

                        <th class="px-6 py-3 text-center">Created</th>

                        <th class="px-6 py-3 text-center">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($histories as $history)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="px-6 py-4">
                            {{ $histories->firstItem() + $loop->index }}
                        </td>

                        <td class="px-6 py-4">

                            <div class="max-w-md">

                                <p class="font-medium text-gray-800 break-words">

                                    {{ \Illuminate\Support\Str::limit($history->text, 120) }}

                                </p>

                            </div>

                        </td>

                        <td class="px-6 py-4 text-center">

                            <span
                                class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">

                                {{ $history->model }}

                            </span>

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ number_format($history->embedding_length) }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ number_format($history->tokens_used) }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            @if($history->is_mock)

                            <span
                                class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">

                                Mock

                            </span>

                            @else

                            <span
                                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">

                                Live

                            </span>

                            @endif

                        </td>

                        <td class="px-6 py-4 text-center">

                            {{ $history->created_at->format('d M Y') }}

                            <br>

                            <span class="text-gray-500 text-sm">

                                {{ $history->created_at->format('h:i A') }}

                            </span>

                        </td>

                        <td class="px-6 py-4 text-center">

                            <form
                                action="{{ route('embedding.destroy',$history->id) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this embedding history?')">

                                @csrf

                                @method('DELETE')

                                <button
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8" class="text-center py-12">

                            <div class="text-gray-500">

                                <i class="fas fa-database text-5xl mb-4"></i>

                                <h3 class="text-xl font-semibold mb-2">

                                    No Embedding History Found

                                </h3>

                                <p>

                                    Generate your first embedding to see it here.

                                </p>

                                <a href="{{ route('home') }}"
                                    class="inline-block mt-5 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">

                                    Generate Embedding

                                </a>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Pagination -->
        @if($histories->hasPages())

        <div class="px-6 py-4 border-t bg-gray-50">

            {{ $histories->links() }}

        </div>

        @endif

    </div>

    <!-- Summary -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">

        <div class="flex items-start">

            <div class="text-blue-600 text-3xl mr-4">

                <i class="fas fa-chart-bar"></i>

            </div>

            <div>

                <h3 class="text-xl font-bold text-blue-800 mb-3">

                    Dashboard Summary

                </h3>

                <div class="grid md:grid-cols-2 gap-4 text-blue-700">

                    <div>

                        <strong>Total Records :</strong>

                        {{ number_format($stats['total']) }}

                    </div>

                    <div>

                        <strong>Today's Records :</strong>

                        {{ number_format($stats['today']) }}

                    </div>

                    <div>

                        <strong>Total Tokens :</strong>

                        {{ number_format($stats['tokens']) }}

                    </div>

                    <div>

                        <strong>Mock Records :</strong>

                        {{ number_format($stats['mock']) }}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection