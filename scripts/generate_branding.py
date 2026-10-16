"""Generate every KSN brand image from the single source logo, resources/assets/img/ksnlogo.svg.

Usage: python3 scripts/generate_branding.py

Requires only the Python standard library and Google Chrome (override its location with CHROME_PATH).
The source logo is never modified; edit it, then rerun this script to refresh everything derived from it.
"""

import os
import re
import shutil
import struct
import subprocess
import sys
import tempfile
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent
ASSETS_DIR = REPO_ROOT / 'resources/assets/img'
PUBLIC_DIR = REPO_ROOT / 'public/img'
SOURCE_LOGO = ASSETS_DIR / 'ksnlogo.svg'
CHROME_PATH = os.environ.get('CHROME_PATH', '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome')

MARK_COLOR = '#ffffff'
ICON_BACKGROUND = 'radial-gradient(circle at 50% 30%, #2a2a2f 0%, #121214 62%, #0b0b0c 100%)'
FAVICON_SIZES = (16, 32, 48, 256)


def read_source_logo():
    markup = SOURCE_LOGO.read_text()

    def find(pattern, label):
        match = re.search(pattern, markup)
        if not match:
            sys.exit(f'Could not find the {label} in {SOURCE_LOGO}')
        return match.group(1)

    return {
        'markup': markup,
        'view_box': [float(value) for value in find(r'viewBox="([^"]+)"', 'viewBox').split()],
        'mark_path': find(r'class="ksn-logo-mark"[^>]*\sd="([^"]+)"', 'letter path'),
        'accent_path': find(r'class="ksn-logo-accent"[^>]*\sd="([^"]+)"', 'accent path'),
        'accent_color': find(r'--ksn-logo-accent:\s*(#[0-9a-fA-F]{3,8})', 'accent color'),
    }


def solid_logo_svg(logo, x, y, width):
    """The logo as nested SVG with fixed colors, for surfaces whose background never changes."""
    _, _, view_width, view_height = logo['view_box']
    height = width * view_height / view_width
    return (
        f'<svg x="{x:.1f}" y="{y:.1f}" width="{width:.1f}" height="{height:.1f}" '
        f'viewBox="{" ".join(f"{value:g}" for value in logo["view_box"])}">'
        f'<path fill="{MARK_COLOR}" d="{logo["mark_path"]}"/>'
        f'<path fill="{logo["accent_color"]}" d="{logo["accent_path"]}"/>'
        '</svg>'
    )


def square_favicon_svg(logo):
    """The source logo in a square frame; keeps its own light/dark color switching."""
    min_x, min_y, width, height = logo['view_box']
    side = max(width, height)
    square_view_box = f'{min_x - (side - width) / 2:g} {min_y - (side - height) / 2:g} {side:g} {side:g}'
    return re.sub(r'viewBox="[^"]+"', f'viewBox="{square_view_box}"', logo['markup'], count=1)


def default_cover_svg(logo):
    logo_width = 512 * 0.5
    _, _, view_width, view_height = logo['view_box']
    logo_height = logo_width * view_height / view_width
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="KSN Music">
  <defs>
    <linearGradient id="ksn-cover-background" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#2a2a2f"/>
      <stop offset="1" stop-color="#0e0e10"/>
    </linearGradient>
  </defs>
  <rect width="512" height="512" fill="url(#ksn-cover-background)"/>
  <g opacity="0.9">
    {solid_logo_svg(logo, (512 - logo_width) / 2, (512 - logo_height) / 2, logo_width)}
  </g>
</svg>
'''


def html_page(width, height, canvas_style, logo_svg):
    return f'''<!doctype html>
<html><head><meta charset="utf-8"><style>
  html, body {{ margin: 0; width: {width}px; height: {height}px; overflow: hidden; background: transparent; }}
  .canvas {{ width: {width}px; height: {height}px; overflow: hidden; {canvas_style} }}
  svg {{ display: block; }}
</style></head><body><div class="canvas">{logo_svg}</div></body></html>'''


def centered_logo_canvas(logo, width, height, logo_width_ratio):
    _, _, view_width, view_height = logo['view_box']
    logo_width = width * logo_width_ratio
    logo_height = logo_width * view_height / view_width
    inner = solid_logo_svg(logo, (width - logo_width) / 2, (height - logo_height) / 2, logo_width)
    return f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}">{inner}</svg>'


def render_png(page_html, width, height, workdir, name):
    html_path = workdir / f'{name}.html'
    png_path = workdir / f'{name}.png'
    html_path.write_text(page_html)
    subprocess.run(
        [
            CHROME_PATH,
            '--headless',
            '--disable-gpu',
            '--hide-scrollbars',
            '--default-background-color=00000000',
            f'--window-size={width},{height}',
            f'--screenshot={png_path}',
            html_path.as_uri(),
        ],
        check=True,
        capture_output=True,
    )
    return png_path.read_bytes()


def build_ico(png_images_by_size):
    header = struct.pack('<HHH', 0, 1, len(png_images_by_size))
    offset = len(header) + 16 * len(png_images_by_size)
    entries = b''
    payload = b''
    for size, png in png_images_by_size:
        entries += struct.pack('<BBBBHHII', size % 256, size % 256, 0, 0, 1, 32, len(png), offset)
        payload += png
        offset += len(png)
    return header + entries + payload


def write(path, content):
    path.parent.mkdir(parents=True, exist_ok=True)
    if isinstance(content, bytes):
        path.write_bytes(content)
    else:
        path.write_text(content)
    print(f'Wrote {path.relative_to(REPO_ROOT)}')


def main():
    if not Path(CHROME_PATH).exists():
        sys.exit(f'Google Chrome not found at {CHROME_PATH}; set CHROME_PATH to its executable.')

    logo = read_source_logo()

    with tempfile.TemporaryDirectory() as temp:
        workdir = Path(temp)

        app_icon = render_png(
            html_page(512, 512, f'background: {ICON_BACKGROUND};', centered_logo_canvas(logo, 512, 512, 0.66)),
            512, 512, workdir, 'icon',
        )

        favicon_images = []
        for size in FAVICON_SIZES:
            logo_ratio = 0.96 if size <= 16 else 0.9 if size <= 32 else 0.8
            rounded_square = f'background: {ICON_BACKGROUND}; border-radius: {size * 0.22:.1f}px;'
            favicon_images.append(
                (size, render_png(html_page(size, size, rounded_square, centered_logo_canvas(logo, size, size, logo_ratio)), size, size, workdir, f'favicon-{size}'))
            )

        tile = render_png(
            html_page(558, 558, f'background: {ICON_BACKGROUND};', centered_logo_canvas(logo, 558, 558, 0.6)),
            558, 558, workdir, 'tile',
        )
        tile_wide = render_png(
            html_page(558, 270, f'background: {ICON_BACKGROUND};', centered_logo_canvas(logo, 558, 270, 0.5)),
            558, 270, workdir, 'tile-wide',
        )

        cover_svg = default_cover_svg(logo)
        cover_png = render_png(html_page(678, 678, '', cover_svg.replace('<svg ', '<svg width="678" height="678" ', 1)), 678, 678, workdir, 'cover')

    favicon_ico = build_ico(favicon_images)
    favicon_svg = square_favicon_svg(logo)

    write(ASSETS_DIR / 'logo.svg', logo['markup'])
    write(ASSETS_DIR / 'favicon.svg', favicon_svg)
    write(ASSETS_DIR / 'icon.png', app_icon)
    write(ASSETS_DIR / 'logo.png', app_icon)
    write(ASSETS_DIR / 'favicon.ico', favicon_ico)
    write(ASSETS_DIR / 'tile.png', tile)
    write(ASSETS_DIR / 'tile-wide.png', tile_wide)
    write(ASSETS_DIR / 'covers/default.svg', cover_svg)
    write(ASSETS_DIR / 'covers/default.png', cover_png)

    for name in ('ksnlogo.svg', 'logo.svg', 'favicon.svg', 'icon.png', 'favicon.ico', 'tile.png', 'tile-wide.png'):
        PUBLIC_DIR.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ASSETS_DIR / name, PUBLIC_DIR / name)
        print(f'Wrote {(PUBLIC_DIR / name).relative_to(REPO_ROOT)}')

    print('All branding assets generated from ksnlogo.svg.')


if __name__ == '__main__':
    main()
