import sys
from PIL import Image, ImageFilter, ImageStat
S = sys.argv[1]; T = sys.argv[2]
jobs = [
    ('s-03-static', 'unisex-essential-eco-cruiser-2.0-hoodie-black-back-6a2fafb72ed22.jpg', 660, 610),
    ('s-04-frequency', 'unisex-organic-oversized-raddler-2.0-sweatshirt-black-back-6a2ff081c9da7.jpg', 700, 470),
    ('s-05-homebase', 'unisex-organic-oversized-high-neck-blaster-2.0-t-shirt-stone-back-6a1310088e25d.jpg', 660, 420),
]
for name, blank, width, top in jobs:
    g = Image.open(f'{S}/blank/{blank}').convert('RGB')
    a = Image.open(f'{S}/art/{name}.png').convert('RGBA')
    h = int(a.height * width / a.width)
    a = a.resize((width, h), Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.6))
    x = (g.width - width) // 2
    patch = g.crop((x, top, x + width, top + h)).convert('L')
    mean = ImageStat.Stat(patch).mean[0]
    # Stoffstruktur und Falten auf den Druck übertragen
    shade = patch.point(lambda v: max(0, min(255, int(225 + (v - mean) * 2.2))))
    r, gg, b, al = a.split()
    rgb = Image.merge('RGB', (r, gg, b))
    rgb = Image.composite(rgb, Image.new('RGB', rgb.size), Image.new('L', rgb.size, 255))
    from PIL import ImageChops
    rgb = ImageChops.multiply(rgb, Image.merge('RGB', (shade, shade, shade)))
    al = al.point(lambda v: int(v * 0.95))
    g.paste(rgb, (x, top), al)
    g = g.crop((260, 180, 1740, 1660)).resize((1100, 1100), Image.LANCZOS)
    g.save(f'{T}/{name}.webp', quality=84)
    g.crop((150, 150, 1050, 1050)).resize((900, 900), Image.LANCZOS).save(f'{S}/art/{name}-crop.jpg', quality=85)
print('ok')
