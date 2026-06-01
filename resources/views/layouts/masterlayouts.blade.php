<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#4f46e5" />
  <title>SubtitleAI</title>

  <link rel="stylesheet" href="{{ asset('css/styles.css')}}" />

  <script>
    // Tailwind Play CDN config (maps design tokens to CSS variables)
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            background: "hsl(var(--background))",
            foreground: "hsl(var(--foreground))",
            card: "hsl(var(--card))",
            "card-foreground": "hsl(var(--card-foreground))",
            popover: "hsl(var(--popover))",
            "popover-foreground": "hsl(var(--popover-foreground))",
            primary: { DEFAULT: "hsl(var(--primary))", foreground: "hsl(var(--primary-foreground))" },
            secondary: { DEFAULT: "hsl(var(--secondary))", foreground: "hsl(var(--secondary-foreground))" },
            muted: { DEFAULT: "hsl(var(--muted))", foreground: "hsl(var(--muted-foreground))" },
            accent: { DEFAULT: "hsl(var(--accent))", foreground: "hsl(var(--accent-foreground))" },
            destructive: { DEFAULT: "hsl(var(--destructive))", foreground: "hsl(var(--destructive-foreground))" },
            border: "hsl(var(--border))",
            input: "hsl(var(--input))",
            ring: "hsl(var(--ring))",
          },
          borderRadius: {
            xl: "var(--radius)",
            "2xl": "calc(var(--radius) + 0.5rem)",
            "3xl": "calc(var(--radius) + 0.75rem)",
          },
        }
      }
    };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/lucide@0.562.0/dist/umd/lucide.min.js"></script>
</head>

<body data-page="home" class="min-h-screen flex flex-col bg-background text-foreground antialiased">

  @include('partials.header')

@yield('content')


  @include('partials.footer')
</body>
  <script>
    // Footer year
    document.getElementById("year") && (document.getElementById("year").textContent = String(new Date().getFullYear()));
  </script>
  <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>