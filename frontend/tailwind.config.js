/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        dark: {
          bg: '#050505',
          surface: '#121212',
          card: '#1E1E1E',
          border: '#2A2A2A',
          nav: 'rgba(18, 18, 18, 0.95)'
        },
        brand: {
          primary: '#3B82F6',
          secondary: '#6366F1',
          accent: '#F59E0B',
          danger: '#EF4444',
          success: '#10B981'
        }
      },
      boxShadow: {
        'glow-blue': '0 0 20px rgba(59, 130, 246, 0.3)',
        'glow-gold': '0 0 15px rgba(245, 158, 11, 0.4)',
        'glow-card': '0 4px 20px rgba(0, 0, 0, 0.4)',
      },
      animation: {
        'float': 'float 6s ease-in-out infinite',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'bounce-slow': 'bounce 3s infinite',
        'slide-up': 'slideUp 0.3s ease-out forwards',
        'road-move': 'roadMove 2s linear infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        slideUp: {
          '0%': { transform: 'translateY(100%)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        roadMove: {
          '0%': { backgroundPosition: '0 0' },
          '100%': { backgroundPosition: '0 100px' },
        }
      }
    },
  },
  plugins: [],
}
