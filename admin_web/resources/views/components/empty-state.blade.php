@props([
    'title' => 'No Data Available',
    'message' => 'There are no records matching your criteria yet.',
    'actionText' => null,
    'actionClick' => null,
    'actionUrl' => null,
])

<div class="p-12 text-center bg-white rounded-2xl border border-dashed border-slate-300 my-4 shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-inner">
        <i class="fa-solid fa-folder-open"></i>
    </div>
    <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">{{ $message }}</p>
    
    @if($actionText)
        @if($actionUrl)
            <a href="{{ $actionUrl }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20">
                <i class="fa-solid fa-plus"></i>
                <span>{{ $actionText }}</span>
            </a>
        @elseif($actionClick)
            <button type="button" @click="{{ $actionClick }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md shadow-emerald-600/20">
                <i class="fa-solid fa-plus"></i>
                <span>{{ $actionText }}</span>
            </button>
        @endif
    @endif
</div>
