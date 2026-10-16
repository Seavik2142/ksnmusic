/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/assets/js/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'k-fg': 'var(--color-fg)',
        'k-fg-3': 'var(--k-fg-3)',
        'k-fg-5': 'var(--k-fg-5)',
        'k-fg-10': 'var(--k-fg-10)',
        'k-fg-20': 'var(--k-fg-20)',
        'k-fg-30': 'var(--k-fg-30)',
        'k-fg-40': 'var(--k-fg-40)',
        'k-fg-50': 'var(--k-fg-50)',
        'k-fg-60': 'var(--k-fg-60)',
        'k-fg-70': 'var(--k-fg-70)',
        'k-fg-80': 'var(--k-fg-80)',
        'k-fg-90': 'var(--k-fg-90)',
        'k-fg-95': 'var(--k-fg-95)',
        'k-fg-input': 'var(--color-fg)',
        'k-bg': 'var(--color-bg)',
        'k-bg-10': 'var(--k-bg-10)',
        'k-bg-20': 'var(--k-bg-20)',
        'k-bg-30': 'var(--k-bg-30)',
        'k-bg-40': 'var(--k-bg-40)',
        'k-bg-50': 'var(--k-bg-50)',
        'k-bg-60': 'var(--k-bg-60)',
        'k-bg-70': 'var(--k-bg-70)',
        'k-bg-80': 'var(--k-bg-80)',
        'k-bg-90': 'var(--k-bg-90)',
        'k-bg-95': 'var(--k-bg-95)',
        'k-bg-context-menu': 'var(--color-bg)',
        'k-bg-input': 'var(--k-bg-input)',
        'k-surface': 'var(--color-surface)',
        'k-surface-raised': 'var(--color-surface-raised)',
        'k-highlight': 'var(--color-highlight)',
        'k-highlight-fg': 'var(--color-highlight-fg)',
        'k-success': 'var(--color-success)',
        'k-danger': 'var(--color-danger)',
        'k-warning': 'var(--color-warning)',
        'k-primary': 'var(--color-primary)',
        'k-love': 'var(--color-love)',
      },
      spacing: {
        'k-header-height': 'var(--header-height)',
        'k-footer-height': 'var(--footer-height)',
        'k-sidebar-width': 'var(--sidebar-width)',
        'k-side-sheet-width': 'var(--side-sheet-width)',
      },
      fontFamily: {
        sans: ['Outfit', 'Inter', 'Kantumruy Pro', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Outfit', 'Inter', 'sans-serif'],
        khmer: ['Kantumruy Pro', 'Siemreap', 'sans-serif'],
      },
      animation: {
        'vinyl-spin': 'spin 30s linear infinite',
        'subtle-pulse': 'pulse 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'fade-in': 'fadeIn 0.25s ease-out forwards',
        'slide-up': 'slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
    },
    variants: {
      extend: {
        gradientColorStops: ['before', 'after'], // enable from-/to-/via- for pseudo-elements
      },
    },
  },
  plugins: [],
}

