# Exportiert den lokal laufenden Shop (http://localhost:8080) als statische Vorschau.
# Aufruf: python3 tools/export-preview.py <arbeitsordner mit wordpress/ und wp-Wrapper>
import re, os, sys, shutil, subprocess, json, html
from urllib.request import urlopen
from PIL import Image

S = sys.argv[1]; OUT = S + '/preview'; WP = S + '/wordpress'
BASE = 'http://localhost:8080'
shutil.rmtree(OUT, ignore_errors=True)
for d in ['img', 'assets', 'css']: os.makedirs(f'{OUT}/{d}', exist_ok=True)

def get(path):
    return urlopen(BASE + path, timeout=120).read().decode('utf-8')

slugs = [l.split('\t')[1] for l in subprocess.run([S + '/wp', 'post', 'list', '--post_type=product', '--post_status=publish', '--fields=ID,post_name', '--posts_per_page=100'], capture_output=True, text=True).stdout.strip().split('\n')[1:]]
pages = {'/': 'index.html', '/shop/': 'shop.html', '/shop/page/2/': 'shop-2.html'}
for t in ['s-01', 's-02', 'essentials']: pages[f'/product-tag/{t}/'] = f'serie-{t}.html'
for c in ['hoodies', 't-shirts', 'sweatshirts', 'caps', 'polos-jacken']: pages[f'/product-category/{c}/'] = f'kat-{c}.html'
for s_ in slugs: pages[f'/product/{s_}/'] = f'p-{s_}.html'

images = {}
def img_ref(url):
    path = url.replace(BASE, '')
    rel = path.split('?')[0]
    local = WP + rel
    m = re.match(r'(.*?)(-\d+x\d+)?\.(jpe?g|png|webp)$', local)
    if not m: return '#'
    stem = m.group(1)
    for cand in [stem + '-720x720.' + m.group(3), stem + '-1024x1024.' + m.group(3), local]:
        if os.path.exists(cand):
            name = os.path.basename(stem) + '.webp'
            images[name] = cand
            return 'img/' + name
    return '#'

def css_ref(url):
    path = url.replace(BASE, '').split('?')[0]
    if path.endswith('/themes/polinis/assets/css/main.css'): return 'assets/main.css'
    name = re.sub(r'[^a-z0-9.-]', '-', path.strip('/').lower()).replace('wp-content-', '').replace('plugins-woocommerce-', 'wc-')[-80:]
    src = WP + path
    if os.path.exists(src):
        shutil.copy(src, f'{OUT}/css/{name}')
        return 'css/' + name
    return '#'

EXTRA = '''
<div class="preview-note" role="note"><strong>Konzept-Vorschau</strong> · So könnte der neue Polinis-Shop aussehen. Bestellen ist hier nicht möglich.</div>
<div class="preview-toast" hidden data-preview-toast></div>
<style>
.preview-note{position:relative;z-index:60;padding:8px max(16px,env(safe-area-inset-left,0px));background:#efedf4;color:#0d0c11;font:500 12px/1.4 "Archivo",Arial,sans-serif;text-align:center}
.preview-toast{position:fixed;left:50%;bottom:calc(20px + env(safe-area-inset-bottom,0px));transform:translateX(-50%);z-index:90;max-width:min(92vw,440px);padding:14px 18px;background:#5124d6;color:#fff;font:600 14px/1.4 "Archivo",Arial,sans-serif;box-shadow:0 12px 30px -10px rgb(0 0 0/.5)}
</style>
<script>
(function(){
  var t=document.querySelector('[data-preview-toast]'),h;
  function toast(m){t.textContent=m;t.hidden=false;clearTimeout(h);h=setTimeout(function(){t.hidden=true},3200);}
  document.addEventListener('submit',function(e){e.preventDefault();
    var f=e.target;
    if(f.classList.contains('cart')) toast('Im echten Shop liegt das Teil jetzt im Warenkorb.');
    else if(f.classList.contains('signup__form')) toast('Im echten Shop wärst du jetzt für das Drop-Signal eingetragen.');
    else toast('In der Vorschau nicht verfügbar.');
  },true);
  document.addEventListener('click',function(e){var a=e.target.closest('a[href="#"]');if(a){e.preventDefault();toast('Diese Seite gibt es in der Vorschau nicht.');}});
  function syncBtn(){document.querySelectorAll('form.variations_form').forEach(function(f){
    var ok=[].every.call(f.querySelectorAll('.variations select'),function(s){return s.value;});
    var b=f.querySelector('.single_add_to_cart_button'); if(!b) return;
    b.classList.toggle('disabled',!ok); b.classList.toggle('wc-variation-selection-needed',!ok);
    var p=f.querySelector('.preview-price'); if(p){
      var vs=JSON.parse(f.dataset.product_variations||'[]'),sel={};
      f.querySelectorAll('.variations select').forEach(function(s){sel[s.name]=s.value;});
      var m=ok&&vs.find(function(v){return Object.keys(v.attributes).every(function(k){return !v.attributes[k]||v.attributes[k]===sel[k];});});
      p.innerHTML=m?m.price_html:'';
    }
  });}
  document.querySelectorAll('form.variations_form .single_variation_wrap').forEach(function(w){var d=document.createElement('div');d.className='preview-price woocommerce-variation-price';w.prepend(d);});
  document.addEventListener('change',function(e){if(e.target.closest('.variations')) setTimeout(syncBtn,0);});
  syncBtn();
})();
</script>
'''

def rewrite(doc, is_index):
    # Skripte: nur Theme-Skripte behalten
    def keep_script(m):
        tag = m.group(0)
        if 'polinis-signal' in tag or 'polinis-js-extra' in tag or 'POLINIS' in tag[:400]: return tag
        if '/themes/polinis/assets/js/main.js' in tag: return re.sub(r'src="[^"]+"', 'src="assets/main.js"', tag)
        return ''
    doc = re.sub(r'<script\b[^>]*>.*?</script>', keep_script, doc, flags=re.S)
    doc = re.sub(r'<link[^>]+rel=["\'](?:alternate|EditURI|https://api.w.org/|shortlink|dns-prefetch|profile|pingback|canonical)["\'][^>]*>', '', doc)
    doc = re.sub(r'<noscript>.*?</noscript>', '', doc, flags=re.S)
    doc = re.sub(r'\s(srcset|sizes)="[^"]*"', '', doc)
    doc = re.sub(r"<link rel='stylesheet'[^>]*href='([^']+)'[^>]*>", lambda m: m.group(0).replace(m.group(1), css_ref(m.group(1))), doc)
    doc = re.sub(r'(?:https?:)?//localhost:8080/wp-content/themes/polinis/assets/(css|fonts|img)/([^"\'\s?)]+)(\?[^"\'\s)]*)?',
                 lambda m: ('assets/' + m.group(2)) if m.group(1) != 'css' else 'assets/main.css', doc)
    doc = re.sub(r'http://localhost:8080/wp-content/uploads/[^"\'\s)]+\.(?:jpe?g|png|webp)', lambda m: img_ref(m.group(0)), doc)
    # Variantendaten: Bildpfade umschreiben
    doc = re.sub(r'http:\\/\\/localhost:8080\\/wp-content\\/uploads\\/[^"]+?\.(?:jpe?g|png|webp)', lambda m: img_ref(m.group(0).replace('\\/', '/')), doc)
    def link(m):
        url = m.group(2); path = url.replace(BASE, '') or '/'
        path = path.split('#')[0].split('?')[0]
        return m.group(1) + pages.get(path, '#') + m.group(3)
    doc = re.sub(r'(href=")(http://localhost:8080[^"]*)(")', link, doc)
    doc = re.sub(r'(action=")(http://localhost:8080[^"]*)(")', lambda m: m.group(1) + '#' + m.group(3), doc)
    doc = doc.replace('http://localhost:8080', '#')
    doc = re.sub(r'(<body[^>]*>)', r'\1' + EXTRA.replace('\\', '\\\\'), doc, count=1)
    if is_index:
        # Hauptseite: das Publish-Gerüst liefert html/head/body selbst
        body_cls = re.search(r'<body[^>]*class="([^"]*)"', doc).group(1)
        doc = re.sub(r'<!DOCTYPE html>|<!doctype html>', '', doc)
        doc = re.sub(r'<html[^>]*>|</html>|<head>|</head>|</body>', '', doc)
        doc = re.sub(r'<meta charset[^>]*>|<meta name="viewport"[^>]*>', '', doc)
        doc = re.sub(r'<title>.*?</title>', '', doc, flags=re.S)
        doc = re.sub(r'<body[^>]*>', '<script>document.body.className=' + json.dumps(body_cls) + ';</script>', doc, count=1)
        doc = '<title>Polinis ON/OFF</title>\n' + doc
    else:
        doc = re.sub(r'<title>.*?</title>', '<title>Polinis ON/OFF</title>', doc, flags=re.S)
    return doc

for path, out in pages.items():
    try:
        doc = get(path)
    except Exception as e:
        print('skip', path, e); continue
    open(f'{OUT}/{out}', 'w').write(rewrite(doc, out == 'index.html'))

T = '/home/user/test/wp-content/themes/polinis/assets/'
shutil.copy(T + 'css/main.css', OUT + '/assets/main.css')
css = open(OUT + '/assets/main.css').read().replace('../fonts/', '').replace('../img/', '')
open(OUT + '/assets/main.css', 'w').write(css)
shutil.copy(T + 'js/main.js', OUT + '/assets/main.js')
for f in os.listdir(T + 'fonts'):
    if f.endswith('.woff2'): shutil.copy(T + 'fonts/' + f, OUT + '/assets/' + f)
for f in os.listdir(T + 'img'): shutil.copy(T + 'img/' + f, OUT + '/assets/' + f)
for name, src in images.items():
    im = Image.open(src).convert('RGB'); im.thumbnail((720, 900)); im.save(f'{OUT}/img/{name}', 'WEBP', quality=80)
n = sum(len(fs) for _, _, fs in os.walk(OUT))
size = sum(os.path.getsize(os.path.join(r, f)) for r, _, fs in os.walk(OUT) for f in fs)
print('pages', len(pages), 'images', len(images), 'files', n, 'MB', round(size / 1e6, 1))
