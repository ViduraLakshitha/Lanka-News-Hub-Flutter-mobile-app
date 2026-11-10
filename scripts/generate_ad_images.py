import os, sys
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
from PIL import Image, ImageDraw, ImageFont

OUT_DIR = "public/uploads/ads"
os.makedirs(OUT_DIR, exist_ok=True)

# Helper to draw vertical or horizontal gradient
def draw_gradient(draw, width, height, start_color, end_color):
    r1, g1, b1 = start_color
    r2, g2, b2 = end_color
    for x in range(width):
        t = x / max(1, width - 1)
        r = int(r1 + (r2 - r1) * t)
        g = int(g1 + (g2 - g1) * t)
        b = int(b1 + (b2 - b1) * t)
        draw.line([(x, 0), (x, height)], fill=(r, g, b))

# Helper to load system font or fallback
def get_font(size, bold=False):
    font_names = [
        "C:\\Windows\\Fonts\\arialbd.ttf" if bold else "C:\\Windows\\Fonts\\arial.ttf",
        "C:\\Windows\\Fonts\\segoeuib.ttf" if bold else "C:\\Windows\\Fonts\\segoeui.ttf",
        "C:\\Windows\\Fonts\\calibrib.ttf" if bold else "C:\\Windows\\Fonts\\calibri.ttf"
    ]
    for fn in font_names:
        if os.path.exists(fn):
            try:
                return ImageFont.truetype(fn, size)
            except Exception:
                continue
    return ImageFont.load_default()

ads_specs = [
    {
        "filename": "ad_header_1200x100.png",
        "width": 1200, "height": 100,
        "placement": "header_728x90",
        "title": "SriLankan Airlines · Fly to Paradise",
        "subtitle": "Discover the wonder of Asia with award-winning hospitality",
        "advertiser": "SRILANKAN AIRLINES",
        "size_text": "SIZE: 1200 × 100 PX · TOP LEADERBOARD",
        "cta": "BOOK FLIGHTS >",
        "c_start": (15, 43, 70), "c_end": (27, 85, 130),
        "accent": (245, 158, 11),
        "is_vertical": False
    },
    {
        "filename": "ad_top_left_590x100.png",
        "width": 590, "height": 100,
        "placement": "top_left",
        "title": "Pure Ceylon Tea · Single Origin",
        "subtitle": "Finest handpicked Ceylon black, green & white tea",
        "advertiser": "SRI LANKA TEA BOARD",
        "size_text": "SIZE: 590 × 100 PX · TOP LEFT",
        "cta": "EXPLORE >",
        "c_start": (6, 78, 59), "c_end": (16, 120, 87),
        "accent": (250, 204, 21),
        "is_vertical": False
    },
    {
        "filename": "ad_top_right_590x100.png",
        "width": 590, "height": 100,
        "placement": "top_right",
        "title": "Colombo Port City · Financial Hub",
        "subtitle": "South Asia’s premier duty-free commercial destination",
        "advertiser": "PORT CITY COLOMBO",
        "size_text": "SIZE: 590 × 100 PX · TOP RIGHT",
        "cta": "INVEST NOW >",
        "c_start": (30, 58, 138), "c_end": (37, 99, 235),
        "accent": (56, 189, 248),
        "is_vertical": False
    },
    {
        "filename": "ad_between_sections_728x90.png",
        "width": 728, "height": 90,
        "placement": "homepage_mid_728x90",
        "title": "Commercial Bank Flash · Digital Banking",
        "subtitle": "Open a zero-fee digital savings account directly on your phone",
        "advertiser": "COMMERCIAL BANK",
        "size_text": "SIZE: 728 × 90 PX · BETWEEN SECTIONS",
        "cta": "GET FLASH >",
        "c_start": (30, 64, 175), "c_end": (59, 130, 246),
        "accent": (245, 158, 11),
        "is_vertical": False
    },
    {
        "filename": "ad_inline_feed_728x90.png",
        "width": 728, "height": 90,
        "placement": "inline_feed",
        "title": "Dialog 5G Ultra High-Speed Broadband",
        "subtitle": "Unlimited 4K streaming, gaming, & nationwide 5G coverage",
        "advertiser": "DIALOG AXIATA PLC",
        "size_text": "SIZE: 728 × 90 PX · INSIDE NEWS FEED",
        "cta": "UPGRADE 5G >",
        "c_start": (153, 27, 27), "c_end": (220, 38, 38),
        "accent": (254, 240, 138),
        "is_vertical": False
    },
    {
        "filename": "ad_in_article_800x250.png",
        "width": 800, "height": 250,
        "placement": "in_article",
        "title": "Hayleys Solar · Clean Green Renewable Power",
        "subtitle": "Cut your electricity bills by up to 90% with Tier-1 solar systems and 25-year guaranteed warranty",
        "advertiser": "HAYLEYS FENTONS SOLAR",
        "size_text": "SIZE: 800 × 250 PX · IN-ARTICLE READING BANNER",
        "cta": "FREE SURVEY >",
        "c_start": (20, 83, 45), "c_end": (22, 163, 74),
        "accent": (250, 204, 21),
        "is_vertical": False
    },
    {
        "filename": "ad_sidebar_300x250.png",
        "width": 300, "height": 250,
        "placement": "sidebar_300x250",
        "title": "Cinnamon Grand Colombo",
        "subtitle": "Luxury city retreats, fine dining, & premium hospitality in the heart of Colombo",
        "advertiser": "CINNAMON HOTELS",
        "size_text": "SIZE: 300 × 250 PX · SIDEBAR",
        "cta": "BOOK DIRECT >",
        "c_start": (74, 4, 78), "c_end": (112, 26, 117),
        "accent": (244, 114, 182),
        "is_vertical": True
    },
    {
        "filename": "ad_tower_300x600.png",
        "width": 300, "height": 600,
        "placement": "sidebar_300x600",
        "title": "Bank of Ceylon",
        "subtitle": "Bankers to the Nation since 1939. Experience reliable personal, corporate, & overseas banking with over 600 branches islandwide.",
        "advertiser": "BANK OF CEYLON",
        "size_text": "SIZE: 300 × 600 PX · TOWER",
        "cta": "OPEN ACCOUNT >",
        "c_start": (12, 74, 110), "c_end": (7, 89, 133),
        "accent": (251, 191, 36),
        "is_vertical": True
    },
    {
        "filename": "ad_footer_1200x100.png",
        "width": 1200, "height": 100,
        "placement": "footer",
        "title": "SLT-MOBITEL · The National ICT Solutions Provider",
        "subtitle": "Connecting homes, schools, and businesses with fiber-optic connectivity and nationwide enterprise cloud services",
        "advertiser": "SLT-MOBITEL",
        "size_text": "SIZE: 1200 × 100 PX · FOOTER BANNER",
        "cta": "CONNECT TODAY >",
        "c_start": (15, 23, 42), "c_end": (3, 105, 161),
        "accent": (56, 189, 248),
        "is_vertical": False
    },
    {
        "filename": "ad_sticky_728x90.png",
        "width": 728, "height": 90,
        "placement": "sticky",
        "title": "Sri Lanka Tourism · You Will Come Back For Love",
        "subtitle": "Endless beaches, timeless ruins, and warm hospitality await you",
        "advertiser": "SO SRI LANKA",
        "size_text": "SIZE: 728 × 90 PX · STICKY BOTTOM",
        "cta": "PLAN YOUR TRIP >",
        "c_start": (194, 65, 12), "c_end": (234, 88, 12),
        "accent": (254, 240, 138),
        "is_vertical": False
    },
    {
        "filename": "ad_popup_600x400.png",
        "width": 600, "height": 400,
        "placement": "popup",
        "title": "Lanka Premier League 2026",
        "subtitle": "Witness the excitement of world-class T20 cricket live under the floodlights in Colombo and Kandy! Exclusive early-bird grandstand passes now available.",
        "advertiser": "SRI LANKA CRICKET",
        "size_text": "SIZE: 600 × 400 PX · POP-UP MODAL",
        "cta": "BUY TICKETS >",
        "c_start": (17, 24, 39), "c_end": (31, 41, 55),
        "accent": (239, 68, 68),
        "is_vertical": True
    }
]

print("=" * 70)
print("GENERATING EXACT-DIMENSION BANNER ARTWORK FOR ALL 11 PLACEMENTS")
print("=" * 70)

for spec in ads_specs:
    w, h = spec["width"], spec["height"]
    img = Image.new("RGB", (w, h), (20, 20, 20))
    draw = ImageDraw.Draw(img)
    
    # 1. Background gradient
    draw_gradient(draw, w, h, spec["c_start"], spec["c_end"])
    
    # 2. Sleek border
    draw.rectangle([(0, 0), (w - 1, h - 1)], outline=spec["accent"], width=2)
    
    # 3. Size and Placement Badge (Top Left of Banner)
    badge_font = get_font(max(9, min(12, int(h * 0.12))), bold=True)
    badge_text = spec["size_text"]
    badge_bg = (0, 0, 0, 180)
    
    # Compute layout based on orientation
    if not spec["is_vertical"]:
        # Horizontal banners (1200x100, 590x100, 728x90, 800x250)
        # Size Badge
        draw.rectangle([(12, 10), (14 + len(badge_text) * 7, 26)], fill=(0, 0, 0))
        draw.text((16, 12), badge_text, fill=spec["accent"], font=badge_font)
        
        # Advertiser label
        adv_font = get_font(max(9, min(11, int(h * 0.11))), bold=True)
        draw.text((w - 220, 12), spec["advertiser"], fill=(200, 210, 225), font=adv_font)
        
        # Main Title
        title_size = max(13, min(24, int(h * 0.26)))
        title_font = get_font(title_size, bold=True)
        draw.text((16, int(h * 0.35)), spec["title"], fill=(255, 255, 255), font=title_font)
        
        # Subtitle
        sub_size = max(9, min(13, int(h * 0.14)))
        sub_font = get_font(sub_size, bold=False)
        draw.text((16, int(h * 0.65)), spec["subtitle"][:90], fill=(220, 230, 240), font=sub_font)
        
        # CTA Button on Right
        btn_w = max(110, min(190, int(w * 0.18)))
        btn_h = max(26, min(38, int(h * 0.38)))
        btn_x = w - btn_w - 16
        btn_y = int((h - btn_h) / 2) + 6
        draw.rectangle([(btn_x, btn_y), (btn_x + btn_w, btn_y + btn_h)], fill=spec["accent"])
        
        btn_font = get_font(max(10, min(12, int(btn_h * 0.38))), bold=True)
        draw.text((btn_x + 12, btn_y + int((btn_h - 14) / 2)), spec["cta"], fill=(15, 23, 42), font=btn_font)
        
    else:
        # Vertical banners (300x250, 300x600, 600x400)
        # Top sponsor bar
        draw.rectangle([(12, 12), (w - 12, 32)], fill=(0, 0, 0))
        draw.text((18, 15), spec["advertiser"], fill=(200, 215, 230), font=get_font(10, bold=True))
        
        # Size Badge
        badge_y = 42
        draw.rectangle([(12, badge_y), (w - 12, badge_y + 24)], fill=(15, 23, 42))
        draw.text((18, badge_y + 4), badge_text, fill=spec["accent"], font=get_font(11, bold=True))
        
        # Main Title
        title_font = get_font(20, bold=True)
        draw.text((16, badge_y + 36), spec["title"], fill=(255, 255, 255), font=title_font)
        
        # Subtitle paragraph
        sub_font = get_font(12, bold=False)
        words = spec["subtitle"].split(" ")
        lines = []
        cur_line = ""
        for word in words:
            if len(cur_line + " " + word) > 34:
                lines.append(cur_line)
                cur_line = word
            else:
                cur_line = (cur_line + " " + word).strip()
        if cur_line:
            lines.append(cur_line)
        
        line_y = badge_y + 80
        for line in lines[:5]:
            draw.text((16, line_y), line, fill=(210, 225, 240), font=sub_font)
            line_y += 18
            
        # Large Dimension Callout in center for 300x600
        if h >= 500:
            callout_y = int(h * 0.52)
            draw.rectangle([(20, callout_y), (w - 20, callout_y + 80)], outline=spec["accent"], width=1, fill=(10, 35, 60))
            draw.text((36, callout_y + 12), "EXACT AD SIZE", fill=(180, 205, 230), font=get_font(10, bold=True))
            draw.text((36, callout_y + 28), f"{w} × {h} PX", fill=spec["accent"], font=get_font(24, bold=True))
            draw.text((36, callout_y + 56), "TOWER SKYSCRAPER", fill=(255, 255, 255), font=get_font(10, bold=True))
            
        # CTA Button at bottom
        btn_w = w - 32
        btn_h = 42
        btn_x = 16
        btn_y = h - btn_h - 20
        draw.rectangle([(btn_x, btn_y), (btn_x + btn_w, btn_y + btn_h)], fill=spec["accent"])
        btn_font = get_font(13, bold=True)
        draw.text((btn_x + int((btn_w - len(spec["cta"]) * 8) / 2), btn_y + 13), spec["cta"], fill=(15, 23, 42), font=btn_font)

    out_path = os.path.join(OUT_DIR, spec["filename"])
    img.save(out_path, format="PNG", optimize=True)
    size_kb = os.path.getsize(out_path) / 1024
    print(f"  ✓ Created: {spec['filename']:32} ({w:4} × {h:3} px, {size_kb:.1f} KB) -> placement: {spec['placement']}")

print("=" * 70)
print("ALL 11 BANNER IMAGES CREATED SUCCESSFULLY IN public/uploads/ads/")
print("=" * 70)
