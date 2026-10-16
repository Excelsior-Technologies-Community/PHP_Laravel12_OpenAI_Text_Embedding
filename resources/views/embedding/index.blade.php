@extends('layouts.app')

@section('title', 'AI Studio - OpenAI Embeddings & RAG Knowledgebase')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    <!-- Top Studio Header & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    <i class="fas fa-brain mr-1.5 text-blue-600"></i> AI Knowledge Studio
                </span>
                <span class="text-xs text-gray-500">&bull; text-embedding-3-small + GPT-4o-mini</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                OpenAI Text Embeddings &amp; RAG Studio
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                Smart Sliding-Window Document Chunking, Vector Retrieval, and Grounded RAG Chatbot
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1-Click Sample Knowledgebase Seeder -->
            <form action="{{ route('document.seed.sample') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <i class="fas fa-magic mr-2 text-yellow-300"></i> Load Sample Knowledgebase
                </button>
            </form>

            <a href="{{ route('embedding.history') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fas fa-history mr-1.5 text-gray-500"></i> Vector History
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Documents</span>
            <div class="text-2xl font-black text-gray-800 mt-1">{{ number_format($stats['total_documents']) }}</div>
            <span class="text-[11px] text-gray-500">PDF, TXT, MD, CSV</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Indexed Chunks</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['total_chunks']) }}</div>
            <span class="text-[11px] text-gray-500">Sliding-window vectors</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Embeddings</span>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($stats['total_embeddings']) }}</div>
            <span class="text-[11px] text-gray-500">1536-dim vectors</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Tokens</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_tokens']) }}</div>
            <span class="text-[11px] text-gray-500">Usage tracked</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm col-span-2 lg:col-span-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">RAG Queries</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ number_format($stats['total_rag_queries']) }}</div>
            <span class="text-[11px] text-gray-500">Grounded answers</span>
        </div>
    </div>

    <!-- Main Studio Nav Tabs -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 pt-3 flex flex-wrap gap-2" id="studioTabs">
            <button type="button" onclick="switchStudioTab('tab-chat')" id="btn-tab-chat"
                class="px-4 py-3 rounded-t-xl text-sm font-bold border-b-2 border-emerald-600 text-emerald-700 bg-white flex items-center gap-2">
                <i class="fas fa-comments text-emerald-500"></i>
                <span>Ask My Docs (RAG Chatbot)</span>
            </button>

            <button type="button" onclick="switchStudioTab('tab-documents')" id="btn-tab-documents"
                class="px-4 py-3 rounded-t-xl text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-2">
                <i class="fas fa-file-pdf text-blue-500"></i>
                <span>Smart Document Chunker ({{ $stats['total_documents'] }})</span>
            </button>

            <button type="button" onclick="switchStudioTab('tab-playground')" id="btn-tab-playground"
                class="px-4 py-3 rounded-t-xl text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-2">
                <i class="fas fa-bolt text-amber-500"></i>
                <span>Text Embedding &amp; Similarity</span>
            </button>

            <button type="button" onclick="switchStudioTab('tab-search')" id="btn-tab-search"
                class="px-4 py-3 rounded-t-xl text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-2">
                <i class="fas fa-search text-purple-500"></i>
                <span>Vector Semantic Search</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: RAG Semantic Knowledgebase Chatbot -->
        <!-- ========================================== -->
        <div id="tab-chat" class="p-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Chat Pane -->
                <div class="flex-1 flex flex-col h-[650px] border border-gray-200 rounded-2xl bg-white shadow-sm overflow-hidden">
                    <!-- Chat Header -->
                    <div class="px-5 py-3.5 bg-slate-900 text-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                                <i class="fas fa-robot text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Grounded RAG Assistant</h3>
                                <p class="text-[11px] text-gray-400">Retrieves context from {{ $stats['total_chunks'] }} document chunks</p>
                            </div>
                        </div>

                        <form action="{{ route('rag.clear') }}" method="POST" onsubmit="return confirm('Clear chat conversation history?');">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition flex items-center gap-1">
                                <i class="fas fa-trash-alt"></i> Clear Chat
                            </button>
                        </form>
                    </div>

                    <!-- Chat Messages Stream -->
                    <div id="chatMessagesStream" class="flex-1 p-5 overflow-y-auto space-y-4 bg-slate-50">
                        <!-- Welcome Bubble -->
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm p-4 text-xs text-gray-800 shadow-sm max-w-2xl leading-relaxed">
                                <p class="font-semibold text-gray-900 mb-1">Hello! I am your RAG Knowledgebase Assistant.</p>
                                <p>Ask me questions about your uploaded documents. I will retrieve the most relevant semantic chunks and answer with verified citations.</p>
                                
                                @if($stats['total_documents'] == 0)
                                    <div class="mt-3 p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-[11px]">
                                        <i class="fas fa-exclamation-circle mr-1"></i> No documents are indexed yet. Click <strong>"Load Sample Knowledgebase"</strong> at top to test instantly!
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Previous Messages from Database -->
                        @foreach($ragMessages->reverse() as $msg)
                            <!-- User Message -->
                            <div class="flex items-start justify-end gap-3">
                                <div class="bg-emerald-600 text-white rounded-2xl rounded-tr-sm p-3.5 text-xs shadow-sm max-w-xl leading-relaxed">
                                    {{ $msg->question }}
                                </div>
                                <div class="w-7 h-7 rounded-full bg-slate-700 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>

                            <!-- AI Response -->
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm p-4 text-xs text-gray-800 shadow-sm max-w-2xl leading-relaxed space-y-3">
                                    <div class="whitespace-pre-line">{{ $msg->answer }}</div>

                                    @if(!empty($msg->sources))
                                        <div class="pt-2 border-t border-gray-100">
                                            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">
                                                <i class="fas fa-bookmark text-emerald-500 mr-1"></i> Retrieved Sources &amp; Citations:
                                            </span>
                                            <div class="space-y-1.5">
                                                @foreach($msg->sources as $src)
                                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-[11px]">
                                                        <div class="flex justify-between items-center font-semibold text-gray-700">
                                                            <span><i class="fas fa-file-lines text-blue-500 mr-1"></i> {{ $src['document_title'] ?? 'Document' }} (Chunk #{{ $src['chunk_index'] ?? 0 }})</span>
                                                            <span class="text-emerald-600 font-mono">{{ $src['similarity_percentage'] ?? 0 }}% match</span>
                                                        </div>
                                                        <p class="text-gray-500 mt-1 italic text-[10px]">{{ $src['snippet'] ?? '' }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex justify-between items-center text-[10px] text-gray-400 pt-1">
                                        <span>Model: {{ $msg->model }} &bull; {{ $msg->tokens_used }} tokens</span>
                                        <span>{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Suggested Query Chips -->
                    <div class="px-4 py-2 bg-slate-100 border-t border-gray-200 flex flex-wrap gap-1.5 items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Try:</span>
                        <button type="button" onclick="setChatPrompt('What are the architectural highlights and features of Laravel 12?')"
                            class="text-[11px] bg-white border border-gray-300 hover:border-emerald-500 px-2 py-1 rounded-md text-gray-600 hover:text-emerald-700 transition">
                            Laravel 12 features?
                        </button>
                        <button type="button" onclick="setChatPrompt('How do text embeddings and cosine similarity work in RAG?')"
                            class="text-[11px] bg-white border border-gray-300 hover:border-emerald-500 px-2 py-1 rounded-md text-gray-600 hover:text-emerald-700 transition">
                            RAG &amp; Cosine similarity?
                        </button>
                        <button type="button" onclick="setChatPrompt('What embedding models are provided by OpenAI?')"
                            class="text-[11px] bg-white border border-gray-300 hover:border-emerald-500 px-2 py-1 rounded-md text-gray-600 hover:text-emerald-700 transition">
                            OpenAI embedding models?
                        </button>
                    </div>

                    <!-- Chat Input Form -->
                    <div class="p-3 bg-white border-t border-gray-200">
                        <form id="ragChatForm" onsubmit="submitRagChat(event)" class="flex gap-2">
                            <input type="text" id="ragQuestionInput" required placeholder="Ask any question about your indexed documents..."
                                class="flex-1 px-4 py-3 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <button type="submit" id="btnSubmitRag"
                                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-sm">
                                <i class="fas fa-paper-plane"></i> <span>Ask AI</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Quick Info Pane -->
                <div class="w-full lg:w-80 space-y-4">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-xs text-emerald-900 space-y-3">
                        <div class="flex items-center gap-2 font-bold text-emerald-800 text-sm">
                            <i class="fas fa-lightbulb text-amber-500"></i> How RAG Works
                        </div>
                        <ol class="list-decimal list-inside space-y-2 text-emerald-800 text-[11px]">
                            <li><strong>Ingest:</strong> Documents are broken into sliding-window text chunks.</li>
                            <li><strong>Embed:</strong> Each chunk is converted to a 1536D vector.</li>
                            <li><strong>Retrieve:</strong> Your question is converted to an embedding and compared via cosine similarity.</li>
                            <li><strong>Grounded Synthesis:</strong> Top matching chunks are injected into GPT-4o-mini to produce a verified answer with citations.</li>
                        </ol>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-3">
                        <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-layer-group text-blue-500"></i> Active Knowledgebase
                        </h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-gray-600 pb-1.5 border-b border-gray-100">
                                <span>Indexed Files:</span>
                                <strong class="text-gray-900">{{ $stats['total_documents'] }}</strong>
                            </div>
                            <div class="flex justify-between text-gray-600 pb-1.5 border-b border-gray-100">
                                <span>Vector Chunks:</span>
                                <strong class="text-blue-600">{{ $stats['total_chunks'] }}</strong>
                            </div>
                            <div class="flex justify-between text-gray-600 pb-1.5 border-b border-gray-100">
                                <span>Embedding Model:</span>
                                <span class="font-mono text-[10px] text-indigo-600">text-embedding-3-small</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Mode:</span>
                                @if($isMockMode)
                                    <span class="text-amber-600 font-semibold">Demo / Testing</span>
                                @else
                                    <span class="text-emerald-600 font-semibold">Live OpenAI API</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: Smart Document Chunker & Vectorizer -->
        <!-- ========================================== -->
        <div id="tab-documents" class="p-6 hidden">
            <!-- Upload Box -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-dashed border-blue-300 rounded-2xl p-6 mb-8 text-center">
                <form action="{{ route('document.upload') }}" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto space-y-4">
                    @csrf
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center mx-auto text-xl shadow-md">
                        <i class="fas fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Upload Document to Chunk &amp; Vectorize</h3>
                        <p class="text-xs text-gray-500 mt-1">Supports PDF, TXT, Markdown (.md), CSV, and JSON (Max 20MB)</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-left">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Select File</label>
                            <input type="file" name="document_file" required accept=".pdf,.txt,.text,.md,.markdown,.csv,.json"
                                class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Title (Optional)</label>
                            <input type="text" name="title" placeholder="e.g. Annual Report 2026"
                                class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Chunk Size (Words)</label>
                            <select name="chunk_size" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="120">120 words (Fine-grained)</option>
                                <option value="180" selected>180 words (Balanced - Recommended)</option>
                                <option value="300">300 words (Coarse context)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition inline-flex items-center gap-2">
                            <i class="fas fa-wand-magic-sparkles"></i> Process &amp; Vectorize Document
                        </button>
                    </div>
                </form>
            </div>

            <!-- Indexed Documents Table -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 text-base">Indexed Documents in Knowledgebase</h3>
                    <span class="text-xs text-gray-500">Total: <strong>{{ count($documents) }}</strong> documents</span>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-sm">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">Document Title</th>
                                <th class="py-3 px-4">File Type</th>
                                <th class="py-3 px-4">Size</th>
                                <th class="py-3 px-4">Chunks Indexed</th>
                                <th class="py-3 px-4">Tokens</th>
                                <th class="py-3 px-4">Uploaded</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($documents as $doc)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-semibold text-gray-900">
                                        <i class="fas fa-file-lines text-blue-500 mr-2"></i>{{ $doc->title }}
                                        <span class="block text-[10px] text-gray-400 font-normal">{{ $doc->original_filename }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            {{ $doc->file_type == 'pdf' ? 'bg-red-100 text-red-700' : ($doc->file_type == 'md' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $doc->file_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $doc->formatted_file_size }}</td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-blue-600">{{ $doc->chunks_count ?? $doc->total_chunks }} chunks</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ number_format($doc->total_tokens) }}</td>
                                    <td class="py-3 px-4 text-gray-400">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4 text-right space-x-1">
                                        <button type="button" onclick="inspectDocumentChunks({{ $doc->id }})"
                                            class="px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-[11px] font-semibold transition">
                                            <i class="fas fa-eye"></i> Chunks
                                        </button>

                                        <form action="{{ route('document.delete', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document and all its chunks?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded text-[11px] font-semibold transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        <i class="fas fa-folder-open text-3xl mb-2 block text-gray-300"></i>
                                        No documents uploaded yet. Upload a file or click "Load Sample Knowledgebase" above.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: Text Embedding & Similarity        -->
        <!-- ========================================== -->
        <div id="tab-playground" class="p-6 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Single Embedding Generator -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-bolt text-blue-500"></i> Generate Single Text Embedding
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">Converts text into a 1536-dimensional OpenAI vector representation.</p>

                    <form action="{{ route('embedding.generate') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Enter Text</label>
                            <textarea name="text" rows="4" required placeholder="Type any text to embed..."
                                class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">The quick brown fox jumps over the lazy dog</textarea>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Generate 1536D Vector
                        </button>
                    </form>
                </div>

                <!-- Compare 2 Texts -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-balance-scale text-emerald-500"></i> Compare 2 Texts Similarity
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">Calculates Cosine Similarity between two text vectors.</p>

                    <form action="{{ route('embedding.compare') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Text 1</label>
                            <textarea name="text1" rows="2" required placeholder="First text..."
                                class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">Artificial intelligence is revolutionizing modern software engineering.</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Text 2</label>
                            <textarea name="text2" rows="2" required placeholder="Second text..."
                                class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">Machine learning models are transforming how code is developed.</textarea>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Calculate Cosine Similarity
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: Semantic Similarity Search         -->
        <!-- ========================================== -->
        <div id="tab-search" class="p-6 hidden">
            <div class="max-w-2xl mx-auto text-center space-y-4">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Search Knowledgebase Using Vector Similarity</h3>
                <p class="text-xs text-gray-500">Search by concept rather than keywords. Matches semantic intent across all vectors.</p>

                <form action="{{ route('embedding.search') }}" method="POST" class="space-y-3 text-left">
                    @csrf
                    <div>
                        <input type="text" name="query" required placeholder="Type semantic query (e.g. 'deep learning algorithms', 'framework performance')..."
                            class="w-full text-xs p-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-none shadow-sm">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition">
                            <i class="fas fa-magnifying-glass mr-1.5"></i> Run Semantic Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<!-- Modal: Inspect Document Chunks -->
<div id="chunkModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-4xl w-full max-h-[85vh] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <h4 class="font-bold text-gray-900 text-sm" id="chunkModalDocTitle">Document Chunks Inspector</h4>
                <p class="text-xs text-gray-500" id="chunkModalDocMeta">Sliding-window partitions &amp; vector metadata</p>
            </div>
            <button type="button" onclick="closeChunkModal()" class="text-gray-400 hover:text-gray-700 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-3" id="chunkModalBody">
            <div class="text-center py-8 text-gray-400">Loading chunk data...</div>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" onclick="closeChunkModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold text-xs rounded-lg transition">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- Tab Switching Logic ---
    function switchStudioTab(tabId) {
        ['tab-chat', 'tab-documents', 'tab-playground', 'tab-search'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
            const btn = document.getElementById('btn-' + id);
            if (btn) {
                btn.className = 'px-4 py-3 rounded-t-xl text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-2';
            }
        });

        const activeEl = document.getElementById(tabId);
        if (activeEl) activeEl.classList.remove('hidden');

        const activeBtn = document.getElementById('btn-' + tabId);
        if (activeBtn) {
            activeBtn.className = 'px-4 py-3 rounded-t-xl text-sm font-bold border-b-2 border-emerald-600 text-emerald-700 bg-white flex items-center gap-2';
        }
    }

    // Auto-select tab from query param if available (e.g. ?tab=documents)
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'documents') {
            switchStudioTab('tab-documents');
        } else if (tab === 'playground') {
            switchStudioTab('tab-playground');
        } else if (tab === 'search') {
            switchStudioTab('tab-search');
        } else {
            switchStudioTab('tab-chat');
        }

        // Scroll chat stream to bottom
        const stream = document.getElementById('chatMessagesStream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    });

    // --- Set Chat Prompt from Chips ---
    function setChatPrompt(text) {
        const input = document.getElementById('ragQuestionInput');
        input.value = text;
        input.focus();
    }

    // --- Submit RAG Chat AJAX ---
    function submitRagChat(e) {
        e.preventDefault();
        const input = document.getElementById('ragQuestionInput');
        const question = input.value.trim();
        const btn = document.getElementById('btnSubmitRag');
        const stream = document.getElementById('chatMessagesStream');

        if (!question) return;

        // Append user bubble immediately
        const userDiv = document.createElement('div');
        userDiv.className = 'flex items-start justify-end gap-3';
        userDiv.innerHTML = `
            <div class="bg-emerald-600 text-white rounded-2xl rounded-tr-sm p-3.5 text-xs shadow-sm max-w-xl leading-relaxed">
                ${escapeHtml(question)}
            </div>
            <div class="w-7 h-7 rounded-full bg-slate-700 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                <i class="fas fa-user"></i>
            </div>
        `;
        stream.appendChild(userDiv);
        input.value = '';
        stream.scrollTop = stream.scrollHeight;

        // Append typing indicator bubble
        const typingDiv = document.createElement('div');
        typingDiv.id = 'aiTypingBubble';
        typingDiv.className = 'flex items-start gap-3';
        typingDiv.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                <i class="fas fa-robot"></i>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm p-3.5 text-xs text-gray-500 shadow-sm flex items-center gap-2">
                <i class="fas fa-spinner fa-spin text-emerald-600"></i> Searching vectors and synthesizing answer...
            </div>
        `;
        stream.appendChild(typingDiv);
        stream.scrollTop = stream.scrollHeight;

        btn.disabled = true;

        fetch("{{ route('rag.ask') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ question: question, top_k: 3 })
        })
        .then(res => res.json())
        .then(data => {
            const typingBubble = document.getElementById('aiTypingBubble');
            if (typingBubble) typingBubble.remove();

            const aiDiv = document.createElement('div');
            aiDiv.className = 'flex items-start gap-3';

            let sourcesHtml = '';
            if (data.sources && data.sources.length > 0) {
                sourcesHtml = '<div class="pt-2 border-t border-gray-100"><span class="text-[10px] uppercase font-bold text-gray-400 block mb-1"><i class="fas fa-bookmark text-emerald-500 mr-1"></i> Retrieved Sources:</span><div class="space-y-1.5">';
                data.sources.forEach(src => {
                    sourcesHtml += `
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-[11px]">
                            <div class="flex justify-between items-center font-semibold text-gray-700">
                                <span><i class="fas fa-file-lines text-blue-500 mr-1"></i> ${escapeHtml(src.document_title)} (Chunk #${src.chunk_index})</span>
                                <span class="text-emerald-600 font-mono">${src.similarity_percentage}% match</span>
                            </div>
                            <p class="text-gray-500 mt-1 italic text-[10px]">${escapeHtml(src.content.substring(0, 160))}...</p>
                        </div>
                    `;
                });
                sourcesHtml += '</div></div>';
            }

            aiDiv.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm p-4 text-xs text-gray-800 shadow-sm max-w-2xl leading-relaxed space-y-3">
                    <div class="whitespace-pre-line">${escapeHtml(data.answer)}</div>
                    ${sourcesHtml}
                    <div class="flex justify-between items-center text-[10px] text-gray-400 pt-1">
                        <span>Model: ${data.model} &bull; ${data.tokens_used} tokens (${data.duration_ms}ms)</span>
                        <span>Just now</span>
                    </div>
                </div>
            `;
            stream.appendChild(aiDiv);
            stream.scrollTop = stream.scrollHeight;
        })
        .catch(err => {
            const typingBubble = document.getElementById('aiTypingBubble');
            if (typingBubble) typingBubble.remove();

            const errDiv = document.createElement('div');
            errDiv.className = 'flex items-start gap-3';
            errDiv.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-red-600 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-3 text-xs">
                    Failed to retrieve response from RAG engine.
                </div>
            `;
            stream.appendChild(errDiv);
            stream.scrollTop = stream.scrollHeight;
        })
        .finally(() => {
            btn.disabled = false;
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // --- Inspect Document Chunks Modal ---
    function inspectDocumentChunks(docId) {
        const modal = document.getElementById('chunkModal');
        const body = document.getElementById('chunkModalBody');
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i> Fetching chunks & vector embeddings...</div>';

        fetch('/document/' + docId + '/chunks')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('chunkModalDocTitle').innerText = data.document.title;
                    document.getElementById('chunkModalDocMeta').innerText = `${data.document.total_chunks} Chunks &bull; ${data.document.size} &bull; ${data.document.total_tokens} Total Tokens`;

                    body.innerHTML = '';
                    data.chunks.forEach(c => {
                        const box = document.createElement('div');
                        box.className = 'bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs space-y-1.5';
                        box.innerHTML = `
                            <div class="flex justify-between items-center font-bold text-gray-700">
                                <span>Chunk #${c.chunk_index} (${c.word_count} words &bull; ${c.token_count} tokens)</span>
                                <span class="text-emerald-600 font-mono text-[10px]"><i class="fas fa-check-circle mr-1"></i>1536D Vector Indexed</span>
                            </div>
                            <div class="text-gray-600 bg-white p-2.5 rounded border border-gray-100 font-mono text-[11px] leading-relaxed">
                                ${escapeHtml(c.content)}
                            </div>
                        `;
                        body.appendChild(box);
                    });
                } else {
                    body.innerHTML = '<div class="text-center py-6 text-red-500">Failed to load chunks.</div>';
                }
            })
            .catch(() => {
                body.innerHTML = '<div class="text-center py-6 text-red-500">Error connecting to server.</div>';
            });
    }

    function closeChunkModal() {
        document.getElementById('chunkModal').classList.add('hidden');
    }
</script>
@endpush
@endsection