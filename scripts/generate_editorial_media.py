import os
from PIL import Image, ImageDraw, ImageFont

OUTPUT_DIR = r"f:\news_page\public\uploads\media"
os.makedirs(OUTPUT_DIR, exist_ok=True)

def get_font(size, bold=False):
    candidates = [
        r"C:\Windows\Fonts\arialbd.ttf" if bold else r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\segoeuib.ttf" if bold else r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\calibrib.ttf" if bold else r"C:\Windows\Fonts\calibri.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            try:
                return ImageFont.truetype(path, size)
            except Exception:
                pass
    return ImageFont.load_default()

items = [
    {
        "filename": "news_colombo_skyline.png",
        "category": "URBAN DEVELOPMENT",
        "title": "Colombo Port City & Financial Skyline",
        "subtitle": "South Asia's Emerging Offshore Financial Center",
        "badge": "FRONT COVER · 4K HIGH RES",
        "gradient": ((15, 23, 42), (30, 58, 138), (56, 189, 248)),
        "accent": (56, 189, 248),
        "pattern": "city"
    },
    {
        "filename": "news_tea_harvest.png",
        "category": "AGRICULTURE & EXPORTS",
        "title": "Nuwara Eliya High-Grown Tea Harvest",
        "subtitle": "Single-Estate Artisan Ceylon Black & Green Teas",
        "badge": "PHOTO ESSAY · FEATURED",
        "gradient": ((6, 78, 59), (16, 185, 129), (5, 150, 105)),
        "accent": (52, 211, 153),
        "pattern": "nature"
    },
    {
        "filename": "news_cricket_action.png",
        "category": "SPORTS & CHAMPIONSHIP",
        "title": "National Cricket T20 Championship Finals",
        "subtitle": "R. Premadasa International Stadium, Colombo",
        "badge": "LIVE WIRE · ACTION SHOT",
        "gradient": ((88, 28, 135), (124, 58, 237), (217, 70, 239)),
        "accent": (232, 121, 249),
        "pattern": "stadium"
    },
    {
        "filename": "news_tech_summit.png",
        "category": "TECHNOLOGY & INNOVATION",
        "title": "Colombo AI & Digital Economy Forum 2026",
        "subtitle": "Accelerating Fintech & Cloud Modernization",
        "badge": "EXCLUSIVE INTERVIEW",
        "gradient": ((17, 24, 39), (31, 41, 55), (79, 70, 229)),
        "accent": (129, 140, 248),
        "pattern": "grid"
    },
    {
        "filename": "news_kandy_culture.png",
        "category": "CULTURE & HERITAGE",
        "title": "Historic Kandy Esala Perahera Pageant",
        "subtitle": "Preserving Ancient Sacred Hill Country Traditions",
        "badge": "CULTURAL ARCHIVE",
        "gradient": ((120, 53, 15), (180, 83, 9), (245, 158, 11)),
        "accent": (251, 191, 36),
        "pattern": "temple"
    },
    {
        "filename": "news_green_energy.png",
        "category": "RENEWABLE ENERGY",
        "title": "Mannar Island 100MW Wind Farm Project",
        "subtitle": "Clean Coastal Grid Power & Sustainable Transition",
        "badge": "INFRASTRUCTURE FOCUS",
        "gradient": ((19, 78, 74), (13, 148, 136), (45, 212, 191)),
        "accent": (94, 234, 212),
        "pattern": "wind"
    },
    {
        "filename": "news_central_bank.png",
        "category": "ECONOMY & MARKETS",
        "title": "Central Bank Annual Fiscal & Monetary Report",
        "subtitle": "GDP Trajectory, Foreign Reserves & Inflation Index",
        "badge": "GOVERNMENT BRIEFING",
        "gradient": ((30, 27, 75), (67, 56, 202), (99, 102, 241)),
        "accent": (165, 180, 252),
        "pattern": "graph"
    },
    {
        "filename": "news_wildlife_yala.png",
        "category": "ENVIRONMENT & WILDLIFE",
        "title": "Yala National Park Leopard & Elephant Habitat",
        "subtitle": "Community-Led Biodiversity Protection Zone",
        "badge": "NATURE WILDLIFE",
        "gradient": ((24, 48, 89), (44, 82, 130), (74, 85, 104)),
        "accent": (147, 197, 253),
        "pattern": "wildlife"
    }
]

W, H = 1000, 625

for item in items:
    img = Image.new("RGB", (W, H), item["gradient"][0])
    draw = ImageDraw.Draw(img)
    
    # Smooth gradient fill
    c1, c2, c3 = item["gradient"]
    for y in range(H):
        ratio = y / H
        if ratio < 0.5:
            r_sub = ratio / 0.5
            r = int(c1[0] + (c2[0] - c1[0]) * r_sub)
            g = int(c1[1] + (c2[1] - c1[1]) * r_sub)
            b = int(c1[2] + (c2[2] - c1[2]) * r_sub)
        else:
            r_sub = (ratio - 0.5) / 0.5
            r = int(c2[0] + (c3[0] - c2[0]) * r_sub)
            g = int(c2[1] + (c3[1] - c2[1]) * r_sub)
            b = int(c2[2] + (c3[2] - c2[2]) * r_sub)
        draw.line([(0, y), (W, y)], fill=(r, g, b))
        
    # Geometric decorative accents
    acc = item["accent"]
    for i in range(5):
        draw.arc([-100 + i*30, -100 + i*20, W + 100 - i*30, H + 100 - i*20], 
                 start=0, end=360, fill=(acc[0], acc[1], acc[2]), width=2)
                 
    # Glass banner card in bottom section
    card_top = int(H * 0.45)
    draw.rectangle([(40, card_top), (W - 40, H - 40)], fill=(15, 23, 42), outline=acc, width=2)
    
    # Header badge pill
    font_badge = get_font(14, bold=True)
    draw.rounded_rectangle([(65, card_top + 25), (280, card_top + 55)], radius=6, fill=acc)
    draw.text((80, card_top + 32), item["badge"], font=font_badge, fill=(15, 23, 42))
    
    # Category tag
    font_cat = get_font(16, bold=True)
    draw.text((310, card_top + 33), item["category"], font=font_cat, fill=acc)
    
    # Main Headline
    font_title = get_font(34, bold=True)
    draw.text((65, card_top + 75), item["title"], font=font_title, fill=(255, 255, 255))
    
    # Subtitle
    font_sub = get_font(20, bold=False)
    draw.text((65, card_top + 130), item["subtitle"], font=font_sub, fill=(203, 213, 225))
    
    # Footer metadata brand
    font_meta = get_font(13, bold=False)
    draw.text((65, H - 75), "LANKA NEWS HUB EDITORIAL MEDIA POOL · AUTHORIZED PRESS ARCHIVE · HIGH RESOLUTION", 
              font=font_meta, fill=(148, 163, 184))

    out_path = os.path.join(OUTPUT_DIR, item["filename"])
    img.save(out_path, "PNG", optimize=True)
    print(f"Generated {out_path}")

# Also create a sample PDF document for testing PDF media
pdf_content = b"%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n4 0 obj\n<< /Length 174 >>\nstream\nBT\n/F1 24 Tf\n50 720 Td\n(LANKA NEWS HUB - SPECIAL INVESTIGATIVE REPORT) Tj\n/F1 12 Tf\n0 -40 Td\n(Fiscal and Monetary Reform Whitepaper - October 2026 Edition) Tj\nET\nendstream\nendobj\n5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000266 00000 n \n0000000493 00000 n \ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n570\n%%EOF\n"
pdf_path = os.path.join(OUTPUT_DIR, "whitepaper_economic_outlook_2026.pdf")
with open(pdf_path, "wb") as f:
    f.write(pdf_content)
print(f"Generated {pdf_path}")

print("All editorial media generated successfully!")
