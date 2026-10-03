import os
import subprocess
from PIL import Image

WORKSPACE = '/Users/ahzarjy/Documents/KSN_song'

# 1. Main Vector Logo (Aspect ratio ~3.25:1, 260x80)
vector_logo_svg = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 260 80" width="100%" height="100%">
  <defs>
    <linearGradient id="ksn-audio-grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#38bdf8" />
      <stop offset="50%" stop-color="#818cf8" />
      <stop offset="100%" stop-color="#f43f5e" />
    </linearGradient>
    <linearGradient id="ksn-bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#181a24" />
      <stop offset="100%" stop-color="#0a0b10" />
    </linearGradient>
    <filter id="subtle-glow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="#818cf8" flood-opacity="0.4" />
    </filter>
  </defs>

  <!-- App Icon Mark -->
  <g filter="url(#subtle-glow)">
    <rect x="6" y="6" width="68" height="68" rx="19" 
          fill="url(#ksn-bg-grad)" 
          stroke="url(#ksn-audio-grad)" 
          stroke-width="2" />
    
    <!-- Equalizer Waves -->
    <g transform="translate(19, 20)">
      <rect x="0" y="12" width="5.5" height="16" rx="2.75" fill="#38bdf8" />
      <rect x="9" y="4" width="5.5" height="32" rx="2.75" fill="url(#ksn-audio-grad)" />
      <rect x="18" y="0" width="5.5" height="40" rx="2.75" fill="url(#ksn-audio-grad)" />
      <rect x="27" y="8" width="5.5" height="24" rx="2.75" fill="#818cf8" />
      <rect x="36" y="15" width="5.5" height="10" rx="2.75" fill="#f43f5e" />
    </g>
  </g>

  <!-- Wordmark -->
  <g transform="translate(88, 0)">
    <text x="0" y="48" 
          font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif" 
          font-size="42" 
          font-weight="900" 
          letter-spacing="2" 
          fill="url(#ksn-audio-grad)">KSN</text>

    <text x="2" y="68" 
          font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif" 
          font-size="12" 
          font-weight="700" 
          letter-spacing="5" 
          fill="#94a3b8">MUSIC</text>
  </g>
</svg>'''

# 2. Square App Icon SVG (512x512)
app_icon_svg = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
  <defs>
    <linearGradient id="ksn-audio-grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#38bdf8" />
      <stop offset="50%" stop-color="#818cf8" />
      <stop offset="100%" stop-color="#f43f5e" />
    </linearGradient>
    <linearGradient id="ksn-bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#181a24" />
      <stop offset="100%" stop-color="#0a0b10" />
    </linearGradient>
    <filter id="subtle-glow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="12" stdDeviation="20" flood-color="#818cf8" flood-opacity="0.45" />
    </filter>
  </defs>

  <!-- Background container / squircle -->
  <g filter="url(#subtle-glow)">
    <rect x="36" y="36" width="440" height="440" rx="110" 
          fill="url(#ksn-bg-grad)" 
          stroke="url(#ksn-audio-grad)" 
          stroke-width="10" />
  </g>

  <!-- Equalizer Waves -->
  <g transform="translate(136, 126)">
    <rect x="0" y="80" width="36" height="100" rx="18" fill="#38bdf8" />
    <rect x="58" y="26" width="36" height="208" rx="18" fill="url(#ksn-audio-grad)" />
    <rect x="116" y="0" width="36" height="260" rx="18" fill="url(#ksn-audio-grad)" />
    <rect x="174" y="50" width="36" height="160" rx="18" fill="#818cf8" />
    <rect x="232" y="95" width="36" height="70" rx="18" fill="#f43f5e" />
  </g>
</svg>'''

# Write vector logo SVGs
svg_targets = [
    f'{WORKSPACE}/resources/assets/img/ksnlogo.svg',
    f'{WORKSPACE}/resources/assets/img/logo.svg',
    f'{WORKSPACE}/public/img/ksnlogo.svg',
    f'{WORKSPACE}/public/img/logo.svg',
]

for target in svg_targets:
    with open(target, 'w') as f:
        f.write(vector_logo_svg)
    print(f'Wrote {target}')

# Generate 512x512 icon PNG via qlmanage
tmp_icon_svg = '/tmp/ksn_icon_src.svg'
with open(tmp_icon_svg, 'w') as f:
    f.write(app_icon_svg)

subprocess.run(['qlmanage', '-t', '-s', '512', '-o', '/tmp', tmp_icon_svg], check=True)
icon_512_png = '/tmp/ksn_icon_src.svg.png'

# Load generated PNG with PIL
img_512 = Image.open(icon_512_png).convert('RGBA')

# Target PNG icon paths
png_targets = [
    f'{WORKSPACE}/resources/assets/img/icon.png',
    f'{WORKSPACE}/public/img/icon.png',
    f'{WORKSPACE}/resources/assets/img/logo.png',
]

for target in png_targets:
    img_512.save(target, format='PNG')
    print(f'Wrote {target}')

# Target ICO favicons
ico_targets = [
    f'{WORKSPACE}/resources/assets/img/favicon.ico',
    f'{WORKSPACE}/public/img/favicon.ico',
]

for target in ico_targets:
    img_512.save(target, format='ICO', sizes=[(16, 16), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)])
    print(f'Wrote {target}')

# Tiles
tile_558 = img_512.resize((558, 558), Image.Resampling.LANCZOS)
tile_558.save(f'{WORKSPACE}/resources/assets/img/tile.png', format='PNG')
print(f'Wrote {WORKSPACE}/resources/assets/img/tile.png')

tile_wide = Image.new('RGBA', (558, 270), (10, 11, 16, 255))
scaled_icon = img_512.resize((230, 230), Image.Resampling.LANCZOS)
tile_wide.paste(scaled_icon, (164, 20), scaled_icon)
tile_wide.save(f'{WORKSPACE}/resources/assets/img/tile-wide.png', format='PNG')
print(f'Wrote {WORKSPACE}/resources/assets/img/tile-wide.png')

print('All branding assets generated successfully!')
