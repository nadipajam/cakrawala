@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-700 placeholder:text-slate-400 shadow-sm transition duration-200 focus:border-orange-500/50 focus:outline-none focus:ring-2 focus:ring-orange-200/50 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400']) }}>
