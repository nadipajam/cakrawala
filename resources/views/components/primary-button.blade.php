<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-transparent bg-gradient-to-r from-orange-600 to-orange-800 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white transition duration-200 hover:-translate-y-0.5 hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-orange-200/70 disabled:cursor-not-allowed disabled:opacity-40']) }}>
    {{ $slot }}
</button>
