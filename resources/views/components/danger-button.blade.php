<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-red-300 bg-red-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-red-700 transition duration-200 hover:-translate-y-0.5 hover:bg-red-200 focus:outline-none focus:ring-4 focus:ring-red-100/90 disabled:cursor-not-allowed disabled:opacity-40']) }}>
    {{ $slot }}
</button>
