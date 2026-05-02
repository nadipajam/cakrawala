<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-orange-200 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-orange-100/80 disabled:cursor-not-allowed disabled:opacity-40']) }}>
    {{ $slot }}
</button>
