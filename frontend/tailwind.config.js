/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Paleta principal: bordô/vinho profundo com acento vermelho
        primary: {
          50: '#fdf3f4',
          100: '#fbe7ea',
          200: '#f5c4cc',
          300: '#e997a4',
          400: '#d26476',
          500: '#b0364c',
          600: '#8b1e2e', // cor principal (bordô)
          700: '#6e1725',
          800: '#4f111b',
          900: '#340b12',
        },
        // Vinho profundo: topbars, hero, botões escuros
        wine: {
          50: '#f8ecee',
          100: '#eecdd1',
          200: '#dc9aa2',
          300: '#c66772',
          400: '#a83f4d',
          500: '#7b1e2a', // vinho principal
          600: '#661822',
          700: '#4f121b',
          800: '#3a0d14',
          900: '#25080d',
        },
        // Vermelho de acento vibrante (CTAs, badges, destaques)
        accent: {
          50: '#fff1f2',
          100: '#ffe4e7',
          200: '#fecdd3',
          300: '#fda4af',
          400: '#f97a86',
          500: '#e11d3f',
          600: '#c8102e',
          700: '#a50d26',
          800: '#7f0a1e',
          900: '#5b0716',
        },
        success: {
          50: '#ecfdf5',
          100: '#d1fae5',
          500: '#10b981',
          600: '#059669',
          700: '#047857',
        },
        warning: {
          50: '#fffbeb',
          100: '#fef3c7',
          500: '#f59e0b',
          600: '#d97706',
          700: '#b45309',
        },
        danger: {
          50: '#fef2f2',
          100: '#fee2e2',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
        },
      },
      boxShadow: {
        'brand-sm': '0 2px 8px -1px rgba(139, 30, 46, 0.08)',
        'brand': '0 8px 24px -6px rgba(139, 30, 46, 0.18)',
        'brand-lg': '0 20px 40px -10px rgba(76, 15, 25, 0.35)',
      },
      backgroundImage: {
        'brand-gradient': 'linear-gradient(135deg, #7b1e2a 0%, #4f121b 100%)',
        'brand-gradient-soft': 'linear-gradient(135deg, #8b1e2e 0%, #c8102e 100%)',
        'brand-radial': 'radial-gradient(circle at 30% 20%, rgba(200,16,46,0.35), transparent 60%), linear-gradient(135deg, #4f121b 0%, #25080d 100%)',
      },
    },
  },
  plugins: [],
}
